<?php
// Clear any existing output buffers to ensure clean JSON
while (ob_get_level()) {
    ob_end_clean();
}

require_once 'includes/config.php';
requireAuth();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $type = $_POST['type'] ?? 'regular';
    $address = $_POST['address'] ?? '';

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer name is required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO customers (name, phone, address, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $address, $type);
    
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        echo json_encode([
            'status' => 'success', 
            'id' => $id, 
            'name' => $name, 
            'type' => $type
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
