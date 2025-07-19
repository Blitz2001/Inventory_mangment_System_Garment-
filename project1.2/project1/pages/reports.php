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

// Fetch system/company name
$sys = $conn->query("SELECT setting_value FROM system_settings WHERE setting_name = 'system_name'")->fetch_assoc();
$company_name = $sys ? $sys['setting_value'] : 'Inventory System';
$date = date('Y-m-d H:i');
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
        .print-btn {
            display: inline-block;
            margin-bottom: 24px;
            padding: 8px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
        }
        .report-branding {
            text-align: center;
            margin-bottom: 16px;
        }
        .report-branding h1 {
            margin: 0;
            font-size: 2em;
        }
        .report-meta {
            text-align: right;
            font-size: 0.95em;
            color: #666;
            margin-bottom: 16px;
        }
        .print-header, .print-footer { display: none; }
        @media print {
            .print-header, .print-footer { display: block !important; }
            .print-btn, .report-nav, .filter-container, nav, .sidebar, .navbar { display: none !important; }
        .chart-container { display: none !important; }
            .header-container { display: none !important; }
            body { background: #fff !important; }
            .report-section { page-break-inside: avoid; }
            .report-container, .container, .main-content, .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
                width: 100% !important;
                max-width: 100% !important;
                page-break-before: auto !important;
                page-break-after: auto !important;
            }
            .print-header {
                text-align: center;
                margin-bottom: 15px;
                page-break-after: avoid !important;
                page-break-before: auto !important;
            }
        }
    </style>
</head>
<body>
<div class="report-branding">
    <h1><?= htmlspecialchars($company_name) ?></h1>
    <div class="report-meta">
        Generated: <?= $date ?><br>
        Period: <?= date('M d, Y', strtotime($start_date)) ?> to <?= date('M d, Y', strtotime($end_date)) ?>
    </div>
</div>
<button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>

<div class="report-container">
    <div class="print-header">
        <div class="company-info">
            <div class="company-name">MGS Garment</div>
            <div class="company-address">
                New town, Ambagasdowa.<br>
                Telephone: +94712291358<br>
                Email: mgsgarment@gmail.com
            </div>
        </div>
        <div class="report-title">INVENTORY REPORTS</div>
        <div class="report-meta">
            Generated: <?php echo $date; ?><br>
            Period: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?>
        </div>
        <hr style="border:none; border-top:1px solid #333; margin:3mm 0;" />
    </div>

    <style>
