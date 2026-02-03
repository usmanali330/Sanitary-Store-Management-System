<?php
include 'includes/config.php';

function checkAndAddColumn($conn, $table, $column, $definition, $after = '') {
    echo "Checking $table for $column...\n";
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (!$res) {
        die("Error checking $table: " . $conn->error . "\n");
    }
    if ($res->num_rows == 0) {
        echo "Adding '$column' to '$table'...\n";
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition" . ($after ? " AFTER `$after`" : "");
        if (!$conn->query($sql)) {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } else {
        echo "'$column' already exists in '$table'.\n";
    }
}

checkAndAddColumn($conn, 'customers', 'balance', 'DECIMAL(10,2) DEFAULT 0.00', 'type');
checkAndAddColumn($conn, 'sales', 'due_amount', 'DECIMAL(10,2) DEFAULT 0.00', 'paid_amount');
checkAndAddColumn($conn, 'sales', 'paid_amount', 'DECIMAL(10,2) DEFAULT 0.00', 'total_amount');

// Check if payments table exists
$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "'payments' table ensured.\n";

echo "Check complete.\n";
?>
