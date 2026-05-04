<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php';

try {
    // Query to get referral counts
    $stmt = $pdo->prepare("
        SELECT referrer_member_id, COUNT(*) as referral_count
        FROM members
        WHERE referrer_member_id IS NOT NULL AND referrer_member_id != ''
        GROUP BY referrer_member_id
        ORDER BY referral_count DESC
    ");
    
    $stmt->execute();
    $referralCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $referralCounts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>