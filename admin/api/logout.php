<?php
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_destroy();
$_SESSION = array();

exit(json_encode([
    'success' => true,
    'message' => 'Logout successful',
    'redirect' => 'login.php'
]));
