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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
        .btn { padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 4px; margin: 0 5px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px;}
        .btn-print { background: #2563eb; }
        .btn-whatsapp { background: #25D366; }
        .btn-whatsapp:hover { background: #20BA5A; }
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
        <button onclick="window.print()" class="btn btn-print">
            <i class="fa-solid fa-print"></i> Print Invoice
        </button>
        <a href="javascript:void(0)" onclick="shareToWhatsApp(event)" class="btn btn-whatsapp">
            <i class="fa-brands fa-whatsapp"></i> Share to WhatsApp
        </a>
        <a href="edit_sale.php?id=<?= $sale['id'] ?>" class="btn" style="background: #ea580c;">Edit Sale</a>
        <a href="pos.php" class="btn btn-home">Back to POS</a>
    </div>

    <script>
        async function shareToWhatsApp(event) {
            event.preventDefault();
            const invoiceElement = document.querySelector('.invoice-box');
            const invoiceId = '<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?>';
            const fileName = `Invoice_${invoiceId}.pdf`;
            
            // Show loading state
            const btn = event.target.closest('.btn-whatsapp') || event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating PDF...';
            btn.style.pointerEvents = 'none';
            
            try {
                // Generate PDF
                const opt = {
                    margin: 0.5,
                    filename: fileName,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                };
                
                const pdfBlob = await html2pdf().set(opt).from(invoiceElement).outputPdf('blob');
                
                // Upload PDF to server first
                const formData = new FormData();
                formData.append('pdf', pdfBlob, fileName);
                formData.append('invoice_id', '<?= $sale['id'] ?>');
                
                const uploadResponse = await fetch('save_invoice_pdf.php', {
                    method: 'POST',
                    body: formData
                });
                
                const uploadResult = await uploadResponse.json();
                
                if (!uploadResult.success) {
                    throw new Error(uploadResult.error || 'Failed to upload PDF');
                }
                
                // Check if Web Share API is available (mobile devices)
                if (navigator.share && navigator.canShare) {
                    try {
                        const file = new File([pdfBlob], fileName, { type: 'application/pdf' });
                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: `Invoice #${invoiceId}`,
                                text: `Invoice Receipt - Invoice #${invoiceId}`,
                                files: [file]
                            });
                            return; // Successfully shared via Web Share API
                        }
                    } catch (shareError) {
                        // If share fails, fall through to WhatsApp URL method
                        console.log('Share API failed, using WhatsApp URL');
                    }
                }
                
                // Open WhatsApp directly with PDF link
                const message = `Invoice Receipt - Invoice #${invoiceId}\n\nPDF: ${uploadResult.full_url}`;
                const encodedMessage = encodeURIComponent(message);
                
                // Detect mobile device
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                
                if (isMobile) {
                    // Try WhatsApp app first, fallback to web
                    window.location.href = `whatsapp://send?text=${encodedMessage}`;
                    // Fallback to web if app doesn't open
                    setTimeout(() => {
                        window.open(`https://wa.me/?text=${encodedMessage}`, '_blank');
                    }, 1000);
                } else {
                    // Open WhatsApp Web directly
                    window.open(`https://web.whatsapp.com/send?text=${encodedMessage}`, '_blank');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error sharing PDF. Please try again.');
            } finally {
                // Restore button state
                btn.innerHTML = originalText;
                btn.style.pointerEvents = 'auto';
            }
        }
    </script>
</div>

</body>
</html>
