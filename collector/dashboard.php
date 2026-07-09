<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('collector');

$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request'])) {
    $rid = (int)$_POST['request_id'];
    $newStatus = clean($conn, $_POST['new_status']);
    $stmt = $conn->prepare("UPDATE collection_requests SET status=?, completed_at=IF(?='completed', NOW(), completed_at) WHERE request_id=? AND assigned_collector_id=?");
    $stmt->bind_param("ssii", $newStatus, $newStatus, $rid, $uid);
    $stmt->execute();
    logActivity($conn, $uid, 'update_request_status', "Request #$rid -> $newStatus");
    setFlash('success', 'Request status updated.');
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_report'])) {
    $rid = (int)$_POST['report_id'];
    $newStatus = clean($conn, $_POST['new_status']);
    $stmt = $conn->prepare("UPDATE waste_reports SET status=?, resolved_at=IF(?='resolved', NOW(), resolved_at) WHERE report_id=? AND assigned_collector_id=?");
    $stmt->bind_param("ssii", $newStatus, $newStatus, $rid, $uid);
    $stmt->execute();
    logActivity($conn, $uid, 'update_report_status', "Report #$rid -> $newStatus");
    setFlash('success', 'Report status updated.');
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

$requests = $conn->query("SELECT * FROM collection_requests WHERE assigned_collector_id=$uid ORDER BY requested_at DESC");
$reports = $conn->query("SELECT * FROM waste_reports WHERE assigned_collector_id=$uid ORDER BY reported_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste - Collector</div>
        <div>
            <a href="dashboard.php" class="active">My Tasks</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>

        <?php if (isset($_SESSION['login_message'])): ?>
            <div class="login-banner">
                👋 <?= htmlspecialchars($_SESSION['login_message']) ?>
                <span class="role-badge role-collector">Collector</span>
            </div>
            <?php unset($_SESSION['login_message']); ?>
        <?php endif; ?>

        <h2>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>

        <div class="card">
            <h3>Assigned Collection Requests</h3>
            <table>
                <tr><th>Date</th><th>Address</th><th>Waste Type</th><th>Status</th><th>Action</th></tr>
                <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['requested_at'])) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><span class="badge badge-waste"><?= htmlspecialchars($row['waste_type']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] !== 'completed' && $row['status'] !== 'cancelled'): ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <select name="new_status" onchange="this.form.submit()">
                                <option value="">--Update--</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <input type="hidden" name="update_request" value="1">
                        </form>
                        <?php else: ?>
                            <span class="text-muted">Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card">
            <h3>Assigned Waste Reports</h3>
            <table>
                <tr><th>Date</th><th>Location</th><th>Issue</th><th>Status</th><th>Action</th></tr>
                <?php while ($row = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['reported_at'])) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><span class="badge badge-waste"><?= str_replace('_', ' ', $row['issue_type']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] !== 'resolved' && $row['status'] !== 'rejected'): ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                            <select name="new_status" onchange="this.form.submit()">
                                <option value="">--Update--</option>
                                <option value="assigned">Assigned</option>
                                <option value="resolved">Resolved</option>
                            </select>
                            <input type="hidden" name="update_report" value="1">
                        </form>
                        <?php else: ?>
                            <span class="text-muted">Resolved</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>