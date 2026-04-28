<?php
// Member: Update Member Profile
header('Content-Type: application/json');
session_start();

require_once '../../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['member_id'])) {
        http_response_code(401);
        $response['message'] = 'कृपया लॉगिन करें';
        echo json_encode($response);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response['message'] = 'केवल POST request स्वीकृत है';
        echo json_encode($response);
        exit;
    }

    $memberId = $_SESSION['member_id'];
    
    // Get form data
    $field = isset($_POST['field']) ? trim($_POST['field']) : '';
    $value = isset($_POST['value']) ? trim($_POST['value']) : '';

    // Validate field name
    $allowedFields = ['email', 'mobile_number', 'office_name', 'office_address', 'permanent_address'];
    
    if (!in_array($field, $allowedFields)) {
        http_response_code(400);
        $response['message'] = 'अमान्य फ़ील्ड';
        echo json_encode($response);
        exit;
    }

    // Validation based on field
    if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        $response['message'] = 'वैध ईमेल पता दर्ज करें';
        echo json_encode($response);
        exit;
    }

    if ($field === 'mobile_number' && !preg_match('/^[6-9]\d{9}$/', preg_replace('/\s+/', '', $value))) {
        http_response_code(400);
        $response['message'] = 'वैध मोबाइल नंबर दर्ज करें';
        echo json_encode($response);
        exit;
    }

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // Update field
    $stmt = $conn->prepare("UPDATE members SET $field = ?, updated_at = NOW() WHERE member_id = ?");
    
    $stmt->bind_param("ss", $value, $memberId);
    
    if (!$stmt->execute()) {
        throw new Exception('अपडेट विफल: ' . $stmt->error);
    }

    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'प्रोफाइल सफलतापूर्वक अपडेट हुई';

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
