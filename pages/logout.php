<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookies if set
setcookie('member_remember', '', time() - 3600, '/');
setcookie('member_id', '', time() - 3600, '/');

// Detect base URL dynamically
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname(dirname($script_name)); // Remove /pages from path

if ($host === 'localhost') {
    $protocol = 'http';
    $base_url = $protocol . '://' . $host . $base_path;
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base_url = $protocol . '://' . $host . $base_path;
}

$login_url = $base_url . '/pages/login.php?message=' . urlencode('आप सफलतापूर्वक लॉगआउट हो गए हैं') . '&type=success';

// Redirect to login page with success message
header('Location: ' . $login_url);
exit;
?>
