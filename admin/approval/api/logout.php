<?php
session_start();
header('Content-Type: application/json');

// Clear session
$_SESSION = [];
session_destroy();

// Clear session cookie if exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to admin login
echo json_encode([
    'success' => true,
    'message' => 'लॉगआउट सफल',
    'redirect' => '../index.php'
]);
?>
