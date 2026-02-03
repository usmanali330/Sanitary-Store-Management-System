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

// Inject compact stylesheet into HTML output to reduce paddings/margins and avoid extra scrolling
if (php_sapi_name() !== 'cli') {
    ob_start(function ($buffer) {
        $cssLink = '<link rel="stylesheet" href="/Sanitary-Store-Management-System/assets/css/compact.css">';
        if (stripos($buffer, '</head>') !== false) {
            // insert before closing head
            return preg_replace('/<\/head>/i', $cssLink . "\n</head>", $buffer, 1);
        }
        // fallback: prepend if no <head> found
        return $cssLink . "\n" . $buffer;
    });
}

// Auto-login logic (Bypass Login)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['username'])) {
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

// Development mode toggle: set to false on production
if (!defined('DEV')) {
    define('DEV', true);
}

/**
 * Safe query helper — runs a query and logs details on failure (DEV mode).
 * Returns mysqli_result|false (same as $conn->query) but logs the error for debugging.
 */
function db_query($sql) {
    global $conn;
    $res = $conn->query($sql);
    if ($res === false && defined('DEV') && DEV) {
        error_log("DB Query Error: {$conn->error} | SQL: {$sql}");
    }
    return $res;
}

/**
 * Convenience helper that converts a result to an array safely.
 * Returns empty array on failure or if no rows.
 */
function fetch_all_assoc_safe($result) {
    if (!$result || !($result instanceof mysqli_result)) {
        return [];
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}
?>
