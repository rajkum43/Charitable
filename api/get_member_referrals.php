<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php';

try {
    // Get member_id from session
    session_start();
    $member_id = $_SESSION['member_id'] ?? null;

    if (!$member_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Not logged in'
        ]);
        exit;
    }

    // Compare using last 7 digits, because referrer_member_id stores the final 7 digits of member_id
    $referrer_member_id = substr($member_id, -7);

    // Query to get referrals for this member
    $stmt = $pdo->prepare("
        SELECT member_id, full_name, father_husband_name, mobile_number, permanent_address, created_at
        FROM members
        WHERE referrer_member_id = ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$referrer_member_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $referrals
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>