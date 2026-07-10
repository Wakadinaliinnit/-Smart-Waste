<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $rid = (int)$_POST['request_id'];
    $cid = (int)$_POST['collector_id'];
    
    if ($cid <= 0) {
        setFlash('error', 'Please select a collector.');
    } else {
        $stmt = $conn->prepare("UPDATE collection_requests SET assigned_collector_id=?, status='assigned' WHERE request_id=?");
        $stmt->bind_param("ii", $cid, $rid);
        if ($stmt->execute()) {
            $resInfo = $conn->query("SELECT resident_id, address FROM collection_requests WHERE request_id=$rid")->fetch_assoc();
            if ($resInfo) {
                sendNotification($conn, $resInfo['resident_id'], "Collection Scheduled", 
                    "Your request for {$resInfo['address']} has been assigned to a collector.");
            }
            logActivity($conn, $_SESSION['user_id'], 'assign_request', "Request #$rid -> Collector #$cid");
            setFlash('success', 'Collector assigned successfully.');
        } else {
            setFlash('error', 'Failed to assign collector.');
        }
        $stmt->close();
    }
    header("Location: requests.php");
    exit();
}

$collectors = $conn->query("SELECT user_id, full_name FROM users WHERE role='collector' AND status='active'");
$collectorList = [];
while ($c = $collectors->fetch_assoc()) $collectorList[] = $c;

$requests = $conn->query("SELECT cr.*, u.full_name AS resident_name FROM collection_requests cr JOIN users u ON cr.resident_id=u.user_id ORDER BY cr.requested_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Collection Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste - Admin</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="requests.php" class="active">Requests</a>
            <a href="reports.php">Reports</a>
            <a href="schedules.php">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>
        
        <div class="card">
            <h2>Collection Requests</h2>
            <table>
                <tr><th>Date</th><th>Resident</th><th>Address</th><th>Waste Type</th><th>Price (KES)</th><th>Status</th><th>Action</th></tr>
                <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['requested_at'])) ?></td>
                    <td><?= htmlspecialchars($row['resident_name']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <?php $wasteTypeLabel = trim((string)($row['waste_type'] ?? '')); ?>
                    <td><span class="badge badge-waste"><?= htmlspecialchars($wasteTypeLabel !== '' ? ucfirst(str_replace('_', ' ', $wasteTypeLabel)) : 'General') ?></span></td>
                    <td><?= number_format((float)($row['estimated_price'] ?? 0), 2) ?></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" style="margin:0; display:flex; gap:6px;">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
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