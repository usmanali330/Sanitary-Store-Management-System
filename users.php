<?php
include 'includes/header.php';


if (!isAdmin()) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// Handle Add/Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<script>alert('User added successfully'); window.location.href='users.php';</script>";
    } else {
        echo "<script>alert('Error adding user');</script>";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['user_id']) { // Check not deleting self
        $conn->query("DELETE FROM users WHERE id=$id");
        echo "<script>window.location.href='users.php';</script>";
    } else {
        echo "<script>alert('Cannot delete yourself');</script>";
    }
}

$users = $conn->query("SELECT * FROM users");
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <div class="card" style="height: fit-content;">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Add New User</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Add User</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Users List</h3>
            <div class="table-container" style="box-shadow: none; padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                        <?php while($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <span class="badge <?= $row['role'] == 'admin' ? 'badge-warning' : 'badge-success' ?>">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')"><i class="fa-solid fa-trash"></i></a>
                                <?php else: ?>
                                    <span style="color: var(--text-light); font-size: 0.8rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No users found or database error.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
