<?php
include 'includes/config.php';

echo "Checking database structure (v2)...\n";

// Add balance to customers table
$res = $conn->query("SHOW COLUMNS FROM customers LIKE 'balance'");
if ($res->num_rows == 0) {
    echo "Adding 'balance' column to 'customers' table...\n";
    $conn->query("ALTER TABLE customers ADD COLUMN balance DECIMAL(10,2) DEFAULT 0.00 AFTER type");
} else {
    echo "'balance' column already exists in 'customers'.\n";
}

// Ensure other tables/columns are present if needed
// For example, if 'index.php' expects 'due_amount' or 'balance'
// The user's index.php might have had:
// $dues_sql = "SELECT SUM(balance) as total_dues FROM customers";
// Let's make sure 'balance' is there.

echo "Database fix v2 completed.\n";
?>
