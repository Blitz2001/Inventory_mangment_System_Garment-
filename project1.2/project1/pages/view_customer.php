<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Check if customer ID is provided
if (!isset($_GET['id'])) {
    header("Location: customers.php");
    exit();
}

$customer_id = (int)$_GET['id'];

// Fetch customer details
$customer_sql = "SELECT * FROM customers WHERE customer_id = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows === 0) {
    header("Location: customers.php");
    exit();
}

$customer = $customer_result->fetch_assoc();
$customer_stmt->close();

// Fetch all customer orders with item details
$orders_sql = "SELECT 
                so.so_id, 
                so.order_number, 
                so.order_date, 
                so.status, 
                so.total_amount,
                COUNT(soi.so_item_id) AS item_count,
                SUM(soi.quantity) AS total_quantity
              FROM sales_orders so
              LEFT JOIN sales_order_items soi ON so.so_id = soi.so_id
              WHERE so.customer_id = ? 
              GROUP BY so.so_id
              ORDER BY so.order_date DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$all_orders = $orders_result->fetch_all(MYSQLI_ASSOC);
$orders_stmt->close();

// Fetch manufacturing orders only for this customer using so_id link
$manufacturing_orders_sql = "SELECT 
    mo.mo_id,
    mo.mo_number,
    p.name AS product_name,
    mo.quantity,
    mo.status,
    mo.start_date,
    mo.completion_date,
    mo.total_cost
FROM manufacturing_orders mo
JOIN products p ON mo.product_id = p.product_id
JOIN sales_orders so ON mo.so_id = so.so_id
WHERE so.customer_id = ?
ORDER BY mo.start_date DESC";
$manufacturing_stmt = $conn->prepare($manufacturing_orders_sql);
$manufacturing_stmt->bind_param("i", $customer_id);
$manufacturing_stmt->execute();
$manufacturing_result = $manufacturing_stmt->get_result();
$manufacturing_orders = $manufacturing_result->fetch_all(MYSQLI_ASSOC);
$manufacturing_stmt->close();

// Calculate order statistics
$total_orders = count($all_orders);
$total_spent = array_sum(array_column($all_orders, 'total_amount'));
$first_order = $total_orders > 0 ? min(array_column($all_orders, 'order_date')) : null;
$last_order = $total_orders > 0 ? max(array_column($all_orders, 'order_date')) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customer Details - <?php echo htmlspecialchars($customer['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-view {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #138496;
        }
        
        .btn-new {
            background-color: #28a745;
            color: white;
        }
        
        .btn-new:hover {
            background-color: #218838;
        }
        
        .customer-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            width: 200px;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
        }
        
        .active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .customer-stats {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            flex: 1;
            min-width: 200px;
            margin: 0 10px 10px 0;
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #6c757d;
            font-size: 14px;
        }
        
        .stat-card p {
            margin-bottom: 0;
            font-size: 24px;
            font-weight: bold;
            color: #343a40;
        }
        
        .customer-orders {
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
        }
        
        .draft {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        .confirmed {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .shipped {
            background-color: #d4edda;
            color: #155724;
        }
        
        .delivered {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .planned {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .in_progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        
        .no-orders {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="header-container">
    <h2>Customer: <?php echo htmlspecialchars($customer['name']); ?></h2>
    <div>
        <a href="customers.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
        <a href="edit_customer.php?id=<?php echo $customer_id; ?>" class="btn btn-edit" style="margin-left: 10px;">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

<div class="customer-details">
    <div class="detail-row">
        <span class="detail-label">Status:</span>
        <span class="detail-value">
            <span class="status <?php echo $customer['status']; ?>">
                <?php echo ucfirst($customer['status']); ?>
            </span>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Contact Person:</span>
        <span class="detail-value"><?php echo htmlspecialchars($customer['contact_person']); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Email:</span>
        <span class="detail-value"><?php echo htmlspecialchars($customer['email']); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Phone:</span>
        <span class="detail-value"><?php echo htmlspecialchars($customer['phone']); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Address:</span>
        <span class="detail-value"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Tax ID:</span>
        <span class="detail-value"><?php echo htmlspecialchars($customer['tax_id']); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Credit Limit:</span>
        <span class="detail-value">Rs <?php echo number_format($customer['credit_limit'], 2); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Payment Terms:</span>
        <span class="detail-value"><?php echo htmlspecialchars($customer['payment_terms']); ?></span>
    </div>
</div>

<div class="customer-stats">
    <div class="stat-card">
        <h3>Total Orders</h3>
        <p><?php echo $total_orders; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Spent</h3>
        <p>Rs <?php echo number_format($total_spent, 2); ?></p>
    </div>
    <div class="stat-card">
        <h3>First Order</h3>
        <p><?php echo $first_order ? date('M d, Y', strtotime($first_order)) : 'N/A'; ?></p>
    </div>
    <div class="stat-card">
        <h3>Last Order</h3>
        <p><?php echo $last_order ? date('M d, Y', strtotime($last_order)) : 'N/A'; ?></p>
    </div>
</div>

<div class="customer-orders">
    <div class="section-header">
        <h3>Order History</h3>
    </div>
    
    <?php if ($total_orders > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        <td>
                            <span class="order-status <?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $order['item_count']; ?></td>
                        <td><?php echo $order['total_quantity']; ?></td>
                        <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <a href="view_sales_order.php?id=<?php echo $order['so_id']; ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-orders">
            <p>This customer hasn't placed any orders yet.</p>
        </div>
    <?php endif; ?>
</div>

<div class="customer-orders">
    <div class="section-header">
        <h3>Manufacturing Orders</h3>
        <a href="create_manufacturing_order.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-new">
            <i class="fas fa-plus"></i> New Manufacturing Order
        </a>
    </div>
    
    <?php if (count($manufacturing_orders) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>MO #</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>Completion Date</th>
                    <th>Total Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($manufacturing_orders as $mo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mo['mo_number']); ?></td>
                        <td><?php echo htmlspecialchars($mo['product_name']); ?></td>
                        <td><?php echo $mo['quantity']; ?></td>
                        <td>
                            <span class="order-status <?php echo str_replace(' ', '_', $mo['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $mo['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $mo['start_date'] ? date('M d, Y', strtotime($mo['start_date'])) : 'N/A'; ?></td>
                        <td><?php echo $mo['completion_date'] ? date('M d, Y', strtotime($mo['completion_date'])) : 'N/A'; ?></td>
                        <td>Rs <?php echo number_format($mo['total_cost'], 2); ?></td>
                        <td>
                            <a href="manufacturing_order_details.php?id=<?php echo $mo['mo_id']; ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-orders">
            <p>No manufacturing orders found for products this customer has ordered.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
$conn->close();
?>