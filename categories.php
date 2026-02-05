<?php
include 'includes/header.php';

// Handle Add/Edit/Delete
$edit_id = null;
$edit_name = '';

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($cat = $res->fetch_assoc()) {
            $edit_name = $cat['name'];
            $edit_top_list = $cat['top_list'] ?? 'sanitary';
        }
    } else {
        die("Database error: " . $conn->error);
    }
}

if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM categories WHERE id = $del_id");
    echo "<script>window.location.href='categories.php';</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $top_list = isset($_POST['top_list']) ? $_POST['top_list'] : 'sanitary';
    if (!empty($name)) {
        // Check if top_list column exists
        $check_column = $conn->query("SHOW COLUMNS FROM categories LIKE 'top_list'");
        $has_top_list = $check_column && $check_column->num_rows > 0;
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            if ($has_top_list) {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, top_list = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("ssi", $name, $top_list, $_POST['id']);
                    $stmt->execute();
                } else {
                    die("Database error: " . $conn->error);
                }
            } else {
                $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $name, $_POST['id']);
                    $stmt->execute();
                } else {
                    die("Database error: " . $conn->error);
                }
            }
        } else {
            // Insert
            if ($has_top_list) {
                $stmt = $conn->prepare("INSERT INTO categories (name, top_list) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ss", $name, $top_list);
                    $stmt->execute();
                } else {
                    die("Database error: " . $conn->error);
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                if ($stmt) {
                    $stmt->bind_param("s", $name);
                    $stmt->execute();
                } else {
                    die("Database error: " . $conn->error);
                }
            }
        }
        echo "<script>window.location.href='categories.php';</script>";
    }
}

// Check existing categories
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");

// Safe read of filters:
$top_list_filter = $_GET['top_list'] ?? '';
?>

<div class="app-container" style="padding: 0;"> 
    <!-- Reuse container styles if needed, but we are inside main content already -->
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <!-- Form Section -->
        <div>
            <div class="card">
                <h3 style="margin-bottom: 1rem; font-size: 1.25rem;"><?= $edit_id ? 'Edit Category' : 'Add Category' ?></h3>
                <form method="POST">
                    <?php if ($edit_id): ?>
                        <input type="hidden" name="id" value="<?= $edit_id ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Top List Selection</label>
                        <select name="top_list" class="form-control" required>
                            <option value="hardware" <?= ($edit_top_list ?? '') == 'hardware' ? 'selected' : '' ?>>Hardware Products</option>
                            <option value="sanitary" <?= ($edit_top_list ?? 'sanitary') == 'sanitary' ? 'selected' : '' ?>>Sanitary Products</option>
                            <option value="ragrai" <?= ($edit_top_list ?? '') == 'ragrai' ? 'selected' : '' ?>>Ragrai Products</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit_name) ?>" placeholder="e.g. PVC Pipes">
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <?= $edit_id ? 'Update' : 'Add' ?>
                        </button>
                        <?php if($edit_id): ?>
                            <a href="categories.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Section -->
        <div>
            <div class="card">
                <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Category List</h3>
                <div class="table-container" style="box-shadow: none; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Top List</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $categories->fetch_assoc()): 
                                $topList = $row['top_list'] ?? 'sanitary';
                                $badgeBg = $topList == 'hardware' ? '#eff6ff' : ($topList == 'sanitary' ? '#ecfdf5' : '#fff7ed');
                                $badgeColor = $topList == 'hardware' ? '#2563eb' : ($topList == 'sanitary' ? '#10b981' : '#f97316');
                            ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td>
                                    <span class="badge" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>;">
                                        <?= ucfirst($topList) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <a href="categories.php?edit=<?= $row['id'] ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
                                    <a href="categories.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
