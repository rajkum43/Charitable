<?php
ob_start(); // Start output buffering to catch any errors
header('Content-Type: application/json');

// Suppress error output to prevent HTML in JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Error logging function (define early)
function logError($message) {
    $logFile = '../logs/death_aavedan_errors.log';
    if (!is_dir('../logs')) {
        mkdir('../logs', 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Custom error handler to prevent HTML output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("Error [$errno]: $errstr in $errfile on line $errline");
    // Don't output error, just log it
    return true;
});

require_once '../includes/config.php';

$response = ['success' => false, 'message' => '', 'data' => []];

// Database connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    $response['message'] = 'डेटाबेस कनेक्शन विफल';
    ob_end_clean();
    echo json_encode($response);
    exit;
}

$conn->set_charset("utf8mb4");

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'केवल POST request स्वीकृत है';
    ob_end_clean();
    echo json_encode($response);
    exit;
}

// Get JSON body or form data
$input = null;

$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($content_type, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else if (strpos($content_type, 'application/x-www-form-urlencoded') !== false || empty($content_type)) {
    $input = $_POST;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
}

if (!$input) {
    http_response_code(400);
    $response['message'] = 'Invalid request format';
    ob_end_clean();
    echo json_encode($response);
    exit;
}

try {
    $member_id = isset($input['member_id']) ? trim($input['member_id']) : '';

    if (empty($member_id)) {
        http_response_code(400);
        $response['message'] = 'Member ID आवश्यक है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }

    // Query to find member by ID or Login ID
    $query = "
        SELECT 
            member_id, 
            login_id, 
            full_name, 
            mobile_number, 
            aadhar_number, 
            father_husband_name,
            date_of_birth,
            gender,
            permanent_address,
            district,
            block,
            state,
            created_at
        FROM members 
        WHERE member_id = ? OR login_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ss', $member_id, $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        $response['message'] = 'सदस्य नहीं मिला। Member ID सत्यापित करें';
        echo json_encode($response);
        exit;
    }

    $member = $result->fetch_assoc();

    // Member is valid
    $response['success'] = true;
    $response['message'] = 'सदस्य सत्यापित';
    $response['data'] = [
        'member_id' => $member['member_id'],
        'login_id' => $member['login_id'],
        'full_name' => $member['full_name'],
        'mobile_number' => $member['mobile_number'],
        'dob' => $member['date_of_birth'],
        'gender' => $member['gender'],
        'address' => $member['permanent_address'],
        'district' => $member['district'],
        'block' => $member['block'],
        'state' => $member['state']
    ];

    http_response_code(200);
    ob_end_clean();
    echo json_encode($response);
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    logError("Get Member Details Error: " . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'सर्वर त्रुटि: ' . $e->getMessage();
    ob_end_clean();
    echo json_encode($response);
    exit;
} catch (Error $e) {
    logError("Get Member Details Fatal Error: " . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'एक गंभीर त्रुटि हुई';
    ob_end_clean();
    echo json_encode($response);
    exit;
}

// Clear output buffer and send clean JSON response
ob_end_clean();
echo json_encode($response);
if (isset($conn)) {
    $conn->close();
}
?>
