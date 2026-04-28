<?php
// API for validating deceased member details
ob_start(); // Start output buffering to catch any errors
header('Content-Type: application/json');
session_start();

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
require_once '../config/membership_requirements.php';

// Load membership requirements
$membership_requirements = require '../config/membership_requirements.php';
$min_membership_days = $membership_requirements['death_aavedan'] ?? 365;

// Initialize response
$response = [
    'valid' => false,
    'message' => '',
    'data' => []
];

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'valid' => false,
        'message' => 'डेटाबेस कनेक्शन विफल'
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'केवल POST request स्वीकृत है';
    ob_end_clean();
    echo json_encode($response);
    exit;
}

try {
    // Get data from POST request
    $data = json_decode(file_get_contents('php://input'), true);
    
    $deceased_member_id = $data['deceased_member_id'] ?? '';
    $deceased_dob = $data['deceased_dob'] ?? '';
    $death_date = $data['death_date'] ?? '';
    $deceased_age = (int)($data['deceased_age'] ?? 0);
    
    // Validate required fields
    if (empty($deceased_member_id) || empty($deceased_dob) || empty($death_date)) {
        http_response_code(400);
        $response['message'] = 'सभी आवश्यक फील्ड भरें';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // Validate age
    if ($deceased_age < 18 || $deceased_age > 60) {
        http_response_code(400);
        $response['message'] = 'मृत्यु के समय आयु 18 से 60 वर्ष के बीच होनी चाहिए';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // Validate death date (must be in past or today)
    try {
        $death_date_obj = new DateTime($death_date);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($death_date_obj > $today) {
            http_response_code(400);
            $response['message'] = 'मृत्यु की तिथि भविष्य की नहीं हो सकती';
            echo json_encode($response);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        $response['message'] = 'मृत्यु की तिथि सही प्रारूप में नहीं है';
        echo json_encode($response);
        exit;
    }
    
    // Check if deceased member exists in members table
    $check_stmt = $conn->prepare("SELECT member_id, full_name, date_of_birth, created_at FROM members WHERE member_id = ?");
    $check_stmt->bind_param("s", $deceased_member_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        $response['message'] = 'मृत व्यक्ति का सदस्य ID डेटाबेस में नहीं मिला';        ob_end_clean();        echo json_encode($response);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    
    $member = $result->fetch_assoc();
    $check_stmt->close();
    
    // Validate that DOB matches
    if ($member['date_of_birth'] !== $deceased_dob) {
        http_response_code(400);
        $response['message'] = 'दर्ज की गई जन्म तिथि डेटाबेस में दर्ज जन्म तिथि से मेल नहीं खाती';        ob_end_clean();        echo json_encode($response);
        $conn->close();
        exit;
    }
    
    // Check if member was registered at least 1 year ago
    $created_at = new DateTime($member['created_at']);
    $today = new DateTime();
    $interval = $created_at->diff($today);
    $years = $interval->y;
    $months = $interval->m;
    $days = $interval->d;
    $total_days = ($years * 365) + ($months * 30) + $days;
    
    if ($total_days < $min_membership_days) {
        $required_years = ceil($min_membership_days / 365);
        http_response_code(400);
        $response['message'] = 'मृत व्यक्ति कम से कम ' . $required_years . ' वर्ष का सदस्य होना चाहिए। वर्तमान में सदस्यता ' . $years . ' वर्ष, ' . $months . ' महीने और ' . $days . ' दिन पुरानी है।';
        ob_end_clean();
        echo json_encode($response);
        $conn->close();
        exit;
    }
    
    // Check if duplicate application exists for this deceased member
    $check_duplicate_stmt = $conn->prepare("SELECT id, application_number, status FROM death_aavedan WHERE deceased_member_id = ? AND status IN ('Pending', 'Under Review') ORDER BY created_at DESC LIMIT 1");
    $check_duplicate_stmt->bind_param("s", $deceased_member_id);
    $check_duplicate_stmt->execute();
    $duplicate_result = $check_duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        $existing_app = $duplicate_result->fetch_assoc();
        http_response_code(400);
        $response['message'] = 'इस मृत व्यक्ति के लिए पहले से एक आवेदन (' . $existing_app['application_number'] . ') ' . $existing_app['status'] . ' स्थिति में है।';
        ob_end_clean();
        echo json_encode($response);
        $check_duplicate_stmt->close();
        $conn->close();
        exit;
    }
    $check_duplicate_stmt->close();
    
    // All validations passed
    $response['valid'] = true;
    $response['message'] = 'मृत व्यक्ति का विवरण सत्यापित हो गया';
    $response['data'] = [
        'member_name' => $member['full_name'],
        'member_id' => $member['member_id'],
        'dob' => $member['date_of_birth'],
        'membership_years' => $years,
        'membership_months' => $months,
        'membership_days' => $days
    ];
    
    http_response_code(200);
    
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'एक त्रुटि हुई: ' . $e->getMessage();
} catch (Error $e) {
    http_response_code(500);
    $response['message'] = 'एक गंभीर त्रुटि हुई';
}

// Clear output buffer and send clean JSON response
ob_end_clean();
echo json_encode($response);
exit;
