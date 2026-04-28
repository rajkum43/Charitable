<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Username and password required']));
}

$username = trim($input['username']);
$password = trim($input['password']);

if (empty($username) || empty($password)) {
    exit(json_encode(['success' => false, 'message' => 'Username and password cannot be empty']));
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}

$stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    $stmt->close();
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Invalid username or password']));
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    $conn->close();
    exit(json_encode(['success' => false, 'message' => 'Invalid username or password']));
}

$_SESSION['admin_id'] = $user['id'];
$_SESSION['admin_username'] = $user['username'];
$_SESSION['last_activity'] = time();
$_SESSION['login_time'] = time();

$ip = $_SERVER['REMOTE_ADDR'];
$log_stmt = $conn->prepare("INSERT INTO admin_login_logs (admin_id, ip_address, login_time) VALUES (?, ?, NOW())");
if ($log_stmt) {
    $log_stmt->bind_param("is", $user['id'], $ip);
    $log_stmt->execute();
    $log_stmt->close();
}

$conn->close();

exit(json_encode([
    'success' => true,
    'message' => 'Login successful',
    'admin_username' => $user['username'],
    'redirect' => 'index.php'
]));
