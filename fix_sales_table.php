<?php
/**
 * Fix Sales Table Structure
 * This script adds missing columns to the sales and customers tables
 */

require_once 'includes/config.php';

echo "<h2>Database Structure Fix Script</h2>";
echo "<pre>";

// Check and fix sales table
echo "Checking sales table...\n";

// Check if paid_amount column exists in sales table
$result = $conn->query("SHOW COLUMNS FROM sales LIKE 'paid_amount'");
if ($result->num_rows == 0) {
    echo "Adding 'paid_amount' column to sales table... ";
    $conn->query("ALTER TABLE sales ADD COLUMN paid_amount DECIMAL(10, 2) DEFAULT 0 AFTER total_amount");
    echo $conn->error ? "ERROR: " . $conn->error . "\n" : "SUCCESS\n";
} else {
    echo "'paid_amount' column already exists.\n";
}

// Check if due_amount column exists in sales table
$result = $conn->query("SHOW COLUMNS FROM sales LIKE 'due_amount'");
if ($result->num_rows == 0) {
    echo "Adding 'due_amount' column to sales table... ";
    $conn->query("ALTER TABLE sales ADD COLUMN due_amount DECIMAL(10, 2) DEFAULT 0 AFTER paid_amount");
    echo $conn->error ? "ERROR: " . $conn->error . "\n" : "SUCCESS\n";
} else {
    echo "'due_amount' column already exists.\n";
}

// Check and fix customers table
echo "\nChecking customers table...\n";

// Check if balance column exists in customers table
$result = $conn->query("SHOW COLUMNS FROM customers LIKE 'balance'");
if ($result->num_rows == 0) {
    echo "Adding 'balance' column to customers table... ";
    $conn->query("ALTER TABLE customers ADD COLUMN balance DECIMAL(10, 2) DEFAULT 0");
    echo $conn->error ? "ERROR: " . $conn->error . "\n" : "SUCCESS\n";
} else {
    echo "'balance' column already exists.\n";
}

// Check and fix products table for top_list column
echo "\nChecking products table...\n";

$result = $conn->query("SHOW COLUMNS FROM products LIKE 'top_list'");
if ($result->num_rows == 0) {
    echo "Adding 'top_list' column to products table... ";
    $conn->query("ALTER TABLE products ADD COLUMN top_list VARCHAR(50) DEFAULT 'sanitary'");
    echo $conn->error ? "ERROR: " . $conn->error . "\n" : "SUCCESS\n";
} else {
    echo "'top_list' column already exists.\n";
}

echo "\n=== Current Sales Table Structure ===\n";
$result = $conn->query("DESCRIBE sales");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL OK' : 'NOT NULL') . "\n";
    }
}

echo "\n=== All Fixes Applied Successfully! ===\n";
echo "</pre>";
echo "<p><a href='pos.php'>Go to POS</a></p>";
?>
