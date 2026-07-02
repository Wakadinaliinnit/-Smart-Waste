<?phprequire_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_schedule'])) {
    $zone = clean($conn, $_POST['zone']);
    $collector_id = (int)$_POST['collector_id'];
    $date = $_POST['scheduled_date'];
    $time = $_POST['scheduled_time'];
    $admin_id = $_SESSION['user_id'];

    if (empty($zone) || empty($date)) {
        setFlash('error', 'Please fill in all required fields.');
    } else {
        $stmt = $conn->prepare("INSERT INTO collection_schedules (zone, collector_id, scheduled_date, scheduled_time, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sissi", $zone, $collector_id, $date, $time, $admin_id);
        if ($stmt->execute()) {
            setFlash('success', 'Schedule created successfully.');
            logActivity($conn, $admin_id, 'create_schedule', "Zone $zone on $date");
        } else {
            setFlash('error', 'Failed to create schedule.');
        }
        $stmt->close();
    }
    header("Location: schedules.php");
    exit();
}

$collectors = $conn->query("SELECT user_id, full_name FROM users WHERE role='collector' AND status='active'");
$collectorList = [];
while ($c = $collectors->fetch_assoc()) $collectorList[] = $c;

$schedules = $conn->query("SELECT cs.*, u.full_name FROM collection_schedules cs LEFT JOIN users u ON cs.collector_id=u.user_id ORDER BY scheduled_date DESC");
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
        <div class="brand">♻️ Smart Waste - Admin</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="requests.php">Requests</a>
            <a href="reports.php">Reports</a>
            <a href="schedules.php" class="active">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>
        
        <div class="card">
            <h2>Create Collection Schedule</h2>
            <form method="POST">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div><label>Zone *</label><input type="text" name="zone" required placeholder="e.g. Zone A"></div>
                    <div>
                        <label>Assign Collector</label>
                        <select name="collector_id">
                            <option value="">--Select Collector--</option>
                            <?php foreach ($collectorList as $c): ?>
                            <option value="<?= $c['user_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label>Date *</label><input type="date" name="scheduled_date" required></div>
                    <div><label>Time</label><input type="time" name="scheduled_time"></div>
                </div>
                <button type="submit" name="create_schedule" value="1" style="margin-top:16px;">Create Schedule</button>
            </form>
        </div>

        <div class="card">
            <h2>All Schedules</h2>
            <table>
                <tr><th>Zone</th><th>Collector</th><th>Date</th><th>Time</th><th>Status</th></tr>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['zone']) ?></td>
                    <td><?= htmlspecialchars($row['full_name'] ?: 'Unassigned') ?></td>
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