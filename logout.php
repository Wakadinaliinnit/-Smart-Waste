<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    $name = $_SESSION['full_name'] ?? 'User';
    $role = $_SESSION['role'] ?? 'unknown';
    
    // Log the logout
    logActivity($conn, $_SESSION['user_id'], 'logout', "User $name ($role) logged out");
    
    // Store logout message
    $_SESSION['logout_message'] = "👋 Goodbye, $name! You have been logged out.";
}

// Clear all session data
$_SESSION = array();
session_destroy();
session_write_close();

// Redirect to login page with message
header("Location: login.php");
exit();
?>