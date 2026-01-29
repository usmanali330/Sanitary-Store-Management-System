<?php
include '../includes/config.php';

$sql = "ALTER TABLE products ADD COLUMN color VARCHAR(50) AFTER size";
if ($conn->query($sql) === TRUE) {
    echo "Column 'color' added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
