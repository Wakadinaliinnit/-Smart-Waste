<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('resident');

$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = clean($conn, $_POST['location']);
    $zone = clean($conn, $_POST['zone']);
    $issue_type = clean($conn, $_POST['issue_type']);
    $description = clean($conn, $_POST['description']);

    $stmt = $conn->prepare("INSERT INTO waste_reports (resident_id, location, zone, issue_type, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $uid, $location, $zone, $issue_type, $description);
    if ($stmt->execute()) {
        logActivity($conn, $uid, 'report_issue', "Reported $issue_type at $location");
        setFlash('success', 'Issue reported successfully. The waste management team has been notified.');
        $stmt->close();
        header("Location: report.php");
        exit();
    }
    $stmt->close();
}

$reports = $conn->query("SELECT * FROM waste_reports WHERE resident_id=$uid ORDER BY reported_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Waste Issue</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="request.php">New Request</a>
            <a href="report.php" class="active">Report Issue</a>
            <a href="schedule.php">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>
        <div class="card" style="max-width:500px; margin:0 auto;">
            <h2>Report Full/Uncollected Bin</h2>
            <form method="POST">
                <label>Location *</label>
                <input type="text" name="location" required>
                <label>Zone</label>
                <input type="text" name="zone">
                <label>Issue Type</label>
                <select name="issue_type">
                    <option value="overflowing_bin">Overflowing Bin</option>
                    <option value="uncollected_waste">Uncollected Waste</option>
                    <option value="illegal_dumping">Illegal Dumping</option>
                    <option value="other">Other</option>
                </select>
                <label>Description *</label>
                <textarea name="description" rows="3" required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>

        <div class="card">
            <h3>My Reports</h3>
            <table>
                <tr><th>Date</th><th>Location</th><th>Issue</th><th>Status</th></tr>
                <?php while ($row = $reports->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['reported_at'])) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><span class="badge badge-waste"><?= str_replace('_', ' ', $row['issue_type']) ?></span></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>