@media print {
        /* Remove unnecessary margins and padding */
        body {
        margin: 0 !important;
        padding: 5mm !important;
    }
    
    /* Compact the header section */
    .print-header {
        margin-bottom: 5px !important;
        padding-bottom: 0 !important;
    }
    
    /* Reduce space between sections */
    .report-section {
        margin-top: 5px !important;
        margin-bottom: 5px !important;
    }
    
    /* Make tables more compact */
    table {
        margin-top: 3px !important;
        margin-bottom: 3px !important;
    }
    
    /* Remove extra space in summary cards */
    .summary-cards {
        margin-bottom: 5px !important;
        gap: 5px !important;
    }
    
    /* Remove any empty elements that might be creating space */
    .empty-space {
        display: none !important;
    }
    
    /* Ensure no page breaks create unnecessary space */
    .report-section, table, .summary-cards {
        page-break-inside: avoid;
    }

    body {
        margin: 0;
        padding: 10px !important;
        font-size: 12px;
        line-height: 1.2;
        background: #fff !important;
        color: #000 !important;
    }
    
    .print-header {
        text-align: center;
        margin-bottom: 10px;
        page-break-after: avoid;
    }
    
    .company-info {
        margin-bottom: 5px;
    }
    
    .company-name {
        font-size: 1.6em;
        font-weight: bold;
        margin-bottom: 3px;
    }
    
    .company-address {
        font-size: 0.9em;
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .report-title {
        font-size: 1.2em;
        margin: 8px 0 4px 0;
        page-break-after: avoid;
    }
    
    .report-meta {
        font-size: 0.8em;
        margin-bottom: 8px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 5px 0 10px 0;
        page-break-inside: avoid;
        font-size: 0.9em;
    }
    
    th, td {
        padding: 5px 3px !important;
        border: 1px solid #ddd !important;
    }
    
    th {
        background-color: #f2f2f2 !important;
        font-weight: bold;
    }
    
    .summary-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
        page-break-inside: avoid;
    }
    
    .summary-card {
        flex: 1 1 120px;
        min-width: 120px;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 2px;
        page-break-inside: avoid;
    }
    
    .summary-card-title {
        font-size: 0.8em;
        margin-bottom: 3px;
    }
    
    .summary-card-value {
        font-size: 1em;
    }
    
    .chart-container {
        height: 300px;
        width: 100%;
        page-break-inside: avoid;
    }
    
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    
    .print-footer {
        margin-top: 15px;
        page-break-before: avoid;
    }
    
    .signatures {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .signature-block {
        width: 45%;
    }
    
    .signature-line {
        border-bottom: 1px solid #000;
        height: 1px;
        margin-bottom: 3px;
    }
    
    .thank-you {
        margin-top: 15px;
        text-align: center;
        font-style: italic;
        font-size: 0.9em;
    }
    
    /* Hide non-print elements */
    .print-btn, .report-nav, .filter-container, nav, .sidebar, .navbar,
    .header-container, .report-branding {
        display: none !important;
    }
    
    /* Hide charts only when printing */
    .chart-container {
        display: none !important;
    }
    
    /* Force black text for print */
    * {
        color: #000 !important;
    }
}

/* Add this to your existing print media query to ensure consistency across all reports */
@media print {
    /* Standardize table styling for all reports */
    .report-section table {
        font-size: 10px !important;
        margin: 2mm 0 !important;
    }
    
    .report-section th, 
    .report-section td {
        padding: 3px 2px !important;
    }
    
    /* Ensure charts don't break across pages */
    .chart-container {
        page-break-inside: avoid;
        height: 250px !important;
    }
    
    /* Standardize summary cards */
    .summary-cards {
        gap: 3px !important;
        margin-bottom: 5px !important;
    }
    
    .summary-card {
        min-width: 120px !important;
        padding: 5px !important;
    }
    
    /* Fix for profitability margin colors */
    .text-success, .text-danger, .text-warning {
        color: inherit !important;
        font-weight: bold;
    }
    
    /* Ensure all report sections avoid page breaks */
    .report-section {
        page-break-inside: avoid;
        margin: 5px 0 !important;
    }
    

    
    /* Fix for long tables */
    table {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

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

        // Order status breakdown
        $status_sql = "SELECT status, COUNT(*) as count, SUM(total_amount) as total FROM sales_orders WHERE order_date BETWEEN ? AND ? GROUP BY status";
        $stmt = $conn->prepare($status_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $status_result = $stmt->get_result();
        $status_breakdown = [];
        while ($row = $status_result->fetch_assoc()) {
            $status_breakdown[$row['status']] = $row;
        }
        $stmt->close();

        // Sales growth vs previous period
        $prev_start = date('Y-m-d', strtotime($start_date . ' -' . (strtotime($end_date) - strtotime($start_date) + 1) . ' days'));
        $prev_end = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $growth_sql = "SELECT SUM(total_amount) as total_sales FROM sales_orders WHERE order_date BETWEEN ? AND ? AND status NOT IN ('cancelled', 'draft')";
        $stmt = $conn->prepare($growth_sql);
        $stmt->bind_param("ss", $prev_start, $prev_end);
        $stmt->execute();
        $prev_sales = $stmt->get_result()->fetch_assoc()['total_sales'] ?? 0;
        $stmt->close();
        $curr_sales = $sales_data['total_sales'] ?? 0;
        $sales_growth = ($prev_sales > 0) ? (($curr_sales - $prev_sales) / $prev_sales) * 100 : 0;
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
                <div class="summary-card-title">Sales Growth vs Previous Period</div>
                <div class="summary-card-value <?php echo ($sales_growth >= 0) ? 'text-success' : 'text-danger'; ?>"><?php echo number_format($sales_growth, 1); ?>%</div>
            </div>
        </div>
        <div class="summary-cards">
            <?php foreach ($status_breakdown as $status => $row): ?>
                <div class="summary-card">
                    <div class="summary-card-title">Orders: <?php echo ucfirst($status); ?></div>
                    <div class="summary-card-value"><?php echo $row['count']; ?> (Rs <?php echo number_format($row['total'], 2); ?>)</div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Top 5 Products -->
        <h4 class="report-title">Top 5 Products</h4>
        <table>
            <thead>
                <tr><th>Product</th><th>Category</th><th>Quantity Sold</th><th>Total Sales</th><th>Tax</th><th>Net Sales</th></tr>
            </thead>
            <tbody>
            <?php
            $top_products_sql = "SELECT 
                                    p.name as product_name,
                                    c.name as category_name,
                                    SUM(soi.quantity) as qty,
                                    SUM(soi.quantity * soi.unit_price) as sales,
                                    SUM(soi.quantity * soi.unit_price * 0.15) as tax
                                FROM products p
                                JOIN sales_order_items soi ON p.product_id = soi.product_id
                                JOIN sales_orders so ON soi.so_id = so.so_id
                                LEFT JOIN categories c ON p.category_id = c.category_id
                                WHERE so.order_date BETWEEN ? AND ?
                                  AND so.status NOT IN ('cancelled', 'draft')
                                GROUP BY p.product_id, p.name, c.name
                                ORDER BY sales DESC LIMIT 5";
            $stmt = $conn->prepare($top_products_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()): 
                $net_sales = $row['sales'] - $row['tax'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                    <td><?php echo $row['qty']; ?></td>
                    <td>Rs <?php echo number_format($row['sales'], 2); ?></td>
                    <td>Rs <?php echo number_format($row['tax'], 2); ?></td>
                    <td>Rs <?php echo number_format($net_sales, 2); ?></td>
                </tr>
            <?php endwhile; $stmt->close(); ?>
            </tbody>
        </table>
        <!-- Top 5 Customers -->
        <h4 class="report-title">Top 5 Customers</h4>
        <table>
            <thead>
                <tr><th>Customer</th><th>Orders</th><th>Total Sales</th></tr>
            </thead>
            <tbody>
            <?php
            $top_customers_sql = "SELECT customer_name, order_count, total_sales FROM vw_sales_by_customer WHERE first_order_date >= ? AND last_order_date <= ? ORDER BY total_sales DESC LIMIT 5";
            $stmt = $conn->prepare($top_customers_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo $row['order_count']; ?></td>
                    <td>Rs <?php echo number_format($row['total_sales'], 2); ?></td>
                </tr>
            <?php endwhile; $stmt->close(); ?>
            </tbody>
        </table>
        
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
                    <th>Tax</th>
                    <th>Net Sales</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get product sales with tax breakdown
                $product_sales_sql = "SELECT 
                                        p.product_id,
                                        p.name as product_name,
                                        c.name as category_name,
                                        COUNT(DISTINCT so.so_id) as order_count,
                                        SUM(soi.quantity) as total_quantity,
                                        SUM(soi.quantity * soi.unit_price) as total_sales,
                                        SUM(soi.quantity * soi.unit_price * 0.15) as total_tax
                                      FROM products p
                                      JOIN sales_order_items soi ON p.product_id = soi.product_id
                                      JOIN sales_orders so ON soi.so_id = so.so_id
                                      LEFT JOIN categories c ON p.category_id = c.category_id
                                      WHERE so.order_date BETWEEN ? AND ?
                                        AND so.status NOT IN ('cancelled', 'draft')
                                      GROUP BY p.product_id, p.name, c.name
                                      ORDER BY total_sales DESC";
                $stmt = $conn->prepare($product_sales_sql);
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $product_sales_result = $stmt->get_result();
                $total_sales = $sales_data['total_sales'] ?? 1; // Avoid division by zero
                while ($row = $product_sales_result->fetch_assoc()):
                    $percentage = ($row['total_sales'] / $total_sales) * 100;
                    $net_sales = $row['total_sales'] - $row['total_tax'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $row['order_count']; ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                        <td>Rs <?php echo number_format($row['total_sales'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['total_tax'], 2); ?></td>
                        <td>Rs <?php echo number_format($net_sales, 2); ?></td>
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
                    <th>Tax</th>
                    <th>Net Sales</th>
                    <th>% of Total</th>
                    <th>First Order</th>
                    <th>Last Order</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get customer sales with tax breakdown
                $customer_sales_sql = "SELECT
    c.customer_id,
    c.name AS customer_name,
    COUNT(DISTINCT so.so_id) AS order_count,
    SUM(so.total_amount) AS total_sales,
    SUM(so.tax_amount) AS total_tax,
    SUM(so.total_amount - so.tax_amount - so.discount_amount) AS net_sales,
    MIN(so.order_date) AS first_order_date,
    MAX(so.order_date) AS last_order_date
FROM customers c
JOIN sales_orders so ON so.customer_id = c.customer_id
WHERE so.status NOT IN ('cancelled', 'draft')
  AND so.order_date BETWEEN ? AND ?
GROUP BY c.customer_id, c.name
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
                        <td>Rs <?php echo number_format($row['total_tax'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['net_sales'], 2); ?></td>
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
        // Initialize chart variables first
        $chart_labels = [];
        $chart_inventory_values = [];
        $chart_potential_revenue = [];
        $has_chart_data = false;
        
        // Get all products for the chart
        $chart_products_sql = "SELECT 
                                p.name as product_name,
                                p.sku,
                                i.quantity_on_hand,
                                (i.quantity_on_hand * p.cost_price) as inventory_value,
                                (i.quantity_on_hand * p.cost_price * 1.5) as potential_revenue
                              FROM products p
                              JOIN inventory i ON p.product_id = i.product_id
                              WHERE p.is_active = 1
                              ORDER BY inventory_value DESC";
        $chart_products_result = $conn->query($chart_products_sql);
        
        if ($chart_products_result) {
            while ($row = $chart_products_result->fetch_assoc()) {
                $chart_labels[] = $row['product_name'] . ' (' . $row['sku'] . ')';
                $chart_inventory_values[] = (float)$row['inventory_value'];
                $chart_potential_revenue[] = (float)$row['potential_revenue'];
            }
            
            // Check if we have data
            $has_chart_data = !empty($chart_labels);
        }
        
        // Get inventory valuation
        $inventory_value = $conn->query("SELECT calculate_inventory_value() as total_value")->fetch_assoc()['total_value'];
        // Get inventory status summary from vw_inventory_status
        $inventory_summary_sql = "SELECT 
                                    COUNT(*) as total_products,
                                    SUM(quantity_on_hand) as total_quantity
                                  FROM vw_inventory_status
                                  WHERE 1";
        $inventory_summary = $conn->query($inventory_summary_sql)->fetch_assoc();

        // Stockout products
        $stockout_sql = "SELECT product_name, sku, category, available_quantity FROM vw_inventory_status WHERE available_quantity <= 0 ORDER BY available_quantity ASC";
        $stockout_result = $conn->query($stockout_sql);
        $stockout_products = [];
        while ($row = $stockout_result->fetch_assoc()) {
            $stockout_products[] = $row;
        }
        $stockout_result->close();
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
                <div class="summary-card-title">Inventory Value</div>
                <div class="summary-card-value">Rs <?php echo number_format($inventory_value, 2); ?></div>
            </div>
        </div>
        
        <div class="summary-cards">
            <?php if (!empty($stockout_products)): ?>
                <div class="summary-card">
                    <div class="summary-card-title">Stockout Products</div>
                    <div class="summary-card-value"><?php echo count($stockout_products); ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chart-container">
            <?php if ($has_chart_data): ?>
                <canvas id="inventoryTurnoverChart"></canvas>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <h4>No Inventory Data Available</h4>
                    <p>There are no products in inventory to display in the chart.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <h3 class="report-title">Inventory by Products</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Quantity On Hand</th>
                    <th>Inventory Value</th>
                    <th>Potential Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get all products with inventory data
                $product_inventory_sql = "SELECT 
                                            p.name as product_name,
                                            p.sku,
                                            c.name as category_name,
                                            i.quantity_on_hand,
                                            (i.quantity_on_hand * p.cost_price) as inventory_value,
                                            (i.quantity_on_hand * p.cost_price * 1.5) as potential_revenue
                                          FROM products p
                                          JOIN inventory i ON p.product_id = i.product_id
                                          LEFT JOIN categories c ON p.category_id = c.category_id
                                          WHERE p.is_active = 1
                                          ORDER BY inventory_value DESC";
                $product_inventory_result = $conn->query($product_inventory_sql);
                while ($row = $product_inventory_result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['sku']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo $row['quantity_on_hand']; ?></td>
                        <td>Rs <?php echo number_format($row['inventory_value'], 2); ?></td>
                        <td>Rs <?php echo number_format($row['potential_revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        

        

        
        <?php if ($has_chart_data): ?>
        <script>
        // Inventory Turnover Chart - All Products
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Chart script loaded');
            const canvas = document.getElementById('inventoryTurnoverChart');
            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }
            const ctx = canvas.getContext('2d');
            console.log('Chart data:', <?php echo json_encode($chart_labels); ?>);
            const inventoryTurnoverChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_labels); ?>,
                    datasets: [{
                        label: 'Inventory Value',
                        data: <?php echo json_encode($chart_inventory_values); ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Potential Revenue',
                        data: <?php echo json_encode($chart_potential_revenue); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
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
                        },
                        legend: {
                            display: true
                        }
                    }
                }
            });
        });
        </script>
        <?php endif; ?>
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

        <!-- Top Consumed Materials -->
        <h4 class="report-title">Top Consumed Materials</h4>
        <table>
            <thead>
                <tr><th>Material</th><th>SKU</th><th>Unit</th><th>Quantity Consumed</th></tr>
            </thead>
            <tbody>
            <?php
            $top_consumed_sql = "SELECT 
    rm.name, rm.sku, rm.unit_of_measure, SUM(rmt.quantity) as total_consumed
 FROM raw_material_transactions rmt
 JOIN raw_materials rm ON rmt.material_id = rm.material_id
 WHERE rmt.transaction_type = 'consumption' AND rmt.created_at BETWEEN ? AND ?
 GROUP BY rmt.material_id, rm.name, rm.sku, rm.unit_of_measure
 ORDER BY total_consumed DESC LIMIT 5";
            $stmt = $conn->prepare($top_consumed_sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $top_consumed_result = $stmt->get_result();
            while ($row = $top_consumed_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['sku']); ?></td>
                    <td><?php echo htmlspecialchars($row['unit_of_measure']); ?></td>
                    <td><?php echo number_format($row['total_consumed'], 2); ?></td>
                </tr>
            <?php endwhile; $stmt->close(); ?>
            </tbody>
        </table>

        <!-- Materials Below Reorder Level -->
        <h4 class="report-title">Materials Below Reorder Level</h4>
        <table>
            <thead>
                <tr><th>Material</th><th>SKU</th><th>Unit</th><th>On Hand</th><th>Reorder Level</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php
            $reorder_sql = "SELECT 
    rm.name, rm.sku, rm.unit_of_measure, rmi.quantity_on_hand, rm.reorder_level
 FROM raw_materials rm
 JOIN raw_material_inventory rmi ON rm.material_id = rmi.material_id
 WHERE rm.is_active = 1 AND rmi.quantity_on_hand < rm.reorder_level
 ORDER BY rmi.quantity_on_hand ASC";
            $reorder_result = $conn->query($reorder_sql);
            while ($row = $reorder_result->fetch_assoc()):
                $status_class = ($row['quantity_on_hand'] <= 0) ? 'text-danger' : 'text-warning';
                $status_text = ($row['quantity_on_hand'] <= 0) ? 'Out of Stock' : 'Low Stock';
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['sku']); ?></td>
                    <td><?php echo htmlspecialchars($row['unit_of_measure']); ?></td>
                    <td><?php echo number_format($row['quantity_on_hand'], 2); ?></td>
                    <td><?php echo number_format($row['reorder_level'], 2); ?></td>
                    <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                </tr>
            <?php endwhile; $reorder_result->close(); ?>
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

        // Profit by Customer
        $profit_by_customer_sql = "SELECT 
                                    c.customer_id,
                                    c.name AS customer_name,
                                    SUM(so.total_amount - so.tax_amount - so.discount_amount) as total_sales,
                                    SUM(soi.quantity * p.cost_price) as total_cost,
                                    SUM(so.total_amount - so.tax_amount - so.discount_amount - (soi.quantity * p.cost_price)) as gross_profit,
                                    (SUM(so.total_amount - so.tax_amount - so.discount_amount - (soi.quantity * p.cost_price)) / 
                                     SUM(so.total_amount - so.tax_amount - so.discount_amount)) * 100 as gross_margin
                                  FROM sales_orders so
                                  JOIN sales_order_items soi ON so.so_id = soi.so_id
                                  JOIN products p ON soi.product_id = p.product_id
                                  LEFT JOIN customers c ON so.customer_id = c.customer_id
                                  WHERE so.order_date BETWEEN ? AND ?
                                  AND so.status NOT IN ('cancelled', 'draft')
                                  GROUP BY c.customer_id, c.name
                                  ORDER BY gross_profit DESC";
        $stmt = $conn->prepare($profit_by_customer_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $profit_by_customer_result = $stmt->get_result();
        $profit_by_customer_data = [];
        while ($row = $profit_by_customer_result->fetch_assoc()) {
            $profit_by_customer_data[] = $row;
        }
        $stmt->close();

        // Top Products by Profit
        $top_products_profit_sql = "SELECT 
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
                                      ORDER BY profit DESC LIMIT 5";
        $stmt = $conn->prepare($top_products_profit_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $top_products_profit_result = $stmt->get_result();
        $top_products_profit_data = [];
        while ($row = $top_products_profit_result->fetch_assoc()) {
            $top_products_profit_data[] = $row;
        }
        $stmt->close();

        // Top Categories by Profit
        $top_categories_profit_sql = "SELECT 
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
                                      ORDER BY profit DESC LIMIT 5";
        $stmt = $conn->prepare($top_categories_profit_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $top_categories_profit_result = $stmt->get_result();
        $top_categories_profit_data = [];
        while ($row = $top_categories_profit_result->fetch_assoc()) {
            $top_categories_profit_data[] = $row;
        }
        $stmt->close();

        // Gross Margin Trend (true gross margin per period)
        $gross_margin_trend_sql = "SELECT 
            DATE_FORMAT(so.order_date, '%Y-%m') as period,
            SUM(so.total_amount - so.tax_amount - so.discount_amount) as net_sales,
            SUM(soi.quantity * p.cost_price) as total_cost,
            (SUM(so.total_amount - so.tax_amount - so.discount_amount) - SUM(soi.quantity * p.cost_price)) / NULLIF(SUM(so.total_amount - so.tax_amount - so.discount_amount), 0) * 100 as gross_margin
        FROM sales_orders so
        JOIN sales_order_items soi ON so.so_id = soi.so_id
        JOIN products p ON soi.product_id = p.product_id
        WHERE so.order_date BETWEEN ? AND ?
          AND so.status NOT IN ('cancelled', 'draft')
        GROUP BY period
        ORDER BY period ASC";
        $stmt = $conn->prepare($gross_margin_trend_sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $gross_margin_trend_result = $stmt->get_result();
        $gross_margin_trend_labels = [];
        $gross_margin_trend_data = [];
        while ($row = $gross_margin_trend_result->fetch_assoc()) {
            $gross_margin_trend_labels[] = date('M Y', strtotime($row['period'] . '-01'));
            $gross_margin_trend_data[] = isset($row['gross_margin']) ? round($row['gross_margin'], 2) : 0;
        }
        $stmt->close();
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

        <h3 class="report-title">Customer Profitability Analysis</h3>
        <div class="summary-cards">
            <?php foreach ($profit_by_customer_data as $customer): ?>
                <div class="summary-card">
                    <div class="summary-card-title">Profit by Customer: <?php echo htmlspecialchars($customer['customer_name']); ?></div>
                    <div class="summary-card-value">Rs <?php echo number_format($customer['gross_profit'], 2); ?></div>
                </div>
            <?php endforeach; ?>
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
                    labels: <?php echo json_encode($gross_margin_trend_labels); ?>,
                    datasets: [{
                        label: 'Gross Margin (%)',
                        data: <?php echo json_encode($gross_margin_trend_data); ?>,
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
                                    return value + '%';
                                }
                            },
                            title: {
                                display: true,
                                text: 'Gross Margin (%)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
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

<div class="print-footer">
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div>Prepared By</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div>Approved By</div>
        </div>
    </div>
    <div class="thank-you">Thank you for your business!</div>
</div>

<?php 
$conn->close();
?>
</body>
</html>