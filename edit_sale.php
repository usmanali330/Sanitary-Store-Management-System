<?php
include 'includes/header.php';

$sale_id = $_GET['id'] ?? null;
if (!$sale_id) {
    echo "<script>window.location.href='sales.php';</script>";
    exit;
}

// Fetch Sale details
$sale_stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$sale_stmt->bind_param("i", $sale_id);
$sale_stmt->execute();
$sale = $sale_stmt->get_result()->fetch_assoc();

if (!$sale) {
    echo "<script>window.location.href='sales.php';</script>";
    exit;
}

// Fetch Sale Items
$items_stmt = $conn->prepare("SELECT si.*, p.name, p.quantity as current_stock 
                              FROM sale_items si 
                              JOIN products p ON si.product_id = p.id 
                              WHERE si.sale_id = ?");
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$sale_items_res = $items_stmt->get_result();
$sale_items = [];
while ($row = $sale_items_res->fetch_assoc()) {
    $sale_items[] = [
        'id' => $row['product_id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'quantity' => (int)$row['quantity'],
        'max_stock' => (int)$row['current_stock'] + (int)$row['quantity'] // Important: include original qty
    ];
}

// Fetch Products for grid
$products_sql = "SELECT * FROM products ORDER BY name ASC";
$products_result = $conn->query($products_sql);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    // Check if this product is already in the sale cart to adjust displayed max stock
    $found_in_sale = false;
    foreach($sale_items as $si) {
        if($si['id'] == $row['id']) {
            $row['quantity'] = $row['quantity'] + $si['quantity'];
            $found_in_sale = true;
            break;
        }
    }
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
        position: relative;
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
    .top-list-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .top-list-tab {
        flex: 1;
        padding: 0.6rem;
        text-align: center;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: var(--transition);
        color: var(--text-light);
    }
    .top-list-tab.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    .top-list-tab:hover:not(.active) {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
</style>

<div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <h2 style="font-size: 1.5rem; font-weight: 700;">Edit Sale #<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?></h2>
    <a href="sales.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Sales</a>
</div>

<div class="pos-container">
    <!-- Left Side: Products -->
    <div style="display: flex; flex-direction: column;">
        <div class="top-list-tabs">
            <div class="top-list-tab active" data-type="all" onclick="setTopListFilter('all', this)">All List</div>
            <div class="top-list-tab" data-type="hardware" onclick="setTopListFilter('hardware', this)">Hardware</div>
            <div class="top-list-tab" data-type="sanitary" onclick="setTopListFilter('sanitary', this)">Sanitary</div>
            <div class="top-list-tab" data-type="ragrai" onclick="setTopListFilter('ragrai', this)">Ragrai</div>
        </div>

        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="productSearch" class="form-control search-input" placeholder="Scan barcode or search product..." onkeyup="filterProducts()">
        </div>
        
        <div class="product-grid" id="productGrid">
            <?php foreach ($products as $p): ?>
                <div class="product-card" onclick='addToCart(<?= json_encode($p) ?>)' data-name="<?= strtolower($p['name']) ?> <?= strtolower($p['brand']) ?>" data-type="<?= $p['top_list'] ?>">
                    <div style="position: relative;">
                        <?php if ($p['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img">
                        <?php else: ?>
                            <div class="product-img" style="display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-box text-light fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <span class="stock-badge"><?= $p['quantity'] ?> avail</span>
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
                <label style="font-size: 0.85rem; margin-bottom: 0.25rem; display: flex; justify-content: space-between;">
                    Customer
                    <button onclick="showAddCustomerModal()" class="btn btn-primary btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">
                        <i class="fa-solid fa-plus"></i> New
                    </button>
                </label>
                <select id="customerSelect" class="form-control" style="font-size: 0.9rem; padding: 0.5rem;">
                    <option value="">Walk-in Customer</option>
                    <?php while($c = $customers_result->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>" <?= $sale['customer_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?> (<?= ucfirst($c['type']) ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="cart-items" id="cartItems">
            <!-- Items injected here -->
        </div>

        <div class="cart-footer">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotalDisplay">Rs. 0.00</span>
            </div>
            <div class="summary-row" style="align-items: center;">
                <span>Discount</span>
                <input type="number" id="discountInput" class="form-control" style="width: 80px; padding: 0.25rem; font-size: 0.9rem; text-align: right;" value="<?= $sale['discount'] ?>" oninput="renderCart()">
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span id="totalDisplay" style="color: var(--primary-dark);">Rs. 0.00</span>
            </div>
            <div class="summary-row" style="align-items: center; margin-top: 1rem;">
                <span>Paid Amount</span>
                <input type="number" id="paidInput" class="form-control" style="width: 100px; padding: 0.25rem; font-size: 0.9rem; text-align: right;" value="<?= $sale['paid_amount'] ?>" oninput="calculateDue()">
            </div>
            <div class="summary-row" style="color: var(--danger-color); font-weight: 600;">
                <span>Due Amount</span>
                <span id="dueDisplay">Rs. 0.00</span>
            </div>
            
            <button onclick="updateSale()" id="updateBtn" class="btn btn-warning" style="width: 100%; margin-top: 1rem; font-size: 1rem; padding: 0.8rem;">
                <i class="fa-solid fa-save"></i> Update & Save Sale
            </button>
        </div>
    </div>
</div>

<script>
    let cart = <?= json_encode($sale_items) ?>;
    let currentTotal = 0;
    const saleId = <?= $sale_id ?>;
    let currentTopList = 'all';

    // Initial render
    window.onload = function() {
        renderCart();
    };

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

            if (matchesSearch && matchesType) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            if (existing.quantity < product.quantity) {
                existing.quantity++;
            } else {
                showToast('Stock limit reached!', 'warning');
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
            showToast('Stock limit reached!', 'warning');
        }
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const updateBtn = document.getElementById('updateBtn');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: var(--text-light); margin-top: 2rem;">
                    <i class="fa-solid fa-cart-shopping fa-2x" style="margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Cart is empty</p>
                </div>
            `;
            updateBtn.disabled = true;
            document.getElementById('subtotalDisplay').innerText = 'Rs. 0.00';
            document.getElementById('totalDisplay').innerText = 'Rs. 0.00';
            return;
        }

        updateBtn.disabled = false;
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
        currentTotal = total > 0 ? total : 0;

        document.getElementById('subtotalDisplay').innerText = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('totalDisplay').innerText = 'Rs. ' + currentTotal.toFixed(2);
        
        calculateDue();
    }

    function calculateDue() {
        const paid = parseFloat(document.getElementById('paidInput').value) || 0;
        const due = currentTotal - paid;
        document.getElementById('dueDisplay').innerText = 'Rs. ' + (due > 0 ? due.toFixed(2) : '0.00');
    }

    function updateSale() {
        if (!confirm('Update this sale record? Previous inventory and customer balance changes will be updated.')) return;

        const customerId = document.getElementById('customerSelect').value;
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const paidAmount = parseFloat(document.getElementById('paidInput').value) || 0;

        const data = {
            sale_id: saleId,
            customer_id: customerId ? customerId : null,
            items: cart,
            discount: discount,
            paid_amount: paidAmount
        };

        const btn = document.getElementById('updateBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
        btn.disabled = true;

        fetch('update_sale.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Response was not valid JSON:', text);
                    throw new Error('Server returned invalid response');
                }
            });
        })
        .then(result => {
            if (result.status === 'success') {
                window.location.href = 'invoice.php?id=' + saleId;
            } else {
                showToast('Error: ' + result.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error: ' + error.message, 'error');
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
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Response was not valid JSON:', text);
                    throw new Error('Server returned invalid response');
                }
            });
        })
        .then(result => {
            if (result.status === 'success') {
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
            showToast('Error: ' + error.message, 'error');
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
