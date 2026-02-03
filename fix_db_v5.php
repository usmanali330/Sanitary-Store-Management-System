<?php
include 'includes/header.php'; // Use header to get $conn and everything

function column_exists($conn, $table, $column) {
    $result = $conn->query("DESCRIBE `$table` `$column` ");
    return $result && $result->num_rows > 0;
}

if (!column_exists($conn, 'customers', 'balance')) {
    echo "Adding balance to customers...\n";
    $conn->query("ALTER TABLE customers ADD COLUMN balance DECIMAL(10,2) DEFAULT 0.00 AFTER type");
}

if (!column_exists($conn, 'sales', 'due_amount')) {
     echo "Adding due_amount to sales...\n";
    $conn->query("ALTER TABLE sales ADD COLUMN due_amount DECIMAL(10,2) DEFAULT 0.00");
}

echo "Done\n";
?>
