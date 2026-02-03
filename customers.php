<?php
include 'includes/header.php';

// Handle Add/Edit User (Reuse logic pattern)
$edit_id = $_GET['edit'] ?? null;
$customer_data = null;

if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $customer_data = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $type = $_POST['type'];
    $balance = isset($_POST['balance']) ? $_POST['balance'] : 0;

    if (!empty($_POST['id'])) {
        $stmt = $conn->prepare("UPDATE customers SET name=?, phone=?, email=?, address=?, type=?, balance=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $phone, $email, $address, $type, $balance, $_POST['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address, type, balance) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $phone, $email, $address, $type, $balance);
    }
    
    if ($stmt->execute()) {
        echo "<script>window.location.href='customers.php';</script>";
    } else {
        echo "<script>alert('Error saving customer');</script>";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM customers WHERE id=$id");
    echo "<script>window.location.href='customers.php';</script>";
}

$customers = $conn->query("SELECT * FROM customers ORDER BY id DESC");
?>

<div class="app-container" style="padding: 0;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <!-- Form -->
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem;"><?= $edit_id ? 'Edit Customer' : 'Add Customer' ?></h3>
            <form method="POST">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="id" value="<?= $edit_id ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($customer_data['name'] ?? '') ?>">
                </div>

                <?php if($edit_id): ?>
                <div class="form-group">
                    <label>Current Balance</label>
                    <input type="text" class="form-control" readonly value="<?= formatPrice($customer_data['balance'] ?? 0) ?>" style="background: #f8fafc; font-weight: 600; color: var(--danger-color);">
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="regular" <?= ($customer_data['type'] ?? '') == 'regular' ? 'selected' : '' ?>>Regular</option>
                        <option value="contractor" <?= ($customer_data['type'] ?? '') == 'contractor' ? 'selected' : '' ?>>Contractor/Plumber</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer_data['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer_data['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($customer_data['address'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save</button>
                    <?php if($edit_id): ?>
                        <a href="customers.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Customer List</h3>
            <div class="table-container" style="box-shadow: none; padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Balance</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $customers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></div>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($row['address']) ?></small>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($row['phone']) ?></div>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($row['email']) ?></small>
                            </td>
                            <td>
                                <?php 
                                    $display_balance = isset($row['balance']) ? $row['balance'] : 0;
                                    if ($display_balance > 0): 
                                ?>
                                    <span style="color: var(--danger-color); font-weight: 600;"><?= formatPrice($display_balance) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--success-color);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $row['type'] == 'contractor' ? 'badge-warning' : 'badge-success' ?>">
                                    <?= ucfirst($row['type']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="customers.php?edit=<?= $row['id'] ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
                                <a href="customers.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete customer?')"><i class="fa-solid fa-trash"></i></a>
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
