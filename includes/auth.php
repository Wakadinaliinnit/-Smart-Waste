<?php
session_start();

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please login to access this page.'];
        header("Location: ../login.php");
        exit();
    }
}

function redirectToRoleDashboard($role = null) {
    $role = $role ?? ($_SESSION['role'] ?? null);
    switch ($role) {
        case 'resident':
            header("Location: ../resident/dashboard.php");
            break;
        case 'collector':
            header("Location: ../collector/dashboard.php");
            break;
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'officer':
            header("Location: ../officer/dashboard.php");
            break;
        default:
            header("Location: ../login.php");
            break;
    }
    exit();
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Unauthorized access. You are not allowed to open that page."];
        redirectToRoleDashboard($_SESSION['role']);
    }
}

function logActivity($conn, $userId, $action, $details = '') {
    // Skip if no connection
    if (!$conn) {
        return false;
    }
    
    $details = $details ?: '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($tableCheck->num_rows === 0) {
        return false;
    }
    
    // Simple direct query without prepared statement to avoid issues
    $userId = (int)$userId;
    $action = $conn->real_escape_string($action);
    $details = $conn->real_escape_string($details);
    $ip = $conn->real_escape_string($ip);
    $userAgent = $conn->real_escape_string($userAgent);
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
            VALUES ($userId, '$action', '$details', '$ip', '$userAgent')";
    
    return $conn->query($sql);
}

function sendNotification($conn, $userId, $title, $message, $type = 'general') {
    if (!$conn) {
        return false;
    }
    
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck->num_rows === 0) {
        return false;
    }
    
    $userId = (int)$userId;
    $title = $conn->real_escape_string($title);
    $message = $conn->real_escape_string($message);
    $type = $conn->real_escape_string($type);
    
    $sql = "INSERT INTO notifications (user_id, title, message, type) 
            VALUES ($userId, '$title', '$message', '$type')";
    
    return $conn->query($sql);
}

function clean($conn, $value) {
    if (is_null($value)) return '';
    return htmlspecialchars(trim($conn->real_escape_string($value)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function displayFlashMessages() {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        $class = $type === 'error' ? 'alert-error' : ($type === 'info' ? 'alert-info' : 'alert-success');
        echo "<div class='alert $class'>$message</div>";
    }
}
?>