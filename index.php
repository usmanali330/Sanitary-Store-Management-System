<?php include 'includes/header.php'; ?>

<?php
// Fetch Statistics

// 1. Total Products
$prod_sql = "SELECT COUNT(*) as count FROM products";
$prod_result = $conn->query($prod_sql);
$total_products = ($prod_result && $prod_result->num_rows > 0) ? $prod_result->fetch_assoc()['count'] : 0;

// 2. Today's Sales
$today = date('Y-m-d');
$sales_sql = "SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM sales WHERE DATE(created_at) = '$today'";
$sales_result = $conn->query($sales_sql);
$sales_data = ($sales_result && $sales_result->num_rows > 0) ? $sales_result->fetch_assoc() : ['count' => 0, 'revenue' => 0];
$today_sales_count = $sales_data['count'];
$today_revenue = $sales_data['revenue'] ?? 0;

// 3. Monthly Revenue
$current_month = date('Y-m');
$month_sql = "SELECT SUM(total_amount) as revenue FROM sales WHERE DATE_FORMAT(created_at, '%Y-%m') = '$current_month'";
$month_result = $conn->query($month_sql);
$monthly_revenue = ($month_result && $month_result->num_rows > 0) ? ($month_result->fetch_assoc()['revenue'] ?? 0) : 0;

// 4. Low Stock
$low_stock_sql = "SELECT COUNT(*) as count FROM products WHERE quantity < 10";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock = ($low_stock_result && $low_stock_result->num_rows > 0) ? $low_stock_result->fetch_assoc()['count'] : 0;

// 5. Recent Transactions
$recent_sql = "SELECT s.id, c.name as customer, u.username as staff, s.total_amount, s.created_at 
               FROM sales s 
               LEFT JOIN customers c ON s.customer_id = c.id 
               LEFT JOIN users u ON s.user_id = u.id 
               ORDER BY s.created_at DESC LIMIT 5";
$recent_result = $conn->query($recent_sql);

// 6. Low Stock List
$low_stock_list_sql = "SELECT name, quantity, brand FROM products WHERE quantity < 10 ORDER BY quantity ASC LIMIT 5";
$low_stock_list_result = $conn->query($low_stock_list_sql);

// 7. Total Inventory Value
$stock_value_sql = "SELECT SUM(quantity * cost_price) as total_value FROM products";
$stock_value_result = $conn->query($stock_value_sql);
$total_inventory_value = ($stock_value_result && $stock_value_result->num_rows > 0) ? ($stock_value_result->fetch_assoc()['total_value'] ?? 0) : 0;

// 8. Total Pending Dues
$dues_sql = "SELECT SUM(balance) as total_dues FROM customers WHERE balance > 0";
$dues_result = $conn->query($dues_sql);
$total_dues = ($dues_result && $dues_result->num_rows > 0) ? ($dues_result->fetch_assoc()['total_dues'] ?? 0) : 0;

// 9. Total Potential Profit (from inventory)
$profit_sql = "SELECT SUM((price - cost_price) * quantity) as total_profit FROM products";
$profit_result = $conn->query($profit_sql);
$total_potential_profit = ($profit_result && $profit_result->num_rows > 0) ? ($profit_result->fetch_assoc()['total_profit'] ?? 0) : 0;

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

    <div class="card stat-card">
        <div class="stat-info">
            <h3><?= formatPrice($total_inventory_value) ?></h3>
            <p>Total Stock Value</p>
        </div>
        <div class="stat-icon bg-purple-light">
            <i class="fa-solid fa-coins"></i>
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-info">
            <h3 style="color: var(--danger);"><?= formatPrice($total_dues) ?></h3>
            <p>Total Pending Dues</p>
        </div>
        <div class="stat-icon" style="background: var(--danger-light); color: var(--danger);">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-info">
            <h3 style="color: var(--success);"><?= formatPrice($total_potential_profit) ?></h3>
            <p>Potential Profit (Stock)</p>
        </div>
        <div class="stat-icon" style="background: var(--success-light); color: var(--success);">
            <i class="fa-solid fa-chart-line"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Recent Transactions -->
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
                            <td style="font-weight: 600;"><?= formatPrice($row['total_amount']) ?></td>
                            <td><?= date('M d, h:i A', strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="invoice.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="View Invoice">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-light);">No recent transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem;">Low Stock</h3>
            <a href="inventory.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        
        <div class="table-container" style="box-shadow: none; padding: 0;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align: right;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($low_stock_list_result->num_rows > 0): ?>
                        <?php while($row = $low_stock_list_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500; font-size: 0.9rem;"><?= htmlspecialchars($row['name']) ?></div>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($row['brand']) ?></small>
                            </td>
                            <td style="text-align: right;">
                                <span class="badge badge-warning"><?= $row['quantity'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; color: var(--text-light);">No low stock items.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
