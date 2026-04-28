<?php
/**
 * Search Member API
 * POST /admin/api/search-member.php
 * 
 * Search for member by member_id or mobile_number
 */

header('Content-Type: application/json; charset=UTF-8');

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'प्रमाणीकरण आवश्यक है'
    ]);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';

try {
    global $pdo;
    
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    $memberId = isset($input['member_id']) ? trim($input['member_id']) : '';
    $mobileNumber = isset($input['mobile_number']) ? trim($input['mobile_number']) : '';

    if (empty($memberId) && empty($mobileNumber)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'सदस्य ID या मोबाइल नंबर दर्ज करें'
        ]);
        exit;
    }

    // Build query
    $query = "SELECT * FROM members WHERE 1=1";
    $params = [];

    if (!empty($memberId)) {
        $query .= " AND member_id = ?";
        $params[] = $memberId;
    }

    if (!empty($mobileNumber)) {
        $query .= " AND mobile_number = ?";
        $params[] = $mobileNumber;
    }

    $query .= " LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        echo json_encode([
            'success' => true,
            'message' => 'सदस्य मिल गया',
            'member' => $member
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'सदस्य नहीं मिला'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'त्रुटि: ' . $e->getMessage()
    ]);
}
?>
