<?php
// Show login message
if (isset($_SESSION['login_message'])) {
    echo '<div class="login-banner">👋 ' . $_SESSION['login_message'] . ' <span class="role-badge role-resident">Resident</span></div>';
    unset($_SESSION['login_message']);
}

require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('resident');

$uid = $_SESSION['user_id'];

$reqCount = $conn->query("SELECT COUNT(*) c FROM collection_requests WHERE resident_id=$uid")->fetch_assoc()['c'];
$reportCount = $conn->query("SELECT COUNT(*) c FROM waste_reports WHERE resident_id=$uid")->fetch_assoc()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) c FROM collection_requests WHERE resident_id=$uid AND status='pending'")->fetch_assoc()['c'];
$unread = $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_assoc()['c'];
$requests = $conn->query("SELECT * FROM collection_requests WHERE resident_id=$uid ORDER BY requested_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste</div>
        <div>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="request.php">New Request</a>
            <a href="report.php">Report Issue</a>
            <a href="schedule.php">Schedules</a>
            <a href="notifications.php">Notifications (<?= $unread ?>)</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
        <div class="dashboard-grid">
            <div class="stat-card"><div class="num"><?= $reqCount ?></div><div class="label">Collection Requests</div></div>
            <div class="stat-card"><div class="num"><?= $reportCount ?></div><div class="label">Issues Reported</div></div>
            <div class="stat-card"><div class="num"><?= $pendingCount ?></div><div class="label">Pending Requests</div></div>
            <div class="stat-card"><div class="num"><?= $unread ?></div><div class="label">Unread Notifications</div></div>
        </div>

        <div class="card">
            <h3>My Recent Requests</h3>
            <table>
                <tr><th>Date</th><th>Address</th><th>Waste Type</th><th>Status</th></tr>
                <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['requested_at'])) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><span class="badge badge-waste"><?= htmlspecialchars($row['waste_type']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>