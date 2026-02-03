<?php
require_once 'includes/config.php';
requireAuth();
header('Content-Type: application/json');

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['sale_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$sale_id = $data['sale_id'];
$customer_id = $data['customer_id'];
$items = $data['items'];
$discount = $data['discount'] ?? 0;
$user_id = $_SESSION['user_id'] ?? null;

// Verify if user_id exists in database to avoid foreign key constraint error
if ($user_id) {
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    if ($check_user->get_result()->num_rows === 0) {
        $user_id = null; // Set to null if user doesn't exist (allowed by DB schema)
    }
}

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Cart cannot be empty']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Fetch ORIGINAL sale and items to revert changes
    $orig_sale_stmt = $conn->prepare("SELECT * FROM sales WHERE id = ? FOR UPDATE");
    $orig_sale_stmt->bind_param("i", $sale_id);
    $orig_sale_stmt->execute();
    $orig_sale = $orig_sale_stmt->get_result()->fetch_assoc();

    if (!$orig_sale) {
        throw new Exception("Original sale record not found.");
    }

    $orig_items_stmt = $conn->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
    $orig_items_stmt->bind_param("i", $sale_id);
    $orig_items_stmt->execute();
    $orig_items = $orig_items_stmt->get_result();

    // 2. REVERT Stock Changes
    while ($item = $orig_items->fetch_assoc()) {
        $revert_stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $revert_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $revert_stmt->execute();
    }

    // 3. REVERT Customer Balance Correction
    if ($orig_sale['customer_id'] && $orig_sale['due_amount'] != 0) {
        $revert_bal = $conn->prepare("UPDATE customers SET balance = balance - ? WHERE id = ?");
        $revert_bal->bind_param("di", $orig_sale['due_amount'], $orig_sale['customer_id']);
        $revert_bal->execute();
    }

    // 4. Delete Old Sale Items
    $del_items = $conn->prepare("DELETE FROM sale_items WHERE sale_id = ?");
    $del_items->bind_param("i", $sale_id);
    $del_items->execute();

    // 5. Calculate New Totals and Verify New Stock
    $subtotal = 0;
    $verified_items = [];

    foreach ($items as $item) {
        $pid = $item['id'];
        $qty = $item['quantity'];

        // Fetch current product state (AFTER revert)
        $stmt = $conn->prepare("SELECT price, quantity FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            throw new Exception("Product ID $pid not found.");
        }

        if ($product['quantity'] < $qty) {
             throw new Exception("Insufficient stock for product " . $item['name'] . ". Available: " . $product['quantity']);
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

        // Deduct New Stock
        $deduct_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $deduct_stmt->bind_param("ii", $qty, $pid);
        $deduct_stmt->execute();
    }

    $final_total = $subtotal - $discount;
    $paid_amount = isset($data['paid_amount']) ? floatval($data['paid_amount']) : $final_total;
    $due_amount = $final_total - $paid_amount;

    // 6. UPDATE Sale Record
    $upd_sale = $conn->prepare("UPDATE sales SET customer_id = ?, subtotal = ?, discount = ?, total_amount = ?, paid_amount = ?, due_amount = ? WHERE id = ?");
    $upd_sale->bind_param("idddddi", $customer_id, $subtotal, $discount, $final_total, $paid_amount, $due_amount, $sale_id);
    if (!$upd_sale->execute()) {
        throw new Exception("Error updating sale record: " . $upd_sale->error);
    }

    // 7. INSERT New Sale Items
    $item_query = "INSERT INTO sale_items (sale_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_query);
    foreach ($verified_items as $itm) {
        $item_stmt->bind_param("iiidd", $sale_id, $itm['id'], $itm['quantity'], $itm['price'], $itm['total']);
        $item_stmt->execute();
    }

    // 8. APPLY New Customer Balance
    if ($due_amount != 0 && $customer_id) {
        $apply_bal = $conn->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?");
        $apply_bal->bind_param("di", $due_amount, $customer_id);
        $apply_bal->execute();
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
