<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'resident': header("Location: resident/dashboard.php"); exit();
        case 'collector': header("Location: collector/dashboard.php"); exit();
        case 'admin':     header("Location: admin/dashboard.php"); exit();
        case 'officer':   header("Location: officer/dashboard.php"); exit();
        default:          header("Location: login.php"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Waste Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste Management</div>
        <div>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </div>
    <div class="container" style="text-align:center; margin-top:80px;">
        <h1>Smart Waste Collection Management System</h1>
        <p style="max-width:600px; margin:16px auto; color:#6b7a70; font-size:18px;">
            A centralized platform for residents, waste collectors, administrators,
            and municipal officers to coordinate waste collection requests, report issues,
            manage schedules, and improve service delivery.
        </p>
        <div style="margin-top:30px;">
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn" style="background:#f9a825; margin-left:10px;">Register as Resident</a>
        </div>
        <div style="margin-top:40px; display:flex; justify-content:center; gap:30px; flex-wrap:wrap;">
            <div class="feature-card">
                <div style="font-size:40px;">🏠</div>
                <h3>Residents</h3>
                <p>Request collection, report issues, view schedules</p>
            </div>
            <div class="feature-card">
                <div style="font-size:40px;">🚛</div>
                <h3>Collectors</h3>
                <p>View tasks, update collection status</p>
            </div>
            <div class="feature-card">
                <div style="font-size:40px;">👨‍💼</div>
                <h3>Admins</h3>
                <p>Manage users, assign tasks, create schedules</p>
            </div>
            <div class="feature-card">
                <div style="font-size:40px;">📊</div>
                <h3>Officers</h3>
                <p>Monitor performance and zone statistics</p>
            </div>
        </div>
    </div>
</body>
</html>