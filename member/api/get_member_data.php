<?php
// Member: Get Member Data
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

    $memberId = $_SESSION['member_id'];

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // Fetch member data
    $stmt = $conn->prepare("
        SELECT 
            member_id, login_id, full_name, aadhar_number, 
            father_husband_name, date_of_birth, mobile_number, 
            gender, occupation, office_name, office_address, 
            state, district, block, permanent_address, email, 
            utr_number, payment_verified, status, created_at,
            poll_option, nominee_name
        FROM members 
        WHERE member_id = ?
    ");

    $stmt->bind_param("s", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('सदस्य डेटा नहीं मिला');
    }

    $memberData = $result->fetch_assoc();
    $stmt->close();

    // Format date fields
    $memberData['date_of_birth'] = date('d/m/Y', strtotime($memberData['date_of_birth']));
    $memberData['created_at'] = date('d/m/Y H:i', strtotime($memberData['created_at']));
    $memberData['aadhar_masked'] = substr($memberData['aadhar_number'], -4) ? '****-****-' . substr($memberData['aadhar_number'], -4) : '****-****-****';
    $memberData['payment_status'] = $memberData['payment_verified'] == 1 ? 'सत्यापित' : 'लंबित';
    $memberData['membership_status'] = $memberData['status'] == 1 ? 'सक्रिय' : 'निष्क्रिय';

    $response['success'] = true;
    $response['message'] = 'डेटा सफलतापूर्वक लोड हुआ';
    $response['data'] = $memberData;

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
