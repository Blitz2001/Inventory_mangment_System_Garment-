<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Ensure $companyName is set for print header
if (!isset($companyName)) {
    $sqlforname = "SELECT setting_value FROM system_settings WHERE setting_id = 1";
    $resultforname = $conn->query($sqlforname);
    if ($resultforname && $resultforname->num_rows > 0) {
        $row = $resultforname->fetch_assoc();
        $companyName = $row['setting_value'];
    } else {
        $companyName = 'Company Name';
    }
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: sales_orders.php");
    exit();
}

$so_id = (int)$_GET['id'];

// Fetch order details
$order_sql = "SELECT so.*, c.name as customer_name, c.email, c.phone, c.address 
             FROM sales_orders so 
             JOIN customers c ON so.customer_id = c.customer_id
             WHERE so.so_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $so_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: sales_orders.php");
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Fetch order items
$items_sql = "SELECT soi.*, p.name as product_name, p.sku, p.unit_price as list_price 
             FROM sales_order_items soi
             JOIN products p ON soi.product_id = p.product_id
             WHERE soi.so_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $so_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Sales Order #<?php echo $order['order_number']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Main page styles */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .btn-print {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-print:hover {
            background-color: #138496;
        }
        
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-receipt, .print-receipt * {
                visibility: visible;
            }
            .print-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
                font-family: Arial, sans-serif;
            }
            .no-print {
                display: none !important;
            }
        }
        
        /* Print receipt styling */
        .print-receipt {
            display: none;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background: white;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-address {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .receipt-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .receipt-info {
            width: 48%;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .receipt-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .receipt-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .receipt-totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        
        .receipt-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #333;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-block {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 30px;
            margin-bottom: 5px;
        }
        
        /* Main page content styling */
        .customer-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        
        .info-value {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-row {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<!-- Printable Receipt (hidden on screen, shown when printing) -->
<div class="print-receipt">
    <div class="receipt-header">
        <div class="company-name"><?php echo htmlspecialchars($companyName); ?></div>
        <div class="company-address">
            123 Business Street, City<br>
            Phone: (123) 456-7890 | Email: info@company.com
        </div>
        <div class="document-title">SALES ORDER RECEIPT</div>
    </div>
    
    <div class="receipt-meta">
        <div class="receipt-info">
            <div class="info-label">Order Information:</div>
            <div><strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?></div>
            <div><strong>Date:</strong> <?php echo date('M j, Y', strtotime($order['order_date'])); ?></div>
            <div><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></div>
        </div>
        
        <div class="receipt-info">
            <div class="info-label">Customer Information:</div>
            <div><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
            <?php if ($order['phone']): ?>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></div>
            <?php endif; ?>
            <?php if ($order['email']): ?>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></div>
            <?php endif; ?>
            <?php if ($order['address']): ?>
            <div><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <table class="receipt-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rs <?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>Rs <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="receipt-totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rs <?php echo number_format($order['subtotal'], 2); ?></span>
        </div>
        <div class="total-row">
            <span>Tax (<?php echo round(($order['tax_amount'] / $order['subtotal']) * 100, 2); ?>%):</span>
            <span>Rs <?php echo number_format($order['tax_amount'], 2); ?></span>
        </div>
        <div class="total-row grand-total">
            <span>Total Amount:</span>
            <span>Rs <?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
    </div>
    
    <div class="receipt-footer">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div>Customer Signature</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div>Authorized Signature</div>
        </div>
    </div>
    
    <?php if (!empty($order['notes'])): ?>
    <div class="notes-section" style="margin-top: 30px;">
        <div><strong>Order Notes:</strong></div>
        <div><?php echo nl2br(htmlspecialchars($order['notes'])); ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Main Page Content -->
<div class="header-container">
    <h2>Sales Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
    <div>
        <a href="sales_orders.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <button class="btn btn-print" onclick="printReceipt()" style="margin-left: 10px;">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>
</div>

<div class="customer-info">
    <div class="info-row">
        <span class="info-label">Customer:</span>
        <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Email:</span>
        <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Phone:</span>
        <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Address:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($order['address'])); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Order Date:</span>
        <span class="info-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">
            <span style="padding: 5px 10px; border-radius: 3px; 
                background-color: <?php 
                    switch($order['status']) {
                        case 'draft': echo '#f8f9fa'; break;
                        case 'confirmed': echo '#fff3cd'; break;
                        case 'shipped': echo '#d4edda'; break;
                        case 'delivered': echo '#cce5ff'; break;
                        case 'cancelled': echo '#f8d7da'; break;
                        default: echo '#f8f9fa';
                    }
                ?>; 
                color: <?php 
                    switch($order['status']) {
                        case 'draft': echo '#6c757d'; break;
                        case 'confirmed': echo '#856404'; break;
                        case 'shipped': echo '#155724'; break;
                        case 'delivered': echo '#004085'; break;
                        case 'cancelled': echo '#721c24'; break;
                        default: echo '#6c757d';
                    }
                ?>;">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </span>
    </div>
</div>

<h3>Order Items</h3>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo htmlspecialchars($item['sku']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>Rs <?php echo number_format($item['unit_price'], 2); ?></td>
                <td>Rs <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="order-summary">
    <div class="summary-row">
        <span>Subtotal:</span>
        <span>Rs <?php echo number_format($order['subtotal'], 2); ?></span>
    </div>
    <div class="summary-row">
        <span>Tax (<?php echo ($order['subtotal'] != 0 ? round($order['tax_amount'] / $order['subtotal'] * 100, 2) : 0); ?>%):</span>
        <span>Rs <?php echo number_format($order['tax_amount'], 2); ?></span>
    </div>
    <div class="summary-row total-row">
        <span>Total Amount:</span>
        <span>Rs <?php echo number_format($order['total_amount'], 2); ?></span>
    </div>
</div>

<?php if (!empty($order['notes'])): ?>
    <div class="notes-section">
        <div><strong>Order Notes:</strong></div>
        <div><?php echo nl2br(htmlspecialchars($order['notes'])); ?></div>
    </div>
<?php endif; ?>

<script>
function printReceipt() {
    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    
    // Get the print receipt HTML
    var printContent = document.querySelector('.print-receipt').outerHTML;
    
    // Write the content to the new window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Sales Order Receipt #<?php echo $order['order_number']; ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                }
                .receipt-header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #333;
                }
                .company-name {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .document-title {
                    font-size: 18px;
                    font-weight: bold;
                    margin: 15px 0;
                }
                .receipt-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .receipt-table th, .receipt-table td {
                    padding: 10px;
                    border: 1px solid #ddd;
                }
                .receipt-table th {
                    background-color: #f2f2f2;
                }
                .receipt-totals {
                    float: right;
                    width: 300px;
                    margin-top: 20px;
                }
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                .grand-total {
                    font-weight: bold;
                    border-top: 2px solid #333;
                    padding-top: 10px;
                }
                .receipt-footer {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 1px solid #333;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-block {
                    width: 200px;
                    text-align: center;
                }
                .signature-line {
                    border-bottom: 1px solid #333;
                    height: 30px;
                    margin-bottom: 5px;
                }
                @page {
                    size: auto;
                    margin: 10mm;
                }
            </style>
        </head>
        <body>
            ${printContent}
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() {
                        window.close();
                    }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
</script>

<?php 
$conn->close();
?>