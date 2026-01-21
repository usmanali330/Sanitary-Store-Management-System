<?php
require_once 'includes/config.php';
requireAuth();
header('Content-Type: application/json');

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$customer_id = $data['customer_id']; // Can be null for walk-in
$items = $data['items'];
$discount = $data['discount'] ?? 0;
$tax_rate = $data['tax_rate'] ?? 0;
$user_id = $_SESSION['user_id'];

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Calculate Totals (Verify server-side prices)
    $subtotal = 0;
    $verified_items = [];

    foreach ($items as $item) {
        $pid = $item['id'];
        $qty = $item['quantity'];

        // Fetch current product state
        $stmt = $conn->prepare("SELECT price, quantity FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();

        if (!$product) {
            throw new Exception("Product ID $pid not found.");
        }

        if ($product['quantity'] < $qty) {
            throw new Exception("Insufficient stock for Product ID $pid. Available: " . $product['quantity']);
        }

        $price = $product['price'];
        $line_total = $price * $qty;
        $subtotal += $line_total;

        $verified_items[] = [
            'id' => $pid,
            'quantity' => $qty,
            'price' => $price,
            'total' => $line_total
        ];

        // 2. Update Stock
        $new_qty = $product['quantity'] - $qty;
        $update_stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_qty, $pid);
        $update_stmt->execute();
    }

    // Calculate Final Amounts
    $tax_amount = ($subtotal * $tax_rate) / 100;
    $total_amount = ($subtotal + $tax_amount) - $discount;

    // 3. Create Sale Record
    $sale_stmt = $conn->prepare("INSERT INTO sales (customer_id, user_id, subtotal, tax, discount, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
    $sale_stmt->bind_param("iidddd", $customer_id, $user_id, $subtotal, $tax_amount, $discount, $total_amount);
    $sale_stmt->execute();
    $sale_id = $conn->insert_id;

    // 4. Create Sale Items Records
    $item_stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
    foreach ($verified_items as $item) {
        $item_stmt->bind_param("iiidd", $sale_id, $item['id'], $item['quantity'], $item['price'], $item['total']);
        $item_stmt->execute();
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'sale_id' => $sale_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
