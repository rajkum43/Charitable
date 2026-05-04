<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php';

try {
    session_start();
    $member_id = $_SESSION['member_id'] ?? null;

    if (!$member_id) {
        echo json_encode([
            'success' => false,
            'message' => 'कृपया लॉगिन करें'
        ]);
        exit;
    }

    // Get member data
    $stmt = $pdo->prepare("
        SELECT *,
               CASE WHEN status = 1 THEN 'सक्रिय' ELSE 'निष्क्रिय' END as membership_status,
               CASE WHEN payment_verified = 1 THEN 'सत्यापित' ELSE 'लंबित' END as payment_status,
               CONCAT('XXXX-XXXX-', RIGHT(aadhar_number, 4)) as aadhar_masked
        FROM members
        WHERE member_id = ?
    ");

    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        echo json_encode([
            'success' => false,
            'message' => 'सदस्य नहीं मिला'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $member
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>