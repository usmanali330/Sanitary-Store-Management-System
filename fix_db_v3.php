<?php
include 'includes/config.php';

echo "Comprehensive Database Check...\n";

// 1. Check customers table for balance
$res = $conn->query("SHOW COLUMNS FROM customers LIKE 'balance'");
if ($res->num_rows == 0) {
    echo "Adding 'balance' column to 'customers'...\n";
    $conn->query("ALTER TABLE customers ADD COLUMN balance DECIMAL(10,2) DEFAULT 0.00 AFTER type");
} else {
    echo "'balance' already in 'customers'.\n";
}

// 2. Check sales table for due_amount
$res = $conn->query("SHOW COLUMNS FROM sales LIKE 'due_amount'");
if ($res->num_rows == 0) {
    echo "Adding 'due_amount' column to 'sales'...\n";
    $conn->query("ALTER TABLE sales ADD COLUMN due_amount DECIMAL(10,2) DEFAULT 0.00 AFTER paid_amount");
} else {
    echo "'due_amount' already in 'sales'.\n";
}

// 3. Create payments table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "'payments' table checked.\n";

echo "Database check complete.\n";
?>
