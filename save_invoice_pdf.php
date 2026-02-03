<?php
include 'includes/config.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$invoice_id = $_POST['invoice_id'] ?? 'unknown';
$target_dir = "uploads/invoices/";

// Create directory if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_extension = 'pdf';
$filename = 'Invoice_' . str_pad($invoice_id, 5, '0', STR_PAD_LEFT) . '_' . time() . '.' . $file_extension;
$target_file = $target_dir . $filename;

if (move_uploaded_file($_FILES['pdf']['tmp_name'], $target_file)) {
    $file_url = $target_file; // Relative path
    $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $file_url;
    
    echo json_encode([
        'success' => true,
        'url' => $file_url,
        'full_url' => $full_url
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
}
?>

