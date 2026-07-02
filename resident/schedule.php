<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('resident');

$myZone = $conn->query("SELECT zone FROM users WHERE user_id={$_SESSION['user_id']}")->fetch_assoc()['zone'];

$sql = "SELECT * FROM collection_schedules WHERE scheduled_date >= CURDATE()";
if ($myZone) $sql .= " AND zone='" . $conn->real_escape_string($myZone) . "'";
$sql .= " ORDER BY scheduled_date ASC";
$schedules = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Schedules</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="request.php">New Request</a>
            <a href="report.php">Report Issue</a>
            <a href="schedule.php" class="active">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <h2>Upcoming Collection Schedules <?= $myZone ? "for " . htmlspecialchars($myZone) : "" ?></h2>
            <table>
                <tr><th>Zone</th><th>Date</th><th>Time</th><th>Status</th></tr>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['zone']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['scheduled_date'])) ?></td>
                    <td><?= $row['scheduled_time'] ?: '-' ?></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>