<?php
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

// Set default response
$response = ['success' => false, 'message' => 'An error occurred'];

try {
    require_once '../includes/auth.php';
    require_once '../../includes/config.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username']) || !isset($input['password']) || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username, password, and email required']);
        exit;
    }

    $username = trim($input['username']);
    $password = trim($input['password']);
    $email = trim($input['email']);

    // Validation
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        exit;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    // Check if username already exists
    $check_stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
    if (!$check_stmt) {
        http_response_code(500);
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $check_stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }

    $check_stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    if (!$stmt) {
        http_response_code(500);
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param("sss", $username, $hashed_password, $email);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Admin user created successfully',
            'admin_id' => $new_id,
            'username' => $username
        ]);
    } else {
        $stmt->close();
        $conn->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating admin user']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
exit;
