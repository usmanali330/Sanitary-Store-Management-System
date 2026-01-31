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

// Prepare items for JS
$items_list = [];
while ($item = $items_result->fetch_assoc()) {
    $items_list[] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?> - Haji Baba</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --success: #10b981;
            --whatsapp: #25D366;
            --danger: #ef4444;
            --bg-body: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --white: #ffffff;
            --card-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-body); 
            color: var(--text-main);
            line-height: 1.6;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* --- Subtle Mesh Background --- */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(14, 165, 233, 0.05) 0px, transparent 50%);
        }

        .container {
            max-width: 950px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Actions Toolbar --- */
        .actions-bar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            padding: 16px 24px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .btn:active { transform: scale(0.98); }

        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        
        .btn-whatsapp { background: var(--whatsapp); color: white; }
        .btn-whatsapp:hover { background: #1fa851; box-shadow: 0 10px 15px -3px rgba(37, 211, 102, 0.3); }
        
        .btn-download { background: var(--text-main); color: white; }
        .btn-download:hover { background: #000; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2); }

        .btn-outline { 
            background: var(--white); 
            border: 1.5px solid #e2e8f0; 
            color: var(--text-main); 
        }
        .btn-outline:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }

        /* --- Invoice Sheet --- */
        .invoice-paper {
            background: var(--white);
            padding: 60px;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            position: relative;
            min-height: 1050px;
            border: 1px solid #eef2f6;
        }

        /* Branding Strip */
        .invoice-paper::after {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(to right, var(--primary), #0ea5e9, var(--primary));
            border-radius: 24px 24px 0 0;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
        }

        .brand-logo h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -1.5px;
            line-height: 1;
            margin-bottom: 5px;
        }
        .brand-logo span {
            font-size: 13px;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .company-meta {
            text-align: right;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .company-meta strong {
            color: var(--text-main);
            font-size: 1rem;
            display: block;
            margin-bottom: 6px;
        }

        /* Information Grid */
        .invoice-info-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
            margin-bottom: 50px;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 35px 0;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .info-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ecfdf5;
            color: #059669;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 800;
            margin-top: 10px;
            text-transform: uppercase;
        }

        /* Table Design */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .invoice-table th {
            text-align: left;
            padding: 16px;
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .invoice-table th:first-child { border-radius: 10px 0 0 10px; }
        .invoice-table th:last-child { border-radius: 0 10px 10px 0; }

        .invoice-table td {
            padding: 20px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }

        .item-name { font-weight: 700; color: var(--text-main); }
        .text-right { text-align: right !important; }

        /* Totals Area */
        .footer-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .totals-box { width: 340px; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1rem;
            color: var(--text-muted);
        }

        .summary-row.grand-total {
            margin-top: 15px;
            padding: 25px 20px;
            background: #f8fafc;
            border-radius: 16px;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
        }

        .invoice-footer {
            margin-top: 80px;
            padding-top: 40px;
            border-top: 2px dashed #f1f5f9;
            text-align: center;
            color: var(--text-muted);
        }

        .invoice-footer p { font-size: 0.85rem; margin-bottom: 8px; }

        /* --- Print & Responsive --- */
        @media print {
            body { background: white; padding: 0; }
            .container { max-width: 100%; }
            .actions-bar { display: none; }
            .invoice-paper { box-shadow: none; border: none; padding: 20px; border-radius: 0; }
            .invoice-paper::after { display: none; }
        }

        @media (max-width: 768px) {
            .invoice-paper { padding: 30px; }
            .invoice-header { flex-direction: column; gap: 20px; }
            .company-meta { text-align: left; }
            .invoice-info-grid { grid-template-columns: 1fr; }
            .text-right { text-align: left !important; }
            .footer-summary { justify-content: flex-start; }
            .totals-box { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="actions-bar">
        <div style="display: flex; gap: 10px;">
            <a href="pos.php" class="btn btn-outline">
                <i class="fa-solid fa-cash-register"></i> POS
            </a>
            <a href="sales.php" class="btn btn-outline">
                <i class="fa-solid fa-receipt"></i> Sales History
            </a>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-outline">
                <i class="fa-solid fa-print"></i> Print
            </button>
            <button onclick="downloadAsPDF()" id="downloadBtn" class="btn btn-download">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
            <button onclick="shareOnWhatsApp()" id="whatsappBtn" class="btn btn-whatsapp">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </button>
        </div>
    </div>

    <div id="invoiceContent" class="invoice-paper">
        <div class="invoice-header">
            <div class="brand-logo">
                <h1>HAJI BABA</h1>
                <span>Sanitary & Hardware</span>
            </div>
            <div class="company-meta">
                <strong>Main Branch - Charsadda</strong>
                Wardaga Road, Near Juma Khan Madrassa<br>
                Sardheri, KP, Pakistan<br>
                <i class="fa-solid fa-phone" style="font-size: 11px; color: var(--primary);"></i> +92 312 9098487
            </div>
        </div>

        <div class="invoice-info-grid">
            <div class="info-block">
                <div class="info-label">Customer Details</div>
                <div class="info-value"><?= htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer') ?></div>
                <p style="font-size: 0.9rem; margin-top: 8px; color: var(--text-muted);">
                    <?php if($sale['customer_phone']) echo '<i class="fa-solid fa-phone" style="width: 20px;"></i>' . htmlspecialchars($sale['customer_phone']) . '<br>'; ?>
                    <?php if($sale['customer_address']) echo '<i class="fa-solid fa-location-dot" style="width: 20px;"></i>' . htmlspecialchars($sale['customer_address']); ?>
                </p>
                <div class="badge-status">
                    <i class="fa-solid fa-check-circle"></i> Fully Paid
                </div>
            </div>
            <div class="info-block text-right">
                <div class="info-label">Invoice Reference</div>
                <div class="info-value">#<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?></div>
                <div style="margin-top: 12px; font-size: 0.9rem; color: var(--text-muted);">
                    <strong>Issued:</strong> <?= date('d M Y, h:i A', strtotime($sale['created_at'])) ?><br>
                    <strong>Clerk:</strong> <?= htmlspecialchars($sale['staff'] ?? '') ?>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th width="50%">Description</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $items_result->data_seek(0);
                while($item = $items_result->fetch_assoc()): 
                ?>
                <tr>
                    <td>
                        <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                    </td>
                    <td class="text-right">Rs. <?= number_format($item['price'], 0) ?></td>
                    <td class="text-right"><?= $item['quantity'] ?></td>
                    <td class="text-right"><strong>Rs. <?= number_format($item['total'], 0) ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="footer-summary">
            <div class="totals-box">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rs. <?= number_format($sale['subtotal'], 0) ?></span>
                </div>
                <div class="summary-row" style="color: var(--danger);">
                    <span>Discount Applied</span>
                    <span>- Rs. <?= number_format($sale['discount'], 0) ?></span>
                </div>
                <div class="summary-row grand-total">
                    <span>Total Amount</span>
                    <span>Rs. <?= number_format($sale['total_amount'], 0) ?></span>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p style="font-weight: 700; color: var(--text-main); font-size: 1rem;">Thank you for your business!</p>
            <p>Exchange policy: Returns accepted within 7 days with original receipt.</p>
            <p style="opacity: 0.5; font-size: 10px; margin-top: 30px;">Electronic Document - No physical signature required</p>
        </div>
    </div>
</div>

<script>
const INVOICE_ID = '<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?>';
const CUSTOMER_NAME = '<?= addslashes($sale['customer_name'] ?: 'Walk-in Customer') ?>';
const TOTAL_AMOUNT = '<?= number_format($sale['total_amount'], 0) ?>';

// Function to generate and download PDF
async function downloadAsPDF() {
    const element = document.getElementById('invoiceContent');
    const btn = document.getElementById('downloadBtn');
    const originalContent = btn.innerHTML;
    
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    btn.style.opacity = '0.7';
    
    const opt = {
        margin: [0.3, 0.3],
        filename: `HB_Invoice_${INVOICE_ID}.pdf`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 3, useCORS: true, letterRendering: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    try {
        await html2pdf().set(opt).from(element).save();
    } catch (error) {
        console.error('PDF Error:', error);
    } finally {
        btn.innerHTML = originalContent;
        btn.style.opacity = '1';
    }
}

// Function to share on WhatsApp
async function shareOnWhatsApp() {
    const btn = document.getElementById('whatsappBtn');
    const originalContent = btn.innerHTML;
    
    let message = `*HAJI BABA SANITARY & HARDWARE*\n`;
    message += `*Invoice:* #${INVOICE_ID}\n`;
    message += `*Customer:* ${CUSTOMER_NAME}\n`;
    message += `--------------------------\n`;
    <?php 
    $items_result->data_seek(0);
    while($item = $items_result->fetch_assoc()) {
        echo "message += `• " . addslashes($item['product_name']) . " x " . $item['quantity'] . " = Rs. " . number_format($item['total'], 0) . "\\n`;\n";
    }
    ?>
    message += `--------------------------\n`;
    message += `*Grand Total: Rs. ${TOTAL_AMOUNT}*\n`;
    message += `Thank you for shopping with us!`;

    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;

    // Mobile Sharing
    if (navigator.share && /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        try {
            const element = document.getElementById('invoiceContent');
            const pdfBlob = await html2pdf().set({
                margin: 0.5,
                filename: `Invoice_${INVOICE_ID}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            }).from(element).outputPdf('blob');

            const file = new File([pdfBlob], `Invoice_${INVOICE_ID}.pdf`, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({
                    files: [file],
                    title: `Invoice #${INVOICE_ID}`,
                    text: `Haji Baba Invoice`
                });
                btn.innerHTML = originalContent;
                return;
            }
        } catch (err) {
            console.log('Mobile share fallback');
        }
    }

    btn.innerHTML = originalContent;
    window.open(whatsappUrl, '_blank');
}
</script>

</body>
</html>