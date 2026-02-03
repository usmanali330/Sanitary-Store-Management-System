<?php
require 'includes/config.php';
$res = $conn->query('SELECT id, username FROM users');
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['username'] . "\n";
}
