<?php
require_once '../config/db_config.php';
// Get company name
$sqlforname = "SELECT setting_value FROM system_settings WHERE setting_id = 1";
$resultforname = $conn->query($sqlforname);
$companyName = ($resultforname && $resultforname->num_rows > 0) ? $resultforname->fetch_assoc()['setting_value'] : 'Company Name';

// Get sale ID
$sale_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$sale_id) {
    echo '<p>Invalid sale ID.</p>';
    exit;
}
// Fetch sale
$stmt = $conn->prepare("SELECT * FROM sales_list WHERE Sales_ID = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$sale) {
    echo '<p>Sale not found.</p>';
    exit;
}
// Fetch item name
function getItemName($conn, $itemID) {
    $stmt = $conn->prepare("SELECT Name FROM item_details WHERE Item_ID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['Name'] ?? 'Unknown';
}
$itemName = getItemName($conn, $sale['Item_ID']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Receipt #<?php echo $sale['Sales_ID']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .receipt-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 30px 40px 30px 40px;
        }
        .print-header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .print-header h1 {
            margin: 0;
            font-size: 2.2em;
            letter-spacing: 2px;
        }
        .print-header .print-meta {
            margin-top: 8px;
            font-size: 1em;
            color: #555;
        }
        .print-header .print-meta span {
            display: inline-block;
            margin-right: 20px;
        }
        .print-hr {
            border: none;
            border-top: 2px solid #333;
            margin: 18px 0 18px 0;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-size: 15px;
        }
        .receipt-table th, .receipt-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }
        .receipt-table th {
            background: #f7f7f7;
            font-weight: bold;
        }
        .receipt-summary {
            margin-top: 20px;
            font-size: 1.1em;
            text-align: right;
        }
        .print-signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            font-size: 1em;
        }
        .print-signature-block {
            width: 30%;
            text-align: left;
        }
        .print-signature-line {
            border-bottom: 1px solid #333;
            width: 90%;
            margin-bottom: 5px;
            height: 30px;
        }
        .print-signature-label {
            color: #555;
            font-size: 0.95em;
        }
        .print-signature-date {
            font-size: 0.95em;
            color: #555;
            margin-top: 4px;
        }
        .print-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 1.1em;
            color: #555;
        }
        .btn-print {
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .btn-print:hover {
            background-color: #138496;
        }
        @media print {
            body { background: #fff; }
            .btn-print { display: none !important; }
            .receipt-container {
                box-shadow: none !important;
                border: 1px solid #bbb;
                border-radius: 8px;
                margin: 0;
                max-width: 100vw;
                padding: 30px 40px 0 40px !important;
            }
        }
    </style>
</head>
<body>
<div class="receipt-container">
    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    <div class="print-header">
        <!-- <img src="../path/to/logo.png" alt="Logo" style="height:60px; margin-bottom:10px;"> -->
        <h1><?php echo htmlspecialchars($companyName); ?></h1>
        <div class="print-meta" style="margin-top:8px;">
            <span style="font-size:1.2em;"><strong>Sales Receipt</strong></span>
        </div>
        <div class="print-meta">
            <span><strong>Receipt #:</strong> <?php echo htmlspecialchars($sale['Sales_ID']); ?></span>
            <span><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($sale['Date_created'])); ?></span>
        </div>
        <hr class="print-hr" />
    </div>
    <table class="receipt-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Client</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($itemName); ?></td>
                <td><?php echo htmlspecialchars($sale['Client']); ?></td>
                <td>Rs <?php echo number_format($sale['Price'], 2); ?></td>
                <td><?php echo htmlspecialchars($sale['Quantity']); ?></td>
                <td>Rs <?php echo number_format($sale['Amount'], 2); ?></td>
            </tr>
        </tbody>
    </table>
    <div class="receipt-summary">
        <div><strong>Total Amount:</strong> Rs <?php echo number_format($sale['Amount'], 2); ?></div>
    </div>
    <div class="print-signatures">
        <div class="print-signature-block">
            <div class="print-signature-line"></div>
            <div class="print-signature-label">Prepared By</div>
            <div class="print-signature-date">Date: ____________</div>
        </div>
        <div class="print-signature-block">
            <div class="print-signature-line"></div>
            <div class="print-signature-label">Approved By</div>
            <div class="print-signature-date">Date: ____________</div>
        </div>
    </div>
    <div class="print-footer">
        Thank you for your business!
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?> 