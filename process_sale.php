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
    $tax_amount = ($tax_rate > 0) ? ($subtotal * $tax_rate) / 100 : 0;
    
    // Total Amount Calculation
    $total_bill = $subtotal + $tax_amount;
    $final_total = $total_bill - $discount;

    // Payment Handling
    $paid_amount = isset($data['paid_amount']) ? floatval($data['paid_amount']) : $final_total;
    
    // Ensure paid amount is not greater than total for simplicity (unless handling advance)
    // For now, let's assume it can be any amount.
    
    $due_amount = $final_total - $paid_amount;
    
    // 3. Create Sale Record
    $sale_stmt = $conn->prepare("INSERT INTO sales (customer_id, user_id, subtotal, tax, discount, total_amount, paid_amount, due_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $sale_stmt->bind_param("iidddddd", $customer_id, $user_id, $subtotal, $tax_amount, $discount, $final_total, $paid_amount, $due_amount);
    if (!$sale_stmt->execute()) {
        throw new Exception("Error creating sale record: " . $sale_stmt->error);
    }
    $sale_id = $conn->insert_id;

    // 4. Create Sale Items Records
    $item_query = "INSERT INTO sale_items (sale_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_query);
    if (!$item_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    foreach ($verified_items as $itm) {
        // Correct variable names from loop above
        $p_id = $itm['id'];
        $qty = $itm['quantity'];
        $price = $itm['price'];
        $total_line = $itm['total'];
        
        $item_stmt->bind_param("iiidd", $sale_id, $p_id, $qty, $price, $total_line);
        if (!$item_stmt->execute()) {
            throw new Exception("Error adding item: " . $item_stmt->error);
        }
    }
    
    // 5. Update Customer Balance if Due Amount > 0 and Customer is known
    if ($due_amount != 0 && $customer_id) {
        $bal_stmt = $conn->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?");
        $bal_stmt->bind_param("di", $due_amount, $customer_id);
        $bal_stmt->execute();
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'sale_id' => $sale_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
