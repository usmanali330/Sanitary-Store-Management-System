<?php
include 'includes/header.php';

// Fetch Products
$products_sql = "SELECT * FROM products WHERE quantity > 0 ORDER BY name ASC";
$products_result = $conn->query($products_sql);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// Fetch Customers
$customers_sql = "SELECT * FROM customers ORDER BY name ASC";
$customers_result = $conn->query($customers_sql);

?>

<style>
    .pos-container {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 1.5rem;
        height: calc(100vh - 120px);
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 1rem;
        overflow-y: auto;
        padding-right: 0.5rem;
        max-height: calc(100vh - 200px);
    }
    .product-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }
    .product-card:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
    }
    .product-img {
        width: 100%;
        height: 80px;
        object-fit: contain;
        margin-bottom: 0.5rem;
        background: #f8fafc;
        border-radius: 4px;
    }
    .product-price {
        font-weight: 700;
        color: var(--primary-color);
        margin-top: 0.25rem;
    }
    .cart-section {
        background: white;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: var(--shadow-sm);
    }
    .cart-header {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .cart-footer {
        padding: 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        border-radius: 0 0 12px 12px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    .total-row {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-color);
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #cbd5e1;
    }
    .qty-btn {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 1px solid #cbd5e1;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    .qty-btn:hover {
        background: #f1f5f9;
    }
    .stock-badge {
        font-size: 0.7rem;
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(0,0,0,0.6);
        color: white;
        padding: 2px 6px;
        border-radius: 99px;
    }
    .search-bar {
        margin-bottom: 1rem;
        position: relative;
    }
    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
    }
    .search-input {
        padding-left: 2.5rem;
    }
</style>

<div class="pos-container">
    <!-- Left Side: Products -->
    <div style="display: flex; flex-direction: column;">
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="productSearch" class="form-control search-input" placeholder="Scan barcode or search product..." onkeyup="filterProducts()">
        </div>
        
        <div class="product-grid" id="productGrid">
            <?php foreach ($products as $p): ?>
                <div class="product-card" onclick='addToCart(<?= json_encode($p) ?>)' data-name="<?= strtolower($p['name']) ?> <?= strtolower($p['brand']) ?>">
                    <div style="position: relative;">
                        <?php if ($p['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img">
                        <?php else: ?>
                            <div class="product-img" style="display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-box text-light fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <span class="stock-badge"><?= $p['quantity'] ?> left</span>
                    </div>
                    <div style="font-weight: 500; font-size: 0.9rem; line-height: 1.2; height: 2.4em; overflow: hidden;"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-price"><?= formatPrice($p['price']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right Side: Cart -->
    <div class="cart-section">
        <div class="cart-header">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.85rem; margin-bottom: 0.25rem;">Select Customer</label>
                <select id="customerSelect" class="form-control" style="font-size: 0.9rem; padding: 0.5rem;">
                    <option value="">Walk-in Customer</option>
                    <?php while($c = $customers_result->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= ucfirst($c['type']) ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="cart-items" id="cartItems">
            <!-- Items injected here -->
            <div style="text-align: center; color: var(--text-light); margin-top: 2rem;">
                <i class="fa-solid fa-cart-shopping fa-2x" style="margin-bottom: 1rem; opacity: 0.3;"></i>
                <p>Cart is empty</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotalDisplay">Rs. 0.00</span>
            </div>
            <div class="summary-row" style="align-items: center;">
                <span>Discount</span>
                <input type="number" id="discountInput" class="form-control" style="width: 80px; padding: 0.25rem; font-size: 0.9rem; text-align: right;" value="0" oninput="renderCart()">
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span id="totalDisplay" style="color: var(--primary-dark);">Rs. 0.00</span>
            </div>
            
            <button onclick="processSale()" id="checkoutBtn" class="btn btn-primary" style="width: 100%; margin-top: 1rem; font-size: 1rem; padding: 0.8rem;" disabled>
                <i class="fa-solid fa-receipt"></i> Process Sale
            </button>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function filterProducts() {
        const query = document.getElementById('productSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function addToCart(product) {
        // Check if exists
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            if (existing.quantity < product.quantity) {
                existing.quantity++;
            } else {
                alert('Stock limit reached!');
            }
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                max_stock: parseInt(product.quantity)
            });
        }
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function updateQty(index, delta) {
        const item = cart[index];
        const newQty = item.quantity + delta;
        if (newQty > 0 && newQty <= item.max_stock) {
            item.quantity = newQty;
            renderCart();
        } else if (newQty > item.max_stock) {
            alert('Stock limit reached!');
        }
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: var(--text-light); margin-top: 2rem;">
                    <i class="fa-solid fa-cart-shopping fa-2x" style="margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Cart is empty</p>
                </div>
            `;
            checkoutBtn.disabled = true;
            document.getElementById('subtotalDisplay').innerText = 'Rs. 0.00';
            document.getElementById('totalDisplay').innerText = 'Rs. 0.00';
            return;
        }

        checkoutBtn.disabled = false;
        let html = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const total = item.price * item.quantity;
            subtotal += total;
            html += `
                <div class="cart-item">
                    <div style="flex: 1;">
                        <div style="font-weight: 500;">${item.name}</div>
                        <div style="color: var(--text-light); font-size: 0.8rem;">Rs. ${item.price} x ${item.quantity}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-right: 1rem;">
                        <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                        <span style="font-weight: 500; min-width: 20px; text-align: center;">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600;">Rs. ${total.toFixed(2)}</div>
                        <i class="fa-solid fa-trash text-danger" style="font-size: 0.8rem; cursor: pointer; margin-top: 0.25rem;" onclick="removeFromCart(${index})"></i>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const total = subtotal - discount;

        document.getElementById('subtotalDisplay').innerText = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('totalDisplay').innerText = 'Rs. ' + (total > 0 ? total.toFixed(2) : '0.00');
    }

    function processSale() {
        if (!confirm('Complete this sale?')) return;

        const customerId = document.getElementById('customerSelect').value;
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;

        const data = {
            customer_id: customerId ? customerId : null,
            items: cart,
            discount: discount
        };

        const btn = document.getElementById('checkoutBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        fetch('process_sale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                alert('Sale successful!');
                window.location.href = 'invoice.php?id=' + result.sale_id;
            } else {
                alert('Error: ' + result.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
