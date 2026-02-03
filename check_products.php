<?php
include 'includes/config.php';

function checkColumn($conn, $table, $column) {
    try {
        $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($res && $res->num_rows > 0) return true;
    } catch(Exception $e) {}
    return false;
}

// Check products for cost_price
if (!checkColumn($conn, 'products', 'cost_price')) {
    echo "Adding cost_price to products...\n";
    $conn->query("ALTER TABLE products ADD COLUMN cost_price DECIMAL(10,2) DEFAULT 0.00 AFTER price");
} else {
    echo "cost_price exists in products.\n";
}

// Check products for quantity (just in case)
if (!checkColumn($conn, 'products', 'quantity')) {
    echo "Adding quantity to products...\n";
    $conn->query("ALTER TABLE products ADD COLUMN quantity INT DEFAULT 0 AFTER price");
} else {
    echo "quantity exists in products.\n";
}

echo "Products check done.\n";
?>
