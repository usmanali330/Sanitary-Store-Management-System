<?php
require_once 'includes/config.php';
requireAuth();

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = $_GET['id'];

// Fetch Customer
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    die("Customer not found");
}

// Fetch recent unpaid/partial sales for context (optional, but good for receipt)
// Since we only track total balance, we'll just show the balance statement.
// But we can show last 5 transactions for reference.
$sales_sql = "SELECT * FROM sales WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5";
$sales_stmt = $conn->prepare($sales_sql);
$sales_stmt->bind_param("i", $id);
$sales_stmt->execute();
$sales = $sales_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Due Payment Reminder - <?= htmlspecialchars($customer['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #525659; font-family: 'Times New Roman', Times, serif; }
        .invoice-page {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            padding: 3rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            min-height: 1000px;
            position: relative;
        }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 2rem; margin-bottom: 2rem; }
        .store-name { font-size: 2.5rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .subtitle { font-size: 1.2rem; margin-top: 0.5rem; color: #555; }
        .contact-info { margin-top: 0.5rem; font-size: 0.9rem; }
        
        .bill-title { 
            text-align: center; margin: 2rem 0; 
            font-size: 1.5rem; font-weight: bold; text-decoration: underline; 
            text-transform: uppercase;
        }

        .customer-info {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .amount-box {
            border: 2px solid #000;
            padding: 1.5rem;
            text-align: center;
            margin: 2rem 0;
            background: #fff;
        }

        .amount-large {
            font-size: 3rem;
            font-weight: bold;
            color: #ef4444;
            margin: 1rem 0;
        }

        .footer {
            margin-top: 4rem;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 2rem;
            font-size: 0.9rem;
            color: #666;
        }

        .btn-print {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #2563eb;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            font-family: sans-serif;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s;
        }
        .btn-print:hover { transform: translateY(-2px); }

        @media print {
            body { background: white; }
            .invoice-page { margin: 0; box-shadow: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="invoice-page">
        <div class="header">
            <div class="store-name">Sanitary Store System</div>
            <div class="subtitle">Premium Sanitary Wares & Pipes</div>
            <div class="contact-info">
                Block A, Main Market, City Name | +92 300 1234567
            </div>
        </div>

        <div class="bill-title">Payment Pending Reminder</div>

        <div class="customer-info">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 15%; font-weight: bold;">To:</td>
                    <td><?= htmlspecialchars($customer['name']) ?></td>
                    <td style="width: 15%; font-weight: bold;">Date:</td>
                    <td style="text-align: right;"><?= date('d M, Y') ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Contact:</td>
                    <td><?= htmlspecialchars($customer['phone']) ?></td>
                    <td style="font-weight: bold;">Type:</td>
                    <td style="text-align: right;"><?= ucfirst($customer['type']) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Address:</td>
                    <td colspan="3"><?= htmlspecialchars($customer['address']) ?></td>
                </tr>
            </table>
        </div>

        <div style="font-family: sans-serif; line-height: 1.6; margin-bottom: 2rem;">
            <p>Dear <strong><?= htmlspecialchars($customer['name']) ?></strong>,</p>
            <p>This is a gentle reminder regarding the outstanding balance on your account. According to our records, the total due amount is pending payment. We would appreciate it if you could clear this balance at your earliest convenience.</p>
        </div>

        <div class="amount-box">
            <div style="text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">Total Pending Balance</div>
            <div class="amount-large"><?= formatPrice($customer['balance']) ?></div>
            <div style="font-style: italic; color: #666;">(Please pay this amount to clear your account)</div>
        </div>

        <?php if ($sales->num_rows > 0): ?>
        <div style="margin-top: 3rem;">
            <h4 style="border-bottom: 1px solid #ccc; padding-bottom: 0.5rem; margin-bottom: 1rem;">Recent Transactions Reference</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f3f4f6;">
                        <th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Sale ID</th>
                        <th style="padding: 0.5rem; text-align: right; border: 1px solid #ddd;">Bill Total</th>
                        <th style="padding: 0.5rem; text-align: right; border: 1px solid #ddd;">Paid</th>
                        <th style="padding: 0.5rem; text-align: right; border: 1px solid #ddd;">Due</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = $sales->fetch_assoc()): ?>
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">#<?= str_pad($s['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd; text-align: right;"><?= number_format($s['total_amount'], 2) ?></td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd; text-align: right;"><?= number_format($s['paid_amount'], 2) ?></td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd; text-align: right; color: #ef4444;"><?= number_format($s['due_amount'], 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p><strong>Sanitary Store System</strong></p>
        </div>
    </div>

    <a href="javascript:window.print()" class="btn-print">
        <i class="fa-solid fa-print"></i> Print Receipt
    </a>

</body>
</html>
