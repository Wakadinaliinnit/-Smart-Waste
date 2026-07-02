<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('resident');

$uid = $_SESSION['user_id'];
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");

$notifications = $conn->query("SELECT * FROM notifications WHERE user_id=$uid ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="request.php">New Request</a>
            <a href="report.php">Report Issue</a>
            <a href="schedule.php">Schedules</a>
            <a href="notifications.php" class="active">Notifications</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <h2>Notifications</h2>
            <?php if ($notifications->num_rows === 0): ?>
                <p style="text-align:center; color:#6b7a70; padding:20px;">No notifications yet.</p>
            <?php else: ?>
                <?php while ($row = $notifications->fetch_assoc()): ?>
                <div class="card" style="border-left: 4px solid <?= $row['is_read'] ? '#e0e4e0' : '#2e7d32' ?>;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <div>
                            <strong><?= htmlspecialchars($row['title']) ?></strong>
                            <p style="margin:6px 0;"><?= htmlspecialchars($row['message']) ?></p>
                        </div>
                        <?php if (!$row['is_read']): ?>
                            <span class="badge badge-pending">New</span>
                        <?php endif; ?>
                    </div>
                    <small style="color:#6b7a70;"><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></small>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>