<?php
/**
 * Update Member API
 * POST /admin/api/update-member.php
 * 
 * Update member information
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
    
    if (empty($memberId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'सदस्य ID आवश्यक है'
        ]);
        exit;
    }

    // Check if member exists
    $checkStmt = $pdo->prepare("SELECT member_id FROM members WHERE member_id = ?");
    $checkStmt->execute([$memberId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'सदस्य नहीं मिला'
        ]);
        exit;
    }

    // Prepare fields to update
    $updateFields = [
        'full_name',
        'father_husband_name',
        'date_of_birth',
        'mobile_number',
        'gender',
        'email',
        'occupation',
        'office_name',
        'office_address',
        'state',
        'district',
        'block',
        'permanent_address',
        'nominee_name',
        'nominee_relation',
        'nominee_mobile',
        'nominee_aadhar',
        'payment_verified',
        'status'
    ];

    $updateValues = [];
    $setSql = [];

    foreach ($updateFields as $field) {
        if (isset($input[$field])) {
            $setSql[] = "`{$field}` = ?";
            $updateValues[] = $input[$field];
        }
    }

    if (empty($setSql)) {
        echo json_encode([
            'success' => false,
            'message' => 'कोई फील्ड अपडेट के लिए उपलब्ध नहीं है'
        ]);
        exit;
    }

    // Add member_id to the end for WHERE clause
    $updateValues[] = $memberId;

    // Build and execute update query
    $query = "UPDATE members SET " . implode(", ", $setSql) . " WHERE member_id = ?";
    
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute($updateValues)) {
        echo json_encode([
            'success' => true,
            'message' => 'सदस्य विवरण सफलतापूर्वक अपडेट किया गया'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'अपडेट विफल'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    
    // Log error
    error_log('Update Member Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'त्रुटि: ' . $e->getMessage()
    ]);
}
?>
