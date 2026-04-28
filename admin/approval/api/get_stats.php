<?php
// Admin: Get Statistics
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

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // Get counts for each status
    $query = "SELECT 
                (SELECT COUNT(*) FROM members WHERE status = 0) as pending,
                (SELECT COUNT(*) FROM members WHERE status = 1) as approved,
                (SELECT COUNT(*) FROM members WHERE status = 2) as rejected,
                (SELECT COUNT(*) FROM members) as total";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('क्वेरी विफल: ' . $conn->error);
    }

    $stats = $result->fetch_assoc();

    $response['success'] = true;
    $response['message'] = 'सांख्यिकी सफलतापूर्वक लोड हुई';
    $response['data'] = $stats;

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
