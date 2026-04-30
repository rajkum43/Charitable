<?php
/**
 * Poll Management API
 * Handles search, update, and delete operations for polls
 */

// Suppress error display, use JSON error responses instead
error_reporting(E_ALL);
ini_set('display_errors', 0);

// IMPORTANT: Set content type and prevent any output before JSON
// This must be the very first thing in the file!
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Prevent any output before JSON
ob_start();

// Now include the auth and config files
if (!file_exists('../includes/auth.php')) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Auth file not found']);
    exit;
}

require_once '../includes/auth.php';

if (!file_exists('../../includes/config.php')) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Config file not found']);
    exit;
}

require_once '../../includes/config.php';

// Clear any buffered output
ob_end_clean();

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if action is provided
if (!isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action not specified']);
    exit;
}

$action = $_POST['action'];

/**
 * Search polls by claim number
 */
if ($action === 'search') {
    if (!isset($_POST['claim_number']) || empty($_POST['claim_number'])) {
        echo json_encode(['success' => false, 'message' => 'Claim number is required']);
        exit;
    }

    $claim_number = $conn->real_escape_string($_POST['claim_number']);
    
    $polls = [];
    $result = $conn->query("SELECT * FROM poll WHERE claim_number LIKE '%$claim_number%' ORDER BY created_at DESC");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $polls[] = $row;
        }
        echo json_encode([
            'success' => true,
            'polls' => $polls,
            'count' => count($polls)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    }
    exit;
}

/**
 * Update poll dates
 */
if ($action === 'update') {
    if (!isset($_POST['id']) || !isset($_POST['start_poll_date']) || !isset($_POST['expire_poll_date'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $id = intval($_POST['id']);
    $start_poll_date = $conn->real_escape_string($_POST['start_poll_date']);
    $expire_poll_date = $conn->real_escape_string($_POST['expire_poll_date']);
    
    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_poll_date) || 
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expire_poll_date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
        exit;
    }

    // Validate dates
    $startDateTime = strtotime($start_poll_date);
    $expireDateTime = strtotime($expire_poll_date);

    if ($startDateTime === false || $expireDateTime === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date values']);
        exit;
    }

    if ($expireDateTime < $startDateTime) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Expiry date must be after start date']);
        exit;
    }

    $sql = "UPDATE poll SET start_poll_date='$start_poll_date', expire_poll_date='$expire_poll_date', updated_at=NOW() WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Poll dates updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

/**
 * Delete poll
 */
if ($action === 'delete') {
    if (!isset($_POST['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Poll ID is required']);
        exit;
    }

    $id = intval($_POST['id']);

    // Get poll details for logging (optional)
    $check = $conn->query("SELECT * FROM poll WHERE id = $id");
    if (!$check || $check->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Poll record not found']);
        exit;
    }

    $sql = "DELETE FROM poll WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Poll deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

/**
 * Invalid action
 */
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
$conn->close();
?>
