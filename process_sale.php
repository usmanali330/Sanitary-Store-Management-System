<?php
// Clear any existing output buffers to ensure clean JSON
while (ob_get_level()) {
    ob_end_clean();
}

require_once 'includes/config.php';
requireAuth();

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received']);
        exit;
    }

    $customer_id = isset($data['customer_id']) && $data['customer_id'] !== '' ? intval($data['customer_id']) : null;
    $items = $data['items'] ?? [];
    $discount = floatval($data['discount'] ?? 0);
    $tax_rate = floatval($data['tax_rate'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? null;

    // Verify if user_id exists in database to avoid foreign key constraint error
    if ($user_id) {
        $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
        if ($check_user) {
            $check_user->bind_param("i", $user_id);
            $check_user->execute();
            if ($check_user->get_result()->num_rows === 0) {
                $user_id = null; // Set to null if user doesn't exist
            }
            $check_user->close();
        }
    }

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }

    // Check if required columns exist in sales table
    $columns_check = $conn->query("SHOW COLUMNS FROM sales LIKE 'paid_amount'");
    if ($columns_check) {
        if ($columns_check->num_rows == 0) {
            // Add missing columns
            $conn->query("ALTER TABLE sales ADD COLUMN paid_amount DECIMAL(10, 2) DEFAULT 0 AFTER total_amount");
            $conn->query("ALTER TABLE sales ADD COLUMN due_amount DECIMAL(10, 2) DEFAULT 0 AFTER paid_amount");
        }
    } else {
        error_log("Failed to check sales columns: " . $conn->error);
    }
    
    // Check balance column in customers
    $balance_check = $conn->query("SHOW COLUMNS FROM customers LIKE 'balance'");
    if ($balance_check) {
        if ($balance_check->num_rows == 0) {
            $conn->query("ALTER TABLE customers ADD COLUMN balance DECIMAL(10, 2) DEFAULT 0");
        }
    } else {
        error_log("Failed to check customers columns: " . $conn->error);
    }

    // Ensure users table is compatible or that we can at least insert a sale without a valid user if needed
    // The foreign key for user_id is ON DELETE SET NULL, so null is fine.

    $conn->begin_transaction();

    // 1. Calculate Totals (Verify server-side prices)
    $subtotal = 0;
    $verified_items = [];

    foreach ($items as $item) {
        $pid = intval($item['id']);
        $qty = intval($item['quantity']);

        // Fetch current product state
        $stmt = $conn->prepare("SELECT price, quantity FROM products WHERE id = ? FOR UPDATE");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception("Product ID $pid not found.");
        }

        if ($product['quantity'] < $qty) {
            throw new Exception("Insufficient stock for Product ID $pid. Available: " . $product['quantity']);
        }

        $price = floatval($product['price']);
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
        if (!$update_stmt) {
            throw new Exception("Update prepare error: " . $conn->error);
        }
        $update_stmt->bind_param("ii", $new_qty, $pid);
        $update_stmt->execute();
        $update_stmt->close();
    }

    // Calculate Final Amounts
    $tax_amount = ($tax_rate > 0) ? ($subtotal * $tax_rate) / 100 : 0;
    
    // Total Amount Calculation
    $total_bill = $subtotal + $tax_amount;
    $final_total = $total_bill - $discount;

    // Payment Handling
    $paid_amount = isset($data['paid_amount']) ? floatval($data['paid_amount']) : $final_total;
    $due_amount = $final_total - $paid_amount;
    
    // 3. Create Sale Record
    $sale_stmt = $conn->prepare("INSERT INTO sales (customer_id, user_id, subtotal, tax, discount, total_amount, paid_amount, due_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$sale_stmt) {
        throw new Exception("Sale insert prepare error: " . $conn->error);
    }
    $sale_stmt->bind_param("iidddddd", $customer_id, $user_id, $subtotal, $tax_amount, $discount, $final_total, $paid_amount, $due_amount);
    if (!$sale_stmt->execute()) {
        throw new Exception("Error creating sale record: " . $sale_stmt->error);
    }
    $sale_id = $conn->insert_id;
    $sale_stmt->close();

    // 4. Create Sale Items Records
    $item_stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
    if (!$item_stmt) {
        throw new Exception("Item insert prepare error: " . $conn->error);
    }
    
    foreach ($verified_items as $itm) {
        $p_id = $itm['id'];
        $qty = $itm['quantity'];
        $price = $itm['price'];
        $total_line = $itm['total'];
        
        $item_stmt->bind_param("iiidd", $sale_id, $p_id, $qty, $price, $total_line);
        if (!$item_stmt->execute()) {
            throw new Exception("Error adding item: " . $item_stmt->error);
        }
    }
    $item_stmt->close();
    
    // 5. Update Customer Balance if Due Amount != 0 and Customer is known
    if ($due_amount != 0 && $customer_id) {
        $bal_stmt = $conn->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?");
        if ($bal_stmt) {
            $bal_stmt->bind_param("di", $due_amount, $customer_id);
            $bal_stmt->execute();
            $bal_stmt->close();
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'sale_id' => $sale_id]);

} catch (Exception $e) {
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Error $e) {
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    echo json_encode(['status' => 'error', 'message' => 'PHP Error: ' . $e->getMessage()]);
}
