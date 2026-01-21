<?php include 'includes/header.php'; ?>

<?php
// Fetch Statistics

// 1. Total Products
$prod_sql = "SELECT COUNT(*) as count FROM products";
$prod_result = $conn->query($prod_sql);
$total_products = $prod_result->fetch_assoc()['count'];

// 2. Today's Sales
$today = date('Y-m-d');
$sales_sql = "SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM sales WHERE DATE(created_at) = '$today'";
$sales_result = $conn->query($sales_sql);
$sales_data = $sales_result->fetch_assoc();
$today_sales_count = $sales_data['count'];
$today_revenue = $sales_data['revenue'] ?? 0;

// 3. Monthly Revenue
$current_month = date('Y-m');
$month_sql = "SELECT SUM(total_amount) as revenue FROM sales WHERE DATE_FORMAT(created_at, '%Y-%m') = '$current_month'";
$month_result = $conn->query($month_sql);
$monthly_revenue = $month_result->fetch_assoc()['revenue'] ?? 0;

// 4. Low Stock
$low_stock_sql = "SELECT COUNT(*) as count FROM products WHERE quantity < 10";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock = $low_stock_result->fetch_assoc()['count'];

// 5. Recent Transactions
$recent_sql = "SELECT s.id, c.name as customer, u.username as staff, s.total_amount, s.created_at 
               FROM sales s 
               LEFT JOIN customers c ON s.customer_id = c.id 
               LEFT JOIN users u ON s.user_id = u.id 
               ORDER BY s.created_at DESC LIMIT 5";
$recent_result = $conn->query($recent_sql);

?>

<div class="card-grid">
    <div class="card stat-card">
        <div class="stat-info">
            <h3><?= $total_products ?></h3>
            <p>Total Products</p>
        </div>
        <div class="stat-icon bg-blue-light">
            <i class="fa-solid fa-box"></i>
        </div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-info">
            <h3><?= formatPrice($today_revenue) ?></h3>
            <p>Today's Revenue (<?= $today_sales_count ?> sales)</p>
        </div>
        <div class="stat-icon bg-green-light">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-info">
            <h3><?= formatPrice($monthly_revenue) ?></h3>
            <p>Monthly Revenue</p>
        </div>
        <div class="stat-icon bg-purple-light">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-info">
            <h3><?= $low_stock ?></h3>
            <p>Low Stock Items</p>
        </div>
        <div class="stat-icon bg-orange-light">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.25rem;">Recent Transactions</h3>
        <a href="sales.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    
    <div class="table-container" style="box-shadow: none; padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Staff</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_result->num_rows > 0): ?>
                    <?php while($row = $recent_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($row['customer'] ?: 'Walk-in') ?></td>
                        <td><?= htmlspecialchars($row['staff']) ?></td>
                        <td style="font-weight: 600;"><?= formatPrice($row['total_amount']) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                        <td>
                            <a href="invoice.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="View Invoice">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-light);">No recent transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
