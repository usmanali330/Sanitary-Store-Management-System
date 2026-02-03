<?php
include 'includes/header.php';

$customer_id = $_GET['id'] ?? null;
$customer = null;

// Fetch Customer if ID provided
if ($customer_id) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
}

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $c_id = $_POST['customer_id'];
    $amount = $_POST['amount'];
    $note = $_POST['note'];

    if ($amount > 0) {
        $conn->begin_transaction();
        try {
            // 1. Record Payment
            $stmt = $conn->prepare("INSERT INTO payments (customer_id, amount, note) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $c_id, $amount, $note);
            $stmt->execute();

            // 2. Reduce Customer Balance
            $update = $conn->prepare("UPDATE customers SET balance = balance - ? WHERE id = ?");
            $update->bind_param("di", $amount, $c_id);
            $update->execute();

            $conn->commit();
            echo "<script>alert('Payment received successfully!'); window.location.href='pending_dues.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error processing payment');</script>";
        }
    } else {
        echo "<script>alert('Amount must be greater than 0');</script>";
    }
}

// Fetch all customers for dropdown
$customers = $conn->query("SELECT * FROM customers WHERE balance > 0 ORDER BY name ASC");
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3 style="margin-bottom: 1.5rem;">Receive Payment</h3>
    
    <form method="POST">
        <div class="form-group">
            <label>Customer</label>
            <select name="customer_id" class="form-control" required onchange="window.location.href='receive_payment.php?id='+this.value">
                <option value="">Select Customer</option>
                <?php while($c = $customers->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= ($customer_id == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?> (Due: <?= formatPrice($c['balance']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <?php if ($customer): ?>
        <div style="background: #eff6ff; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bfdbfe;">
            <div style="font-weight: 600; color: #1e40af;">Current Outstanding Balance</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #2563eb;"><?= formatPrice($customer['balance']) ?></div>
        </div>

        <div class="form-group">
            <label>Payment Amount (Rs)</label>
            <input type="number" step="0.01" name="amount" class="form-control" required max="<?= $customer['balance'] ?>" value="<?= $customer['balance'] ?>">
            <small style="color: var(--text-light);">Enter the amount received from customer.</small>
        </div>

        <div class="form-group">
            <label>Note / Reference</label>
            <input type="text" name="note" class="form-control" placeholder="e.g. Cash, Bank Transfer Ref #123">
        </div>

        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">
            <i class="fa-solid fa-check"></i> Confirm Payment
        </button>
        <?php endif; ?>
        
        <div style="margin-top: 1rem; text-align: center;">
            <a href="pending_dues.php" style="color: var(--text-light); text-decoration: none;">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
