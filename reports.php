<?php
include 'includes/header.php';
requireAuth();

if (!isAdmin()) {
    echo "<div class='card'>Access Denied. Admins Only.</div>";
    include 'includes/footer.php';
    exit;
}

// 1. Stock Valuation
$stock_val_sql = "SELECT SUM(quantity * cost_price) as total_cost, SUM(quantity * price) as total_value FROM products";
$stock_val = $conn->query($stock_val_sql)->fetch_assoc();
$total_cost = $stock_val['total_cost'] ?? 0;
$total_value = $stock_val['total_value'] ?? 0;
$potential_profit = $total_value - $total_cost;

// 2. Category Wise Stock
$cat_report_sql = "SELECT c.name, SUM(p.quantity) as total_qty, SUM(p.quantity * p.price) as total_val 
                   FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   GROUP BY c.name";
$cat_report = $conn->query($cat_report_sql);

// 3. Daily Sales (Last 30 Days)
$daily_sales_sql = "SELECT DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue 
                    FROM sales 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    GROUP BY DATE(created_at) 
                    ORDER BY date DESC";
$daily_sales = $conn->query($daily_sales_sql);

?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Stock Summary Card -->
    <div class="card">
        <h3 style="margin-bottom: 1rem; color: var(--primary-dark);">Stock Valuation</h3>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span>Total Cost Value:</span>
            <span style="font-weight: 600;"><?= formatPrice($total_cost) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span>Total Selling Value:</span>
            <span style="font-weight: 600;"><?= formatPrice($total_value) ?></span>
        </div>
        <hr style="margin: 0.5rem 0; border: 0; border-top: 1px dashed #ccc;">
        <div style="display: flex; justify-content: space-between; color: var(--success-color);">
            <span>Potential Profit:</span>
            <span style="font-weight: 700;"><?= formatPrice($potential_profit) ?></span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Category Report -->
    <div class="card">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Category-wise Inventory</h3>
        <div class="table-container" style="box-shadow: none; padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $cat_report->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['total_qty'] ?></td>
                        <td><?= formatPrice($row['total_val']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Sales Report -->
    <div class="card">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Last 30 Days Sales</h3>
        <div class="table-container" style="box-shadow: none; padding: 0; max-height: 300px; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $daily_sales->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M d', strtotime($row['date'])) ?></td>
                        <td><?= $row['count'] ?></td>
                        <td><?= formatPrice($row['revenue']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
