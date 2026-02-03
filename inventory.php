<?php
include 'includes/header.php';

// Handle Stock Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'] ?? 'Restock';

    if ($quantity > 0) {
        $conn->begin_transaction();
        try {
            // Update Product
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();

            // Log
            $log_stmt = $conn->prepare("INSERT INTO stock_logs (product_id, quantity, type, reason) VALUES (?, ?, 'in', ?)");
            $log_stmt->bind_param("iis", $product_id, $quantity, $reason);
            $log_stmt->execute();

            $conn->commit();
            echo "<script>alert('Stock added successfully'); window.location.href='inventory.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error adding stock');</script>";
        }
    }
}

// Low stock only filter
$low_stock = isset($_GET['low_stock']);
$where = "WHERE 1=1";
if ($low_stock) {
    $where .= " AND p.quantity < 10";
}

$products = $conn->query("SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.quantity ASC");

// Get recent logs
$logs = $conn->query("SELECT l.*, p.name as product_name FROM stock_logs l JOIN products p ON l.product_id = p.id ORDER BY l.created_at DESC LIMIT 10");

?>

<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.25rem;">Inventory Status</h3>
        <div>
            <?php if ($low_stock): ?>
                <a href="inventory.php" class="btn btn-secondary">Show All</a>
            <?php else: ?>
                <a href="inventory.php?low_stock=1" class="btn btn-danger"><i class="fa-solid fa-triangle-exclamation"></i> Show Low Stock Only</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="table-container" style="box-shadow: none; padding: 0; max-height: 400px; overflow-y: auto;">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td style="font-weight: bold; font-size: 1.1em;"><?= $row['quantity'] ?></td>
                    <td>
                        <?php if ($row['quantity'] == 0): ?>
                            <span class="badge" style="background: #fee2e2; color: #ef4444;">Out of Stock</span>
                        <?php elseif ($row['quantity'] < 10): ?>
                            <span class="badge badge-warning">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-success">Good</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="openStockModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus"></i> Add Stock
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Recent Stock History</h3>
    <div class="table-container" style="box-shadow: none; padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs && $logs->num_rows > 0): ?>
                    <?php while($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                        <td><?= htmlspecialchars($log['product_name']) ?></td>
                        <td>
                            <span class="badge" style="background: <?= $log['type'] == 'in' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $log['type'] == 'in' ? '#166534' : '#ef4444' ?>">
                                <?= strtoupper($log['type']) ?>
                            </span>
                        </td>
                        <td><?= $log['quantity'] ?></td>
                        <td><?= htmlspecialchars($log['reason']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center;">No stock history yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Stock Modal -->
<div id="stockModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 1rem;">Add Stock</h3>
        <p id="modalProductName" style="margin-bottom: 1rem; color: var(--text-light);"></p>
        
        <form method="POST">
            <input type="hidden" name="add_stock" value="1">
            <input type="hidden" name="product_id" id="modalProductId">
            
            <div class="form-group">
                <label>Quantity to Add</label>
                <input type="number" name="quantity" class="form-control" required min="1">
            </div>
            
            <div class="form-group">
                <label>Reason (Optional)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Supplier Purchase">
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStockModal(id, name) {
    document.getElementById('stockModal').style.display = 'flex';
    document.getElementById('modalProductId').value = id;
    document.getElementById('modalProductName').innerText = 'Product: ' + name;
}

function closeStockModal() {
    document.getElementById('stockModal').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
