<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $rid = (int)$_POST['report_id'];
    $cid = (int)$_POST['collector_id'];
    
    if ($cid <= 0) {
        setFlash('error', 'Please select a collector.');
    } else {
        $stmt = $conn->prepare("UPDATE waste_reports SET assigned_collector_id=?, status='assigned' WHERE report_id=?");
        $stmt->bind_param("ii", $cid, $rid);
        if ($stmt->execute()) {
            $resInfo = $conn->query("SELECT resident_id, location FROM waste_reports WHERE report_id=$rid")->fetch_assoc();
            if ($resInfo) {
                sendNotification($conn, $resInfo['resident_id'], "Issue Assigned", 
                    "Your reported issue at {$resInfo['location']} has been assigned for resolution.");
            }
            logActivity($conn, $_SESSION['user_id'], 'assign_report', "Report #$rid -> Collector #$cid");
            setFlash('success', 'Collector assigned to report successfully.');
        } else {
            setFlash('error', 'Failed to assign collector.');
        }
        $stmt->close();
    }
    header("Location: reports.php");
    exit();
}

$collectors = $conn->query("SELECT user_id, full_name FROM users WHERE role='collector' AND status='active'");
$collectorList = [];
while ($c = $collectors->fetch_assoc()) $collectorList[] = $c;

$reports = $conn->query("SELECT wr.*, u.full_name AS resident_name FROM waste_reports wr JOIN users u ON wr.resident_id=u.user_id ORDER BY wr.reported_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Waste Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste - Admin</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="requests.php">Requests</a>
            <a href="reports.php" class="active">Reports</a>
            <a href="schedules.php">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>
        
        <div class="card">
            <h2>Reported Issues</h2>
            <table>
                <tr><th>Date</th><th>Resident</th><th>Location</th><th>Issue</th><th>Status</th><th>Assign Collector</th></tr>
                <?php while ($row = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['reported_at'])) ?></td>
                    <td><?= htmlspecialchars($row['resident_name']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><span class="badge badge-waste"><?= str_replace('_', ' ', $row['issue_type']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'open'): ?>
                        <form method="POST" style="margin:0; display:flex; gap:6px;">
                            <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                            <select name="collector_id" required>
                                <option value="">--Select Collector--</option>
                                <?php foreach ($collectorList as $c): ?>
                                <option value="<?= $c['user_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign" value="1" class="btn btn-sm">Assign</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">Assigned</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>