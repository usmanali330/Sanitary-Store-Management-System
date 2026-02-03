<?php
include 'includes/config.php';
$r = $conn->query('SHOW TABLES');
while($row = $r->fetch_row()) {
    echo $row[0] . "\n";
}
?>
