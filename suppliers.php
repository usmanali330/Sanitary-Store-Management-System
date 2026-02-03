<?php
include 'includes/header.php';

$edit_id = $_GET['edit'] ?? null;
$supplier = null;

if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if (!empty($_POST['id'])) {
        $stmt = $conn->prepare("UPDATE suppliers SET name=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $_POST['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $address);
    }
    
    if ($stmt->execute()) {
        echo "<script>window.location.href='suppliers.php';</script>";
    }
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM suppliers WHERE id=" . $_GET['delete']);
    echo "<script>window.location.href='suppliers.php';</script>";
}

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
?>

<div class="app-container" style="padding: 0;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem;"><?= $edit_id ? 'Edit Supplier' : 'Add Supplier' ?></h3>
            <form method="POST">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="id" value="<?= $edit_id ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Company Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($supplier['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save</button>
                    <?php if($edit_id): ?>
                        <a href="suppliers.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Supplier List</h3>
            <div class="table-container" style="box-shadow: none; padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $suppliers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($row['phone']) ?></div>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($row['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td>
                                <a href="suppliers.php?edit=<?= $row['id'] ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
                                <a href="suppliers.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete supplier?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
