<?php
include 'includes/header.php'; 

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Product deleted successfully'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Error deleting product');</script>";
    }
}

// Search & Filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.brand LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($category_filter) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$cats = $conn->query("SELECT * FROM categories");
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Product Management</h3>
            <p style="color: var(--text-light); font-size: 0.9rem;">Manage your store inventory efficiently.</p>
        </div>
        <a href="product_form.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add New Product
        </a>
    </div>

    <form method="GET" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Search product name or brand..." value="<?= htmlspecialchars($search) ?>" style="max-width: 300px;">
        
        <select name="category" class="form-control" style="max-width: 200px;">
            <option value="">All Categories</option>
            <?php while($c = $cats->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $category_filter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
        </select>
        
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if($search || $category_filter): ?>
            <a href="products.php" class="btn btn-danger" style="text-decoration: none;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="table-container" style="box-shadow: none; padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Size/Type</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999;">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></div>
                            <small style="color: var(--text-light);"><?= htmlspecialchars($row['warranty']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['brand']) ?></td>
                        <td><?= htmlspecialchars($row['size']) ?> <br> <small><?= htmlspecialchars($row['type']) ?></small></td>
                        <td style="font-weight: 600;"><?= formatPrice($row['price']) ?></td>
                        <td>
                            <?php if ($row['quantity'] < 10): ?>
                                <span class="badge badge-warning"><?= $row['quantity'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= $row['quantity'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="product_form.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a href="products.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
