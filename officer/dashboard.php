<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('officer');

$loginMessage = '';
if (isset($_SESSION['login_message'])) {
    $loginMessage = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

$totalRequests = $conn->query("SELECT COUNT(*) c FROM collection_requests")->fetch_assoc()['c'];
$completedRequests = $conn->query("SELECT COUNT(*) c FROM collection_requests WHERE status='completed'")->fetch_assoc()['c'];
$totalReports = $conn->query("SELECT COUNT(*) c FROM waste_reports")->fetch_assoc()['c'];
$resolvedReports = $conn->query("SELECT COUNT(*) c FROM waste_reports WHERE status='resolved'")->fetch_assoc()['c'];

$completionRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 1) : 0;
$resolutionRate = $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100, 1) : 0;

$zoneStats = $conn->query(" 
    SELECT zone, COUNT(*) AS total,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed
    FROM collection_requests
    GROUP BY zone
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipal Officer Dashboard - Smart Waste</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'dashboard.html'; ?>
</body>
</html>