<?php
include '../addphp/navbar.php';
require_once '../config/db_config.php';

// Initialize message variables
$message = '';
$messageType = '';

// Set default report type
$report_type = isset($_GET['report']) ? $_GET['report'] : 'sales';

// Set date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .report-nav {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .report-nav a {
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            border-bottom: none;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            background-color: #f8f9fa;
        }
        
        .report-nav a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .report-nav a:hover:not(.active) {
            background-color: #e9ecef;
        }
        
        .filter-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .filter-group input, .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .report-section {
            margin-bottom: 30px;
        }
        
        .report-title {
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
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
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        
        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            flex: 1;
            min-width: 200px;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .summary-card-title {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .summary-card-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .currency {
            font-size: 16px;
            color: #28a745;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-warning {
            color: #ffc107;
        }
    </style>
</head>
<body>

<div class="header-container">
    <h2>Inventory Reports</h2>
</div>

<!-- Report Navigation -->
<div class="report-nav">
    <a href="?report=sales" class="<?php echo $report_type === 'sales' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Sales Reports
    </a>
    <a href="?report=inventory" class="<?php echo $report_type === 'inventory' ? 'active' : ''; ?>">
        <i class="fas fa-boxes"></i> Inventory Reports
    </a>
    <a href="?report=raw_material_usage" class="<?php echo $report_type === 'raw_material_usage' ? 'active' : ''; ?>">
        <i class="fas fa-cubes"></i> Raw Material Usage
    </a>
    <a href="?report=profitability" class="<?php echo $report_type === 'profitability' ? 'active' : ''; ?>">
        <i class="fas fa-money-bill-wave"></i> Profitability Analysis
    </a>
</div>

<!-- Date Range Filter -->
<div class="filter-container">
    <form method="GET" action="">
        <input type="hidden" name="report" value="<?php echo $report_type; ?>">
        
        <div class="filter-row">
            <div class="filter-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            
            <div class="filter-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Apply Date Range
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='?report=<?php echo $report_type; ?>'">
                <i class="fas fa-times"></i> Reset
            </button>
        </div>
    </form>
</div>

<?php if ($report_type === 'sales'): ?>
    <!-- Sales Reports -->
    <div class="report-section">
        <h3 class="report-title">Sales Summary</h3>
        
        <?php
        // Get total sales for the period
        $sales_sql = "SELECT 
                        COUNT(DISTINCT so.so_id) as order_count,
                        SUM(so.total_amount) as total_sales,
                        SUM(so.total_amount - so.tax_amount - so.discount_amount) as net_sales,
                        SUM(so.tax_amount) as total_tax,
                        SUM(so.discount_amount) as total_discount
                      FROM sales_orders so
                      WHERE so.order_date BETWEEN ? AND ?
                      AND so.status NOT IN ('cancelled', 'draft')";
        
        $stmt = $conn->prepare($sales_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $sales_result = $stmt->get_result();
        $sales_data = $sales_result->fetch_assoc();
        $stmt->close();
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-title">Total Orders</div>
                <div class="summary-card-value"><?php echo $sales_data['order_count'] ?? 0; ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Total Sales</div>
                <div class="summary-card-value">Rs <?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Net Sales</div>
                <div class="summary-card-value">Rs <?php echo number_format($sales_data['net_sales'] ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Total Tax</div>
                <div class="summary-card-value">Rs <?php echo number_format($sales_data['total_tax'] ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Total Discount</div>
                <div class="summary-card-value">Rs <?php echo number_format($sales_data['total_discount'] ?? 0, 2); ?></div>
            </div>
        </div>
        
        <?php
        // Query sales totals per month for the selected date range
        $sales_trend_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as period, SUM(total_amount) as total_sales
                            FROM sales_orders
                            WHERE order_date BETWEEN ? AND ?
                              AND status NOT IN ('cancelled', 'draft')
                            GROUP BY period
                            ORDER BY period ASC";
        $stmt = $conn->prepare($sales_trend_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $trend_result = $stmt->get_result();
        $trend_labels = [];
        $trend_data = [];
        while ($row = $trend_result->fetch_assoc()) {
            $trend_labels[] = date('M Y', strtotime($row['period'] . '-01'));
            $trend_data[] = (float)$row['total_sales'];
        }
        $stmt->close();
        ?>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
        
        <h3 class="report-title">Sales by Product</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Orders</th>
                    <th>Quantity Sold</th>
                    <th>Total Sales</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Use vw_sales_summary view
                $product_sales_sql = "SELECT 
                                        product_id,
                                        product_name,
                                        category,
                                        order_count,
                                        total_quantity,
                                        total_sales
                                      FROM vw_sales_summary
                                      WHERE first_sale_date >= ? AND last_sale_date <= ?
                                      ORDER BY total_sales DESC";
                $stmt = $conn->prepare($product_sales_sql);
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $product_sales_result = $stmt->get_result();
                $total_sales = $sales_data['total_sales'] ?? 1; // Avoid division by zero
                while ($row = $product_sales_result->fetch_assoc()):
                    $percentage = ($row['total_sales'] / $total_sales) * 100;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $row['order_count']; ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                        <td>Rs <?php echo number_format($row['total_sales'], 2); ?></td>
                        <td><?php echo number_format($percentage, 1); ?>%</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h3 class="report-title">Sales by Customer</h3>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Orders</th>
                    <th>Total Sales</th>
                    <th>% of Total</th>
                    <th>First Order</th>
                    <th>Last Order</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Use vw_sales_by_customer view
                $customer_sales_sql = "SELECT 
                                        customer_id,
                                        customer_name,
                                        order_count,
                                        total_sales,
                                        first_order_date,
                                        last_order_date
                                      FROM vw_sales_by_customer
                                      WHERE first_order_date >= ? AND last_order_date <= ?
                                      ORDER BY total_sales DESC";
                $stmt = $conn->prepare($customer_sales_sql);
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $customer_sales_result = $stmt->get_result();
                while ($row = $customer_sales_result->fetch_assoc()):
                    $percentage = ($row['total_sales'] / $total_sales) * 100;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo $row['order_count']; ?></td>
                        <td>Rs <?php echo number_format($row['total_sales'], 2); ?></td>
                        <td><?php echo number_format($percentage, 1); ?>%</td>
                        <td><?php echo date('M d, Y', strtotime($row['first_order_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['last_order_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <script>
        // Sales Trend Chart (Dynamic)
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesTrendChart').getContext('2d');
            const salesTrendChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($trend_labels); ?>,
                    datasets: [{
                        label: 'Total Sales',
                        data: <?php echo json_encode($trend_data); ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            },
                            title: {
                                display: true,
                                text: 'Sales (Rs)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Sales: Rs ' + context.raw.toLocaleString();
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
        </script>
    </div>

<?php elseif ($report_type === 'inventory'): ?>
    <!-- Inventory Reports -->
    <div class="report-section">
        <h3 class="report-title">Inventory Status</h3>
        
        <?php
        // Get inventory valuation
        $inventory_value = $conn->query("SELECT calculate_inventory_value() as total_value")->fetch_assoc()['total_value'];
        // Get inventory status summary from vw_inventory_status
        $inventory_summary_sql = "SELECT 
                                    COUNT(*) as total_products,
                                    SUM(quantity_on_hand) as total_quantity,
                                    SUM(quantity_allocated) as total_allocated,
                                    SUM(available_quantity) as total_available
                                  FROM vw_inventory_status
                                  WHERE 1";
        $inventory_summary = $conn->query($inventory_summary_sql)->fetch_assoc();
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-title">Total Products</div>
                <div class="summary-card-value"><?php echo $inventory_summary['total_products'] ?? 0; ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Total Quantity</div>
                <div class="summary-card-value"><?php echo $inventory_summary['total_quantity'] ?? 0; ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Allocated Quantity</div>
                <div class="summary-card-value"><?php echo $inventory_summary['total_allocated'] ?? 0; ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Available Quantity</div>
                <div class="summary-card-value"><?php echo $inventory_summary['total_available'] ?? 0; ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Inventory Value</div>
                <div class="summary-card-value">Rs <?php echo number_format($inventory_value, 2); ?></div>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="inventoryTurnoverChart"></canvas>
        </div>
        
        <h3 class="report-title">Inventory by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Products</th>
                    <th>Total Quantity</th>
                    <th>Inventory Value</th>
                    <th>Potential Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Use vw_inventory_valuation view
                $category_inventory_sql = "SELECT 
                                            category,
                                            product_count,
                                            total_quantity,
                                            total_value,
                                            potential_revenue
                                          FROM vw_inventory_valuation
                                          ORDER BY total_value DESC";
                $category_inventory_result = $conn->query($category_inventory_sql);
                while ($row = $category_inventory_result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $row['product_count']; ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                        <td>Rs <?php echo number_format($row['total_value'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['potential_revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h3 class="report-title">Products to Reorder</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>On Hand</th>
                    <th>Allocated</th>
                    <th>Available</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Use vw_products_to_reorder view
                $reorder_sql = "SELECT 
                                    product_name,
                                    sku,
                                    category,
                                    quantity_on_hand,
                                    quantity_allocated,
                                    available_quantity,
                                    reorder_level
                                FROM vw_products_to_reorder
                                ORDER BY available_quantity ASC";
                $reorder_result = $conn->query($reorder_sql);
                while ($row = $reorder_result->fetch_assoc()):
                    $status_class = ($row['available_quantity'] <= 0) ? 'text-danger' : 'text-warning';
                    $status_text = ($row['available_quantity'] <= 0) ? 'Out of Stock' : 'Low Stock';
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['sku']); ?></td>
                        <td><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $row['quantity_on_hand']; ?></td>
                        <td><?php echo $row['quantity_allocated']; ?></td>
                        <td><?php echo $row['available_quantity']; ?></td>
                        <td><?php echo $row['reorder_level']; ?></td>
                        <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <script>
        // Inventory Turnover Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('inventoryTurnoverChart').getContext('2d');
            const inventoryTurnoverChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['T-Shirts', 'Jeans', 'Outerwear', 'Accessories'],
                    datasets: [{
                        label: 'Inventory Value',
                        data: [4500, 6800, 2400, 1600],
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Potential Revenue',
                        data: [8500, 12500, 4800, 3200],
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rs ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
    </div>

<?php elseif ($report_type === 'raw_material_usage'): ?>
    <!-- Raw Material Usage Report -->
    <div class="report-section">
        <h3 class="report-title">Raw Material Usage</h3>
        <table>
            <thead>
                <tr>
                    <th>Material Name</th>
                    <th>SKU</th>
                    <th>Unit</th>
                    <th>Opening Balance</th>
                    <th>Purchased</th>
                    <th>Consumed</th>
                    <th>Adjusted</th>
                    <th>Closing Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get all active raw materials
                $materials_sql = "SELECT material_id, name, sku, unit_of_measure FROM raw_materials WHERE is_active = 1 ORDER BY name";
                $materials_result = $conn->query($materials_sql);
                while ($mat = $materials_result->fetch_assoc()):
                    $material_id = $mat['material_id'];
                    // Opening balance: sum of all transactions before start_date
                    $opening_sql = "SELECT IFNULL(SUM(quantity),0) as opening FROM raw_material_transactions WHERE material_id = ? AND created_at < ?";
                    $stmt = $conn->prepare($opening_sql);
                    $stmt->bind_param("is", $material_id, $start_date);
                    $stmt->execute();
                    $opening = $stmt->get_result()->fetch_assoc()['opening'];
                    $stmt->close();
                    // Purchased in range
                    $purchased_sql = "SELECT IFNULL(SUM(quantity),0) as purchased FROM raw_material_transactions WHERE material_id = ? AND transaction_type = 'purchase' AND created_at BETWEEN ? AND ?";
                    $stmt = $conn->prepare($purchased_sql);
                    $stmt->bind_param("iss", $material_id, $start_date, $end_date);
                    $stmt->execute();
                    $purchased = $stmt->get_result()->fetch_assoc()['purchased'];
                    $stmt->close();
                    // Consumed in range
                    $consumed_sql = "SELECT IFNULL(SUM(quantity),0) as consumed FROM raw_material_transactions WHERE material_id = ? AND transaction_type = 'consumption' AND created_at BETWEEN ? AND ?";
                    $stmt = $conn->prepare($consumed_sql);
                    $stmt->bind_param("iss", $material_id, $start_date, $end_date);
                    $stmt->execute();
                    $consumed = $stmt->get_result()->fetch_assoc()['consumed'];
                    $stmt->close();
                    // Adjusted in range
                    $adjusted_sql = "SELECT IFNULL(SUM(quantity),0) as adjusted FROM raw_material_transactions WHERE material_id = ? AND transaction_type = 'adjustment' AND created_at BETWEEN ? AND ?";
                    $stmt = $conn->prepare($adjusted_sql);
                    $stmt->bind_param("iss", $material_id, $start_date, $end_date);
                    $stmt->execute();
                    $adjusted = $stmt->get_result()->fetch_assoc()['adjusted'];
                    $stmt->close();
                    // Closing balance: opening + purchased + consumed + adjusted
                    $closing = $opening + $purchased + $consumed + $adjusted;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($mat['name']); ?></td>
                    <td><?php echo htmlspecialchars($mat['sku']); ?></td>
                    <td><?php echo htmlspecialchars($mat['unit_of_measure']); ?></td>
                    <td><?php echo number_format($opening, 2); ?></td>
                    <td><?php echo number_format($purchased, 2); ?></td>
                    <td><?php echo number_format($consumed, 2); ?></td>
                    <td><?php echo number_format($adjusted, 2); ?></td>
                    <td><?php echo number_format($closing, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($report_type === 'profitability'): ?>
    <!-- Profitability Analysis -->
    <div class="report-section">
        <h3 class="report-title">Profitability Overview</h3>
        
        <?php
        // Get profitability data
        $profit_sql = "SELECT 
                        SUM(so.total_amount - so.tax_amount - so.discount_amount) as net_sales,
                        SUM(soi.quantity * p.cost_price) as total_cost,
                        SUM(so.total_amount - so.tax_amount - so.discount_amount - (soi.quantity * p.cost_price)) as gross_profit,
                        (SUM(so.total_amount - so.tax_amount - so.discount_amount - (soi.quantity * p.cost_price)) / 
                         SUM(so.total_amount - so.tax_amount - so.discount_amount)) * 100 as gross_margin
                      FROM sales_orders so
                      JOIN sales_order_items soi ON so.so_id = soi.so_id
                      JOIN products p ON soi.product_id = p.product_id
                      WHERE so.order_date BETWEEN ? AND ?
                      AND so.status NOT IN ('cancelled', 'draft')";
        
        $stmt = $conn->prepare($profit_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $profit_result = $stmt->get_result();
        $profit_data = $profit_result->fetch_assoc();
        $stmt->close();
        
        $gross_margin = $profit_data['gross_margin'] ?? 0;
        $margin_class = ($gross_margin >= 40) ? 'text-success' : (($gross_margin >= 30) ? 'text-warning' : 'text-danger');
        ?>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-title">Net Sales</div>
                <div class="summary-card-value">Rs <?php echo number_format($profit_data['net_sales'] ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Cost of Goods</div>
                <div class="summary-card-value">Rs <?php echo number_format($profit_data['total_cost'] ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Gross Profit</div>
                <div class="summary-card-value">Rs <?php echo number_format($profit_data['gross_profit'] ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-title">Gross Margin</div>
                <div class="summary-card-value <?php echo $margin_class; ?>"><?php echo number_format($gross_margin, 1); ?>%</div>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="profitabilityChart"></canvas>
        </div>
        
        <h3 class="report-title">Profitability by Product</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Sales</th>
                    <th>Cost</th>
                    <th>Profit</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $product_profit_sql = "SELECT 
                                        p.product_id,
                                        p.sku,
                                        p.name as product_name,
                                        c.name as category_name,
                                        SUM(soi.quantity * soi.unit_price) as sales,
                                        SUM(soi.quantity * p.cost_price) as cost,
                                        SUM(soi.quantity * soi.unit_price) - SUM(soi.quantity * p.cost_price) as profit,
                                        (SUM(soi.quantity * soi.unit_price) - SUM(soi.quantity * p.cost_price)) / 
                                        SUM(soi.quantity * soi.unit_price) * 100 as margin
                                      FROM sales_order_items soi
                                      JOIN sales_orders so ON soi.so_id = so.so_id
                                      JOIN products p ON soi.product_id = p.product_id
                                      LEFT JOIN categories c ON p.category_id = c.category_id
                                      WHERE so.order_date BETWEEN ? AND ?
                                      AND so.status NOT IN ('cancelled', 'draft')
                                      GROUP BY p.product_id, p.sku, p.name, c.name
                                      ORDER BY profit DESC";
                
                $stmt = $conn->prepare($product_profit_sql);
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $product_profit_result = $stmt->get_result();
                
                while ($row = $product_profit_result->fetch_assoc()):
                    $margin = $row['margin'] ?? 0;
                    $margin_class = ($margin >= 40) ? 'text-success' : (($margin >= 30) ? 'text-warning' : 'text-danger');
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['sku']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>Rs <?php echo number_format($row['sales'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['cost'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['profit'], 2); ?></td>
                        <td class="<?php echo $margin_class; ?>"><?php echo number_format($margin, 1); ?>%</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h3 class="report-title">Profitability by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Sales</th>
                    <th>Cost</th>
                    <th>Profit</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $category_profit_sql = "SELECT 
                                        c.category_id,
                                        c.name as category_name,
                                        SUM(soi.quantity * soi.unit_price) as sales,
                                        SUM(soi.quantity * p.cost_price) as cost,
                                        SUM(soi.quantity * soi.unit_price) - SUM(soi.quantity * p.cost_price) as profit,
                                        (SUM(soi.quantity * soi.unit_price) - SUM(soi.quantity * p.cost_price)) / 
                                        SUM(soi.quantity * soi.unit_price) * 100 as margin
                                      FROM sales_order_items soi
                                      JOIN sales_orders so ON soi.so_id = so.so_id
                                      JOIN products p ON soi.product_id = p.product_id
                                      LEFT JOIN categories c ON p.category_id = c.category_id
                                      WHERE so.order_date BETWEEN ? AND ?
                                      AND so.status NOT IN ('cancelled', 'draft')
                                      GROUP BY c.category_id, c.name
                                      ORDER BY profit DESC";
                
                $stmt = $conn->prepare($category_profit_sql);
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $category_profit_result = $stmt->get_result();
                
                while ($row = $category_profit_result->fetch_assoc()):
                    $margin = $row['margin'] ?? 0;
                    $margin_class = ($margin >= 40) ? 'text-success' : (($margin >= 30) ? 'text-warning' : 'text-danger');
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>Rs <?php echo number_format($row['sales'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['cost'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['profit'], 2); ?></td>
                        <td class="<?php echo $margin_class; ?>"><?php echo number_format($margin, 1); ?>%</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <script>
        // Profitability Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('profitabilityChart').getContext('2d');
            const profitabilityChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['T-Shirts', 'Jeans', 'Outerwear', 'Accessories'],
                    datasets: [{
                        label: 'Sales',
                        data: [8500, 12500, 4800, 3200],
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Cost',
                        data: [4500, 6800, 2400, 1600],
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Profit',
                        data: [4000, 5700, 2400, 1600],
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rs ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
    </div>
<?php endif; ?>

<?php 
$conn->close();
?>
</body>
</html>