<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $loginAs = $_POST['login_as'] ?? 'user';

    $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] !== 'active') {
            $error = "Your account is not active. Please contact the administrator.";
        } elseif (password_verify($password, $user['password_hash'])) {
            if ($loginAs === 'admin' && $user['role'] !== 'admin') {
                $error = "Unauthorized access. You cannot login as admin.";
            } else {
            session_regenerate_id(true);

            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['email']         = $user['email'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time']    = date('Y-m-d H:i:s');

            logActivity($conn, $user['user_id'], 'login', 'User logged in');

            switch ($user['role']) {
                case 'resident':  header("Location: resident/dashboard.php");  break;
                case 'collector': header("Location: collector/dashboard.php"); break;
                case 'admin':     header("Location: admin/dashboard.php");     break;
                case 'officer':   header("Location: officer/dashboard.php");   break;
                default:          header("Location: index.php");               break;
            }
            exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Waste Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 16px;
            padding: 12px;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-box">
    <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="your@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">

            <label>Login As</label>
            <select name="login_as">
                <option value="user" <?= (($_POST['login_as'] ?? 'user') === 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (($_POST['login_as'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrator</option>
            </select>

            <button type="submit" class="btn-block">Login</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:14px;">
            Don't have an account? <a href="register.php">Register as Resident</a>
        </p>
    </div>
</body>
</html>