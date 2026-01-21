<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sanitary_store_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-login logic (Bypass Login)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Default to ID 1 (Admin)
    $_SESSION['username'] = 'System Admin';
    $_SESSION['role'] = 'admin';
}

// Helper function for Auth
function isAuthenticated() {
    return true;
}

function isAdmin() {
    return true;
}

function requireAuth() {
    // Authentication disabled
}

function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}
?>
