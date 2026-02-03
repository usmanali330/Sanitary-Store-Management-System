<?php
include 'includes/header.php';

// Prepare Filters
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$customer_id = $_GET['customer_id'] ?? '';

// Build Query
$where = "WHERE DATE(s.created_at) BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";

if ($customer_id) {
    $where .= " AND s.customer_id = ?";
    $params[] = $customer_id;
    $types .= "i";
}

$sql = "SELECT s.*, c.name as customer_name, u.username as staff 
        FROM sales s 
        LEFT JOIN customers c ON s.customer_id = c.id 
        LEFT JOIN users u ON s.user_id = u.id 
        $where 
        ORDER BY s.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$sales = $stmt->get_result();

// Get Customers for Filter
$customers = $conn->query("SELECT * FROM customers ORDER BY name");

// Calculate Total for Filtered Range (Optional but useful)
$total_sql = "SELECT SUM(total_amount) as total FROM sales s " . $where;
// Re-bind params excluding limit/offset
$total_stmt = $conn->prepare($total_sql);
// We need to slice params array to match the count for this query
$total_params = array_slice($params, 0, count($params) - 2); 
$total_types = substr($types, 0, strlen($types) - 2);
$total_stmt->bind_param($total_types, ...$total_params);
$total_stmt->execute();
$range_total = $total_stmt->get_result()->fetch_assoc()['total'] ?? 0;
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <h3 style="font-size: 1.25rem;">Sales History</h3>
    </div>

    <!-- Filters -->
    <form method="GET" style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; margin-bottom: 0.75rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.85rem;">Date From</label>
            <input type="date" name="date_from" value="<?= $date_from ?>" class="form-control">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.85rem;">Date To</label>
            <input type="date" name="date_to" value="<?= $date_to ?>" class="form-control">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.85rem;">Customer</label>
            <select name="customer_id" class="form-control">
                <option value="">All Customers</option>
                <?php while($c = $customers->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= $customer_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>
    
    <div style="margin-bottom: 0.75rem; font-weight: 600; color: var(--primary-color);">
        Total Revenue (Selected Period): <?= formatPrice($range_total) ?>
    </div>

    <div class="table-container" style="box-shadow: none; padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Staff</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($sales->num_rows > 0): ?>
                    <?php while($row = $sales->fetch_assoc()): ?>
                    <tr>
                        <td><?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                        <td><?= htmlspecialchars($row['customer_name'] ?: 'Walk-in') ?></td>
                        <td><?= htmlspecialchars($row['staff'] ?? '') ?></td>
                        <td><?= number_format($row['subtotal'], 2) ?></td>
                        <td style="color: var(--danger-color);"><?= $row['discount'] > 0 ? '-' . number_format($row['discount'], 2) : '0.00' ?></td>
                        <td style="font-weight: 600;"><?= formatPrice($row['total_amount']) ?></td>
                        <td style="color: #10b981; font-weight: 600;"><?= formatPrice($row['paid_amount']) ?></td>
                        <td style="color: <?= $row['due_amount'] > 0 ? 'var(--danger-color)' : '#10b981' ?>; font-weight: 600;"><?= formatPrice($row['due_amount']) ?></td>
                        <td>
                            <a href="invoice.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm" title="View Invoice">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="edit_sale.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit Sale">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2rem;">No sales found for this period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
