<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $name = clean($conn, $_POST['full_name']);
    $email = clean($conn, $_POST['email']);
    $phone = clean($conn, $_POST['phone']);
    $role = clean($conn, $_POST['role']);
    $zone = clean($conn, $_POST['zone']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        setFlash('error', 'Please fill in all required fields.');
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            setFlash('error', 'A user with this email already exists.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, zone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $hash, $role, $zone);
            if ($stmt->execute()) {
                setFlash('success', "User created successfully.");
                logActivity($conn, $_SESSION['user_id'], 'create_user', "Created $role: $email");
            } else {
                setFlash('error', 'Failed to create user.');
            }
            $stmt->close();
        }
        $check->close();
    }
    header("Location: users.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'suspend') {
        $conn->query("UPDATE users SET status='suspended' WHERE user_id=$id");
        setFlash('info', 'User suspended.');
    } elseif ($_GET['action'] === 'activate') {
        $conn->query("UPDATE users SET status='active' WHERE user_id=$id");
        setFlash('success', 'User activated.');
    } elseif ($_GET['action'] === 'delete') {
        $conn->query("DELETE FROM users WHERE user_id=$id");
        setFlash('info', 'User deleted.');
    }
    header("Location: users.php");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste - Admin</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php" class="active">Users</a>
            <a href="requests.php">Requests</a>
            <a href="reports.php">Reports</a>
            <a href="schedules.php">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>
        
        <div class="card">
            <h2>Create New User</h2>
            <form method="POST">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div><label>Full Name *</label><input type="text" name="full_name" required></div>
                    <div><label>Email *</label><input type="email" name="email" required></div>
                    <div><label>Phone</label><input type="text" name="phone"></div>
                    <div>
                        <label>Role *</label>
                        <select name="role" required>
                            <option value="collector">Waste Collector</option>
                            <option value="officer">Municipal Officer</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div><label>Zone (for collectors)</label><input type="text" name="zone"></div>
                    <div><label>Password *</label><input type="password" name="password" required></div>
                </div>
                <button type="submit" name="create_user" value="1" style="margin-top:16px;">Create User</button>
            </form>
        </div>

        <div class="card">
            <h2>All Users</h2>
            <table>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Zone</th><th>Status</th><th>Actions</th></tr>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="badge badge-waste"><?= $row['role'] ?></span></td>
                    <td><?= htmlspecialchars($row['zone'] ?: '-') ?></td>
                    <td><span class="badge <?= $row['status'] === 'active' ? 'badge-completed' : 'badge-cancelled' ?>"><?= $row['status'] ?></span></td>
                    <td>
                        <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                            <?php if ($row['status'] === 'active'): ?>
                                <a href="?action=suspend&id=<?= $row['user_id'] ?>" class="btn btn-sm btn-danger">Suspend</a>
                            <?php else: ?>
                                <a href="?action=activate&id=<?= $row['user_id'] ?>" class="btn btn-sm">Activate</a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?= $row['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user permanently?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">(You)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>