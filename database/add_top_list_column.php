<?php
/**
 * Database Migration Script
 * Adds top_list column to categories table if it doesn't exist
 * 
 * Run this file once to update your database:
 * http://localhost/Sanitary-Store-Management-System/database/add_top_list_column.php
 */

include '../includes/config.php';

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM categories LIKE 'top_list'");

if ($check && $check->num_rows == 0) {
    // Column doesn't exist, add it
    $sql = "ALTER TABLE categories ADD COLUMN top_list VARCHAR(50) DEFAULT 'sanitary' AFTER name";
    
    if ($conn->query($sql)) {
        echo "<h2>✅ Success!</h2>";
        echo "<p>The 'top_list' column has been added to the categories table.</p>";
        echo "<p>Default value set to 'sanitary'.</p>";
        echo "<p><a href='../categories.php'>Go to Categories</a></p>";
    } else {
        echo "<h2>❌ Error</h2>";
        echo "<p>Failed to add column: " . $conn->error . "</p>";
    }
} else {
    echo "<h2>ℹ️ Column Already Exists</h2>";
    echo "<p>The 'top_list' column already exists in the categories table.</p>";
    echo "<p><a href='../categories.php'>Go to Categories</a></p>";
}

$conn->close();
?>

