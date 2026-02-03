<?php
include 'includes/header.php';

// Use a safe query (don't reference columns that may not exist)
$result = $conn->query("SELECT * FROM customers");

$total_dues = 0;
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h3 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; color: var(--danger); margin-bottom: 0.5rem;">Receivable Balances</h3>
            <p style="color: var(--text-muted); font-weight: 600;">Monitor and manage outstanding customer dues.</p>
        </div>
        
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <i class="fa-solid fa-print"></i> Export Print Data
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>Relationship</th>
                    <th style="text-align: right;">Pending Dues</th>
                    <th class="no-print" style="text-align: right;">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $balance = $row['balance'] ?? 0; // safe fallback
                        $total_dues += $balance;
                    ?>
                    <tr>
                        <td style="font-weight: 700;">
                            <?= htmlspecialchars($row['name'] ?? '') ?>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><i class="fa-solid fa-phone" style="width: 20px; color: var(--primary);"></i> <?= htmlspecialchars($row['phone']) ?></div>
                            <?php if($row['email']): ?>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;"><i class="fa-solid fa-envelope" style="width: 20px;"></i><?= htmlspecialchars($row['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $row['type'] == 'contractor' ? 'badge-warning' : 'badge-success' ?>">
                                <?= ucfirst($row['type']) ?>
                            </span>
                        </td>
                        <td style="text-align: right; color: var(--danger); font-weight: 800; font-size: 1.1rem;">
                            <?= formatPrice($balance) ?>
                        </td>
                        <td class="no-print" style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="receive_payment.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm" title="Receive Payment">
                                    <i class="fa-solid fa-money-bill-wave"></i> Recv
                                </a>
                                <a href="due_receipt.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Generate Receipt">
                                    <i class="fa-solid fa-file-invoice"></i> PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <tr style="background: var(--danger-light); font-weight: 800;">
                        <td colspan="3" style="text-align: right; padding: 2rem;">Total Cumulative Receivables:</td>
                        <td style="text-align: right; color: var(--danger); font-size: 1.5rem; padding: 2rem 1.5rem;">
                            <?= formatPrice($total_dues) ?>
                        </td>
                        <td class="no-print"></td>
                    </tr>
                    
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 5rem; color: var(--success);">
                            <i class="fa-solid fa-circle-check fa-4x" style="margin-bottom: 1.5rem; opacity: 0.2;"></i><br>
                            <span style="font-size: 1.25rem; font-weight: 700;">Zero Outstanding Balance!</span><br>
                            <span style="color: var(--text-muted);">All customers have cleared their accounts.</span>
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
        table th { background: #f1f5f9 !important; border-bottom: 2px solid #000 !important; }
        td { border-bottom: 1px solid #eee !important; }
    }
</style>


<?php include 'includes/footer.php'; ?>
