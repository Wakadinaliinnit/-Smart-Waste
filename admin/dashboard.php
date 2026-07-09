<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

// Show login message
$loginMessage = '';
if (isset($_SESSION['login_message'])) {
    $loginMessage = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) c FROM users WHERE status='active'")->fetch_assoc()['c'];
$totalRequests = $conn->query("SELECT COUNT(*) c FROM collection_requests")->fetch_assoc()['c'];
$pendingRequests = $conn->query("SELECT COUNT(*) c FROM collection_requests WHERE status='pending'")->fetch_assoc()['c'];
$openReports = $conn->query("SELECT COUNT(*) c FROM waste_reports WHERE status='open'")->fetch_assoc()['c'];

// Get recent activity
$recentActivity = $conn->query("
    SELECT al.*, u.full_name 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.user_id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Waste</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-banner {
            background: #e6f4ea;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .role-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
        }
        .role-admin { background: #c62828; color: white; }
        .role-collector { background: #1565c0; color: white; }
        .role-officer { background: #6a1b9a; color: white; }
        .role-resident { background: #2e7d32; color: white; }
        .user-info-card {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .user-info-card .name {
            font-size: 18px;
            font-weight: bold;
        }
        .user-info-card .details {
            color: #666;
            font-size: 14px;
        }
        .user-info-card .time {
            color: #999;
            font-size: 13px;
        }
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            padding: 25px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s;
        }
        .stat-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .stat-box .number {
            font-size: 36px;
            font-weight: bold;
            color: #2e7d32;
        }
        .stat-box .label {
            color: #666;
            margin-top: 5px;
            font-size: 14px;
        }
        .stat-box .trend {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0 30px 0;
        }
        .quick-action-btn {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            background: #2e7d32;
            color: white;
            transform: translateY(-2px);
        }
        .quick-action-btn .icon {
            font-size: 28px;
            display: block;
            margin-bottom: 8px;
        }
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-user {
            font-weight: bold;
            color: #2e7d32;
        }
        .activity-time {
            color: #999;
            font-size: 12px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="brand">♻️ Smart Waste - Admin</div>
        <div>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="requests.php">Requests</a>
            <a href="reports.php">Reports</a>
            <a href="schedules.php">Schedules</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php displayFlashMessages(); ?>
        
        <!-- Login Success Banner -->
        <?php if ($loginMessage): ?>
        <div class="login-banner">
            <div>
                <span>✅</span> 
                <strong><?= $loginMessage ?></strong>
                <span class="role-badge role-admin">Admin</span>
            </div>
            <div style="font-size:14px; color:#555;">
                🕐 Logged in: <?= date('h:i A', strtotime($_SESSION['login_time'] ?? 'now')) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- User Info Card -->
        <div class="user-info-card">
            <div>
                <span class="name">👋 <?= htmlspecialchars($_SESSION['full_name']) ?></span>
                <span class="role-badge role-admin">Admin</span>
            </div>
            <div>
                <span class="details">Administrator account</span>
                <span class="time" style="margin-left:15px;">🕐 Logged in: <?= date('h:i A') ?></span>
            </div>
        </div>
        
        <h2>📊 Admin Dashboard</h2>
        <p style="color:#666; margin-bottom:25px;">Welcome back! Here's your system overview.</p>

        <!-- Statistics -->
        <div class="dashboard-stats">
            <div class="stat-box">
                <div class="number"><?= number_format($totalUsers) ?></div>
                <div class="label">Active Users</div>
                <div class="trend">⬆️ 12% this month</div>
            </div>
            <div class="stat-box">
                <div class="number"><?= number_format($totalRequests) ?></div>
                <div class="label">Collection Requests</div>
                <div class="trend"><?= $pendingRequests ?> pending</div>
            </div>
            <div class="stat-box">
                <div class="number"><?= number_format($pendingRequests) ?></div>
                <div class="label">Pending Requests</div>
                <div class="trend">⚠️ Needs attention</div>
            </div>
            <div class="stat-box">
                <div class="number"><?= number_format($openReports) ?></div>
                <div class="label">Open Reports</div>
                <div class="trend"><?= $openReports > 5 ? '🔴 High' : '🟢 Normal' ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="users.php" class="quick-action-btn">
                <span class="icon">👤</span>
                Manage Users
            </a>
            <a href="requests.php" class="quick-action-btn">
                <span class="icon">📋</span>
                Assign Requests
            </a>
            <a href="reports.php" class="quick-action-btn">
                <span class="icon">⚠️</span>
                View Reports
            </a>
            <a href="schedules.php" class="quick-action-btn">
                <span class="icon">📅</span>
                Create Schedule
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <h3>📝 Recent Activity</h3>
            <?php if ($recentActivity->num_rows === 0): ?>
                <p style="color:#999; text-align:center; padding:20px;">No recent activity found.</p>
            <?php else: ?>
                <?php while ($row = $recentActivity->fetch_assoc()): ?>
                <div class="activity-item">
                    <div>
                        <span class="activity-user"><?= htmlspecialchars($row['full_name'] ?? 'System') ?></span>
                        <span style="color:#555;"><?= htmlspecialchars($row['action']) ?></span>
                        <?php if ($row['details']): ?>
                            <span style="color:#888; font-size:13px;">- <?= htmlspecialchars($row['details']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="activity-time"><?= timeAgo($row['created_at']) ?></span>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return floor($diff/604800) . 'w ago';
}
?>