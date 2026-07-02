<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('resident');

$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = clean($conn, $_POST['address']);
    $zone = clean($conn, $_POST['zone']);
    $waste_type = clean($conn, $_POST['waste_type']);
    $notes = clean($conn, $_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO collection_requests (resident_id, address, zone, waste_type, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $uid, $address, $zone, $waste_type, $notes);
    if ($stmt->execute()) {
        logActivity($conn, $uid, 'submit_request', "Requested collection at $address");
        setFlash('success', 'Your waste collection request has been submitted.');
        $stmt->close();
        header("Location: request.php");
        exit();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Collection Request</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="request.php" class="active">New Request</a>
            <a href="report.php">Report Issue</a>
            <a href="schedule.php">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <?php displayFlashMessages(); ?>
        <div class="card" style="max-width:500px; margin:0 auto;">
            <h2>Submit Waste Collection Request</h2>
            <form method="POST">
                <label>Address *</label>
                <input type="text" name="address" required>
                <label>Zone</label>
                <input type="text" name="zone" placeholder="e.g. Zone A">
                <label>Waste Type</label>
                <select name="waste_type">
                    <option value="general">General</option>
                    <option value="recyclable">Recyclable</option>
                    <option value="organic">Organic</option>
                    <option value="hazardous">Hazardous</option>
                </select>
                <label>Additional Notes</label>
                <textarea name="notes" rows="3"></textarea>
                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>