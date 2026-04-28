<?php
// Admin: Get Member Details
header('Content-Type: application/json');
session_start();

require_once '../../../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        $response['message'] = 'अनुमति प्राप्त नहीं';
        echo json_encode($response);
        exit;
    }

    $member_id = isset($_GET['member_id']) ? trim($_GET['member_id']) : '';

    if (empty($member_id)) {
        http_response_code(400);
        $response['message'] = 'सदस्य ID आवश्यक है';
        echo json_encode($response);
        exit;
    }

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // Get member details
    $stmt = $conn->prepare("
        SELECT m.*, 
               (SELECT receipt_file_name FROM payment_receipts WHERE member_id = ? LIMIT 1) as receipt_file
        FROM members m
        WHERE m.member_id = ?
    ");
    
    $stmt->bind_param('ss', $member_id, $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('सदस्य नहीं मिला');
    }

    $member = $result->fetch_assoc();
    $stmt->close();

    // Format dates
    $member['date_of_birth'] = date('d/m/Y', strtotime($member['date_of_birth']));
    $member['created_at'] = date('d/m/Y H:i', strtotime($member['created_at']));
    $member['aadhar_masked'] = '****-****-' . substr($member['aadhar_number'], -4);

    $response['success'] = true;
    $response['message'] = 'डेटा सफलतापूर्वक लोड हुआ';
    $response['data'] = $member;

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
