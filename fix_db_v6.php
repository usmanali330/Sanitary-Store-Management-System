<?php
include 'includes/config.php';

function column_exists($conn, $table, $column) {
    try {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($result && $result->num_rows > 0) {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}

if (!column_exists($conn, 'customers', 'balance')) {
    echo "Adding balance to customers...\n";
    $conn->query("ALTER TABLE customers ADD COLUMN balance DECIMAL(10,2) DEFAULT 0.00 AFTER type");
} else {
    echo "Balance already exists in customers.\n";
}

if (!column_exists($conn, 'sales', 'due_amount')) {
     echo "Adding due_amount to sales...\n";
    $conn->query("ALTER TABLE sales ADD COLUMN due_amount DECIMAL(10,2) DEFAULT 0.00");
} else {
    echo "Due_amount already exists in sales.\n";
}

$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

echo "Done\n";
?>
