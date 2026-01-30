<?php
$current_page = basename($_SERVER['PHP_SELF']);
require_once 'config.php';
?>
<div class="sidebar">
    <div class="brand">
        <i class="fa-solid fa-faucet-drip"></i>
        <span>Haji Baba</span>
    </div>
    <ul class="nav-links">
        <li class="nav-item">
            <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-grip"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="pos.php" class="<?= $current_page == 'pos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-cash-register"></i> POS / Billing
            </a>
        </li>
        <li class="nav-item">
            <a href="products.php" class="<?= $current_page == 'products.php' || $current_page == 'product_form.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-box-open"></i> Products
            </a>
        </li>
        <li class="nav-item">
            <a href="inventory.php" class="<?= $current_page == 'inventory.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-boxes-stacked"></i> Inventory
            </a>
        </li>
        <li class="nav-item">
            <a href="categories.php" class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> Categories
            </a>
        </li>
        <li class="nav-item">
            <a href="sales.php" class="<?= $current_page == 'sales.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i> Sales History
            </a>
        </li>
        <li class="nav-item">
            <a href="customers.php" class="<?= $current_page == 'customers.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a href="pending_dues.php" class="<?= $current_page == 'pending_dues.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i> Pending Dues
            </a>
        </li>
        <li class="nav-item">
            <a href="suppliers.php" class="<?= $current_page == 'suppliers.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-truck"></i> Suppliers
            </a>
        </li>
        <?php if (isAdmin()): ?>

        <li class="nav-item">
            <a href="reports.php" class="<?= $current_page == 'reports.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Reports
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>
