<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($conn, $_POST['full_name']);
    $email = clean($conn, $_POST['email']);
    $phone = clean($conn, $_POST['phone']);
    $address = clean($conn, $_POST['address']);
    $zone = clean($conn, $_POST['zone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "An account with this email already exists. Please <a href='login.php'>login</a> instead.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, address, zone) VALUES (?, ?, ?, ?, 'resident', ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $hash, $address, $zone);
            
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                $success = "Account created successfully! You can now login.";
                logActivity($conn, $userId, 'register', "New resident registered: $email");
            } else {
                $error = "Registration failed: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Waste Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-box">
        <h2>📝 Resident Registration</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?> <a href="login.php">Login here</a></div><?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST">
            <label>Full Name *</label>
            <input type="text" name="full_name" required placeholder="John Doe">
            <label>Email Address *</label>
            <input type="email" name="email" required placeholder="john@example.com">
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="+1234567890">
            <label>Home Address</label>
            <input type="text" name="address" placeholder="123 Main St, City">
            <label>Zone</label>
            <input type="text" name="zone" placeholder="e.g. Zone A">
            <label>Password *</label>
            <input type="password" name="password" required placeholder="Min 8 characters">
            <label>Confirm Password *</label>
            <input type="password" name="confirm_password" required placeholder="Re-enter password">
            <button type="submit" class="btn-block">Register</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:14px;">
            Already have an account? <a href="login.php">Login</a>
        </p>
        <?php endif; ?>
    </div>
</body>
</html>