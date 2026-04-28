<?php
/**
 * Admin Authentication/Security Check
 * Include this file at the beginning of any protected page:
 * require_once 'includes/auth.php';
 */

// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Not logged in - redirect to login page
    header('Location: login.php');
    exit;
}

// Verify session timeout (30 minutes)
$timeout_duration = 30 * 60; // 30 minutes in seconds
if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    if ($elapsed_time > $timeout_duration) {
        // Session expired
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Optional: Add additional security checks
function verify_admin_session() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
        return false;
    }
    return true;
}

// Get admin username from session
$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin';
$admin_id = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : null;
?>
