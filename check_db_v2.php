<?php
require 'includes/config.php';
echo "--- TABLES ---\n";
$res = $conn->query('SHOW TABLES');
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
echo "\n--- USERS CONTENT ---\n";
$res = $conn->query('SELECT * FROM users');
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Username: " . $row['username'] . " | Role: " . $row['role'] . "\n";
    }
} else {
    echo "Users table error: " . $conn->error . "\n";
}

echo "\n--- SALES TABLE SCHEMA ---\n";
$res = $conn->query('SHOW CREATE TABLE sales');
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n";
}

echo "\n--- PAYMENTS TABLE SCHEMA ---\n";
$res = $conn->query('SHOW CREATE TABLE payments');
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n";
}
