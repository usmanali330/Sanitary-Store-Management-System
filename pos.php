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
    :root {
        --pos-bg: #f8fafc;
        --pos-border: #e2e8f0;
    }

    .pos-container {
        display: grid;
        grid-template-columns: 1.6fr 1.2fr;
        gap: 2rem;
        height: calc(100vh - 140px);
    }

    /* --- Product Selection Area --- */
    .selection-area {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        overflow: hidden;
    }

    .top-list-tabs {
        display: flex;
        gap: 0.75rem;
        padding: 4px;
        background: #eef2ff;
        border-radius: 12px;
    }

    .top-list-tab {
        flex: 1;
        padding: 0.75rem;
        text-align: center;
        background: transparent;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 700;
        transition: var(--transition);
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .top-list-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: var(--shadow-subtle);
    }

    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        font-size: 1.1rem;
    }

    .search-input {
        padding-left: 3.5rem !important;
        height: 55px;
        border-radius: 16px !important;
        font-size: 1rem !important;
        box-shadow: var(--shadow-subtle) !important;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1.25rem;
        overflow-y: auto;
        padding: 4px;
        flex: 1;
    }

    .product-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1rem;
        cursor: pointer;
        transition: var(--transition);
        text-align: left;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .product-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .product-img-wrap {
        width: 100%;
        aspect-ratio: 1;
        background: var(--background);
        border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }

    .product-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: var(--transition);
    }

    .product-card:hover .product-img { transform: scale(1.1); }

    .product-info h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.3;
        height: 2.6em;
        overflow: hidden;
    }

    .product-price {
        font-weight: 800;
        color: var(--primary);
        font-size: 1.1rem;
    }

    .stock-tag {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
    }

    /* --- Cart Area --- */
    .cart-section {
        background: white;
        border-radius: var(--radius-xl);
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .cart-header {
        padding: 1.5rem;
        border-bottom: 3px solid var(--background);
    }

    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        padding: 1.25rem 0;
        border-bottom: 1.5px solid var(--background);
    }

    .cart-item-info strong {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .cart-item-info span {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--background);
        padding: 4px;
        border-radius: 50px;
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 800;
        box-shadow: var(--shadow-subtle);
        transition: var(--transition);
    }

    .qty-btn:hover { background: var(--primary); color: white; }

    .item-total {
        min-width: 100px;
        text-align: right;
        font-weight: 800;
        color: var(--text-main);
    }

    .cart-footer {
        padding: 2rem;
        background: #f8fafc;
        border-top: 1px solid var(--border);
    }

    .summary-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 2rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        font-weight: 600;
        color: var(--text-muted);
    }

    .summary-total {
        margin-top: 12px;
        padding-top: 20px;
        border-top: 2px dashed var(--border);
        color: var(--text-main);
        font-size: 1.5rem;
        font-weight: 800;
    }

    #checkoutBtn {
        height: 60px;
        border-radius: 18px;
        font-size: 1.1rem;
        gap: 12px;
    }

    /* Modal Styling */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(8px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-content {
        animation: modalIn 0.3s cubic-bezier(0.19, 1, 0.22, 1);
    }

    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    @media (max-width: 1280px) {
        .pos-container { grid-template-columns: 1fr; height: auto; }
        .product-grid { max-height: 600px; }
    }
</style>


<div class="pos-container">
    <!-- Left Side: Selection Area -->
    <div class="selection-area">
        <div class="top-list-tabs">
            <div class="top-list-tab active" data-type="all" onclick="setTopListFilter('all', this)">Global List</div>
            <div class="top-list-tab" data-type="hardware" onclick="setTopListFilter('hardware', this)">Hardware</div>
            <div class="top-list-tab" data-type="sanitary" onclick="setTopListFilter('sanitary', this)">Sanitary</div>
            <div class="top-list-tab" data-type="ragrai" onclick="setTopListFilter('ragrai', this)">Ragrai</div>
        </div>

        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="productSearch" class="form-control search-input" placeholder="Scan Barcode or Search Products..." onkeyup="filterProducts()">
        </div>
        
        <div class="product-grid" id="productGrid">
            <?php foreach ($products as $p): ?>
                <div class="product-card" onclick='addToCart(<?= json_encode($p) ?>)' data-name="<?= strtolower($p['name']) ?> <?= strtolower($p['brand']) ?>" data-type="<?= $p['top_list'] ?>">
                    <div class="product-img-wrap">
                        <?php if ($p['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img">
                        <?php else: ?>
                            <i class="fa-solid fa-box text-light fa-2x"></i>
                        <?php endif; ?>
                        <span class="stock-tag"><?= $p['quantity'] ?> IN STOCK</span>
                    </div>
                    <div class="product-info">
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <div class="product-price"><?= formatPrice($p['price']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right Side: Cart Hub -->
    <div class="cart-section">
        <div class="cart-header">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="display: flex; justify-content: space-between; align-items: center;">
                    Customer Profile
                    <button onclick="showAddCustomerModal()" class="btn btn-primary btn-sm" style="border-radius: 8px;">
                        <i class="fa-solid fa-user-plus"></i> New
                    </button>
                </label>
                <select id="customerSelect" class="form-control" style="margin-top: 10px;">
                    <option value="">Walk-in Customer</option>
                    <?php while($c = $customers_result->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= ucfirst($c['type']) ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="cart-items" id="cartItems">
            <div style="text-align: center; color: var(--text-light); margin-top: 6rem;">
                <i class="fa-solid fa-cart-shopping fa-3x" style="margin-bottom: 1.5rem; opacity: 0.1;"></i>
                <p style="font-weight: 700; opacity: 0.4;">Your cart is empty</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="summary-grid">
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay">Rs. 0</span>
                </div>
                <div class="summary-item" style="align-items: center;">
                    <span>Reduction (Rs)</span>
                    <input type="number" id="discountInput" class="form-control" style="width: 100px; text-align: right; padding: 6px 12px; height: 40px;" value="0" oninput="renderCart()">
                </div>
                <div class="summary-item summary-total">
                    <span>Total Bill</span>
                    <span id="totalDisplay">Rs. 0</span>
                </div>
                <div class="summary-item" style="align-items: center; margin-top: 10px;">
                    <span>Payment Received</span>
                    <input type="number" id="paidInput" class="form-control" style="width: 120px; text-align: right; font-weight: 800; border-color: var(--primary);" placeholder="0" oninput="calculateDue()">
                </div>
                <div class="summary-item" style="color: var(--danger); font-weight: 800; font-size: 1.1rem; margin-top: 5px;">
                    <span>Due Balance</span>
                    <span id="dueDisplay">Rs. 0</span>
                </div>
            </div>
            
            <button onclick="processSale()" id="checkoutBtn" class="btn btn-primary btn-lg w-100" disabled>
                <i class="fa-solid fa-check-circle"></i> Complete Checkout
            </button>
        </div>
    </div>
</div>

<script>
    let cart = [];
    let currentTopList = 'all';
    let currentTotal = 0;

    function setTopListFilter(type, el) {
        currentTopList = type;
        document.querySelectorAll('.top-list-tab').forEach(tab => tab.classList.remove('active'));
        el.classList.add('active');
        filterProducts();
    }

    function filterProducts() {
        const query = document.getElementById('productSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const type = card.getAttribute('data-type');
            
            const matchesSearch = name.includes(query);
            const matchesType = (currentTopList === 'all' || type === currentTopList);

            card.style.display = (matchesSearch && matchesType) ? 'flex' : 'none';
        });
    }

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            if (existing.quantity < product.quantity) {
                existing.quantity++;
            } else {
                showToast('Limited Stock Available!', 'warning');
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
        showToast('Item Added to Cart');
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
            showToast('Insufficient Stock!', 'warning');
        }
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: var(--text-light); margin-top: 6rem;">
                    <i class="fa-solid fa-cart-shopping fa-3x" style="margin-bottom: 1.5rem; opacity: 0.1;"></i>
                    <p style="font-weight: 700; opacity: 0.4;">Your cart is empty</p>
                </div>
            `;
            checkoutBtn.disabled = true;
            document.getElementById('subtotalDisplay').innerText = 'Rs. 0';
            document.getElementById('totalDisplay').innerText = 'Rs. 0';
            document.getElementById('dueDisplay').innerText = 'Rs. 0';
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
                    <div class="cart-item-info">
                        <strong>${item.name}</strong>
                        <span>Rs. ${item.price.toLocaleString()} × ${item.quantity}</span>
                    </div>
                    <div class="cart-item-actions">
                        <div class="qty-control">
                            <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <span style="font-weight: 800; min-width: 25px; text-align: center;">${item.quantity}</span>
                            <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                        <div class="item-total">Rs. ${total.toLocaleString()}</div>
                        <button onclick="removeFromCart(${index})" style="background: none; border: none; color: var(--danger); cursor: pointer;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        currentTotal = Math.max(0, subtotal - discount);

        document.getElementById('subtotalDisplay').innerText = 'Rs. ' + subtotal.toLocaleString();
        document.getElementById('totalDisplay').innerText = 'Rs. ' + currentTotal.toLocaleString();
        
        calculateDue();
    }

    function calculateDue() {
        const paid = parseFloat(document.getElementById('paidInput').value) || 0;
        const due = Math.max(0, currentTotal - paid);
        document.getElementById('dueDisplay').innerText = 'Rs. ' + due.toLocaleString();
    }

    function processSale() {
        if (!confirm('Proceed to generate invoice for this sale?')) return;

        const customerId = document.getElementById('customerSelect').value;
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const paidAmount = parseFloat(document.getElementById('paidInput').value) || 0;

        const data = {
            customer_id: customerId ? customerId : null,
            items: cart,
            discount: discount,
            paid_amount: paidAmount
        };

        const btn = document.getElementById('checkoutBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finalizing Sale...';
        btn.disabled = true;

        fetch('process_sale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                window.location.href = 'invoice.php?id=' + result.sale_id;
            } else {
                showToast(result.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Communication Error', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>


<!-- Quick Add Customer Modal -->
<div id="customerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 400px; margin: 20px;">
        <h3 style="margin-bottom: 1.5rem;">Add New Customer</h3>
        <form id="quickAddCustomerForm" onsubmit="event.preventDefault(); submitQuickCustomer();">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" id="quickCustName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" id="quickCustPhone" class="form-control">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select id="quickCustType" class="form-control">
                    <option value="regular">Regular</option>
                    <option value="contractor">Contractor/Plumber</option>
                </select>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea id="quickCustAddress" class="form-control" rows="2"></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Customer</button>
                <button type="button" onclick="hideAddCustomerModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ... existing script functions ...
    function showAddCustomerModal() {
        document.getElementById('customerModal').style.display = 'flex';
        document.getElementById('quickCustName').focus();
    }

    function hideAddCustomerModal() {
        document.getElementById('customerModal').style.display = 'none';
        document.getElementById('quickAddCustomerForm').reset();
    }

    function submitQuickCustomer() {
        const name = document.getElementById('quickCustName').value;
        const phone = document.getElementById('quickCustPhone').value;
        const type = document.getElementById('quickCustType').value;
        const address = document.getElementById('quickCustAddress').value;

        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('type', type);
        formData.append('address', address);

        fetch('ajax_add_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                // Add to dropdown and select
                const select = document.getElementById('customerSelect');
                const option = document.createElement('option');
                option.value = result.id;
                option.text = `${result.name} (${result.type.charAt(0).toUpperCase() + result.type.slice(1)})`;
                option.selected = true;
                select.add(option);
                
                hideAddCustomerModal();
                showToast('Customer added successfully!');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
    // ... rest of script ...
</script>

<?php include 'includes/footer.php'; ?>
