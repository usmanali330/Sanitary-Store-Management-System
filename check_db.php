<?php
require 'includes/config.php';
echo "--- TABLES ---\n";
$res = $conn->query('SHOW TABLES');
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
echo "--- USERS ---\n";
$res = $conn->query('SELECT id, username, role FROM users');
if ($res) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Users table error: " . $conn->error . "\n";
}
