<?php
require_once 'includes/config.php';

// Add top_list to categories
$sql1 = "ALTER TABLE categories ADD COLUMN top_list ENUM('hardware', 'sanitary', 'ragrai') NOT NULL DEFAULT 'sanitary'";
if ($conn->query($sql1)) {
    echo "Categories table updated.<br>";
} else {
    echo "Error updating categories: " . $conn->error . "<br>";
}

// Add top_list to products
$sql2 = "ALTER TABLE products ADD COLUMN top_list ENUM('hardware', 'sanitary', 'ragrai') NOT NULL DEFAULT 'sanitary'";
if ($conn->query($sql2)) {
    echo "Products table updated.<br>";
} else {
    echo "Error updating products: " . $conn->error . "<br>";
}

echo "Database update completed.";
?>
