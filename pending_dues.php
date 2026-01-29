<?php
include 'includes/header.php';

// Fetch customers with dues
$sql = "SELECT * FROM customers WHERE balance > 0 ORDER BY balance DESC";
$result = $conn->query($sql);

$total_dues = 0;
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--danger-color);">Pending Dues List</h3>
            <p style="color: var(--text-light);">Customers with outstanding pending balances.</p>
        </div>
        
        <button onclick="window.print()" class="btn btn-primary no-print">
            <i class="fa-solid fa-print"></i> Print List
        </button>
    </div>

    <div class="table-container" style="box-shadow: none; padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th>Type</th>
                    <th style="text-align: right;">Pending Balance</th>
                    <th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $total_dues += $row['balance'];
                    ?>
                    <tr>
                        <td style="font-weight: 500; font-size: 1.05rem;">
                            <?= htmlspecialchars($row['name']) ?>
                        </td>
                        <td>
                            <div><i class="fa-solid fa-phone" style="width: 20px; color: var(--text-light);"></i> <?= htmlspecialchars($row['phone']) ?></div>
                            <?php if($row['email']): ?>
                            <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.25rem;"><i class="fa-solid fa-envelope" style="width: 20px;"></i><?= htmlspecialchars($row['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="max-width: 250px;">
                            <?= htmlspecialchars($row['address']) ?: '<span style="color: #ccc;">N/A</span>' ?>
                        </td>
                        <td>
                            <span class="badge <?= $row['type'] == 'contractor' ? 'badge-warning' : 'badge-success' ?>">
                                <?= ucfirst($row['type']) ?>
                            </span>
                        </td>
                        <td style="text-align: right; color: var(--danger-color); font-weight: 700; font-size: 1.1rem;">
                            <?= formatPrice($row['balance']) ?>
                        </td>
                        <td class="no-print">
                            <a href="receive_payment.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm" title="Receive Payment">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </a>
                            <a href="due_receipt.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Generate Receipt">
                                <i class="fa-solid fa-file-invoice"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <tr style="background: #fff1f2; font-weight: bold;">
                        <td colspan="4" style="text-align: right; padding: 1.5rem;">Total Outstanding Dues:</td>
                        <td style="text-align: right; color: var(--danger-color); font-size: 1.25rem; padding: 1.5rem 1rem;">
                            <?= formatPrice($total_dues) ?>
                        </td>
                        <td></td>
                    </tr>
                    
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--success-color);">
                            <i class="fa-solid fa-check-circle fa-3x" style="margin-bottom: 1rem;"></i><br>
                            No pending dues found! All clear.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        .sidebar, .top-bar, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .app-container {
            display: block !important;
        }
        body {
            background: white !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
