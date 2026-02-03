<?php
include 'includes/header.php';

$id = $_GET['id'] ?? null;
$product = null;
$error = '';
$success = '';

// Check if editing
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) {
        echo "<script>window.location.href='products.php';</script>";
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $top_list = $_POST['top_list'];
    $category_id = $_POST['category_id'];
    $brand = $_POST['brand'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $type = $_POST['type'];
    $cost_price = $_POST['cost_price'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $warranty = $_POST['warranty'];
    
    // Handle Image Upload
    $image = $product['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "uploads/";
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $new_filename;
        }
    }

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE products SET name=?, top_list=?, category_id=?, brand=?, size=?, color=?, type=?, cost_price=?, price=?, quantity=?, warranty=?, image=? WHERE id=?");
        $stmt->bind_param("ssissssddissi", $name, $top_list, $category_id, $brand, $size, $color, $type, $cost_price, $price, $quantity, $warranty, $image, $id);
        if ($stmt->execute()) {
            $success = "Product updated successfully!";
            // Refresh
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Error updating product.";
        }
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO products (name, top_list, category_id, brand, size, color, type, cost_price, price, quantity, warranty, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssddiss", $name, $top_list, $category_id, $brand, $size, $color, $type, $cost_price, $price, $quantity, $warranty, $image);
        if ($stmt->execute()) {
            $success = "Product added successfully!";
             echo "<script>window.location.href='products.php';</script>";
             exit;
        } else {
            $error = "Error adding product.";
        }
    }
}

// Get Categories
$cats = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories_data = [];
while($c = $cats->fetch_assoc()) {
    $categories_data[] = $c;
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?= $id ? 'Edit Product' : 'Add New Product' ?></h3>
        <a href="products.php" style="color: var(--primary-color); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Back to Products</a>
    </div>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="top_list">Top List *</label>
                <select name="top_list" id="top_list" class="form-control" required onchange="filterCategories()">
                    <option value="hardware" <?= ($product['top_list'] ?? '') == 'hardware' ? 'selected' : '' ?>>Hardware Products</option>
                    <option value="sanitary" <?= ($product['top_list'] ?? 'sanitary') == 'sanitary' ? 'selected' : '' ?>>Sanitary Products</option>
                    <option value="ragrai" <?= ($product['top_list'] ?? '') == 'ragrai' ? 'selected' : '' ?>>Ragrai Products</option>
                </select>
            </div>
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                </select>
            </div>
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" name="brand" id="brand" class="form-control" value="<?= htmlspecialchars($product['brand'] ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="size">Size</label>
                <input type="text" name="size" id="size" class="form-control" value="<?= htmlspecialchars($product['size'] ?? '') ?>" placeholder="e.g. 4 inch, Standard">
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" name="color" id="color" class="form-control" value="<?= htmlspecialchars($product['color'] ?? '') ?>" placeholder="e.g. White, Chrome">
            </div>
            <div class="form-group">
                <label for="type">Type/Material</label>
                <input type="text" name="type" id="type" class="form-control" value="<?= htmlspecialchars($product['type'] ?? '') ?>" placeholder="e.g. PVC, Ceramic">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="cost_price">Cost Price (Rs) *</label>
                <input type="number" step="0.01" name="cost_price" id="cost_price" class="form-control" required value="<?= htmlspecialchars($product['cost_price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="price">Selling Price (Rs) *</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" required value="<?= htmlspecialchars($product['price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" name="quantity" id="quantity" class="form-control" required value="<?= htmlspecialchars($product['quantity'] ?? '') ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="warranty">Warranty</label>
            <input type="text" name="warranty" id="warranty" class="form-control" value="<?= htmlspecialchars($product['warranty'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <?php if (!empty($product['image'])): ?>
                <div style="margin-top: 0.5rem;">
                    <small>Current Image:</small><br>
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Current" style="height: 60px; border-radius: 4px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-save"></i> <?= $id ? 'Update Product' : 'Save Product' ?>
        </button>
    </form>
</div>

<script>
    const categories = <?= json_encode($categories_data) ?>;
    const initialCategoryId = "<?= $product['category_id'] ?? '' ?>";

    function filterCategories() {
        const topList = document.getElementById('top_list').value;
        const categorySelect = document.getElementById('category_id');
        
        // Clear previous options
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        
        // Filter categories by top list
        const filtered = categories.filter(c => c.top_list === topList);
        
        filtered.forEach(c => {
            const option = document.createElement('option');
            option.value = c.id;
            option.textContent = c.name;
            if (c.id == initialCategoryId) {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
    }

    // Run on load
    window.onload = filterCategories;
</script>

<?php include 'includes/footer.php'; ?>
