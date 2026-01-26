<?php
include 'includes/config.php';
requireAuth();

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit();
}

$sale_id = $_GET['id'];

// Fetch Sale Info
$sale_sql = "SELECT s.*, c.name as customer_name, c.address as customer_address, c.phone as customer_phone, u.username as staff 
             FROM sales s 
             LEFT JOIN customers c ON s.customer_id = c.id 
             LEFT JOIN users u ON s.user_id = u.id 
             WHERE s.id = ?";
$stmt = $conn->prepare($sale_sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

if (!$sale) {
    die("Invoice not found.");
}

// Fetch Sale Items
$items_sql = "SELECT si.*, p.name as product_name 
              FROM sale_items si 
              LEFT JOIN products p ON si.product_id = p.id 
              WHERE si.sale_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$items_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $sale['id'] ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 20px; background: #555; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .header { display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .logo { font-size: 24px; font-weight: bold; color: #333; }
        .company-info { text-align: right; color: #555; }
        .invoice-details { margin-bottom: 2rem; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f9f9f9; font-weight: 600; }
        .text-right { text-align: right; }
        .totals { float: right; width: 300px; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .grand-total { font-size: 1.2rem; font-weight: bold; border-top: 2px solid #333; }
        .action-buttons { margin-top: 2rem; text-align: center; }
        .btn { padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 4px; margin: 0 5px; cursor: pointer; border: none;}
        .btn-print { background: #2563eb; }
        .btn-home { background: #475569; }

        @media print {
            body { background: white; padding: 0; }
            .invoice-box { box-shadow: none; max-width: 100%; }
            .action-buttons { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <div class="logo">
            HAJI BABA <br>
            <span style="font-size: 14px; font-weight: normal; color: #777;">Sanitary Store</span>
        </div>
        <div class="company-info">
            Hardware & Sanitary Store<br>
            wardaga raod near Juma khan madrassa sardheri<br>
            Phone: +92 3129098487
        </div>
    </div>

    <div class="invoice-details">
        <div>
            <strong>Bill To:</strong><br>
            <?= htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer') ?><br>
            <?php if($sale['customer_address']) echo htmlspecialchars($sale['customer_address']); ?><br>
            <?php if($sale['customer_phone']) echo htmlspecialchars($sale['customer_phone']); ?>
        </div>
        <div class="text-right">
            <strong>Invoice #:</strong> <?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?><br>
            <strong>Date:</strong> <?= date('d M Y', strtotime($sale['created_at'])) ?><br>
            <strong>Staff:</strong> <?= htmlspecialchars($sale['staff']) ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Price</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item = $items_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td class="text-right"><?= number_format($item['price'], 2) ?></td>
                <td class="text-right"><?= $item['quantity'] ?></td>
                <td class="text-right"><?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span><?= number_format($sale['subtotal'], 2) ?></span>
        </div>
        <div class="totals-row">
            <span>Discount:</span>
            <span>-<?= number_format($sale['discount'], 2) ?></span>
        </div>
        <div class="totals-row grand-total">
            <span>Total:</span>
            <span>Rs. <?= number_format($sale['total_amount'], 2) ?></span>
        </div>
    </div>
    <div style="clear: both;"></div>

    <div style="margin-top: 3rem; text-align: center; color: #777; font-size: 0.9rem;">
        <p>Thank you for your business!</p>
        <p>Goods once sold will not be returned or exchanged without receipt.</p>
    </div>

    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-print">Print Invoice</button>
        <a href="pos.php" class="btn btn-home">Back to POS</a>
    </div>
</div>

</body>
</html>
