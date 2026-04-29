<?php
/**
 * Get Death Claims Donations Received by Member
 * Returns all donations received by a member for death claims
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['member_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'member_id parameter required'
    ]);
    exit;
}

$member_id = htmlspecialchars($_GET['member_id']);

try {
    $query = "SELECT 
        dt.id,
        dt.member_id as donor_member_id,
        m.full_name as donor_name,
        COALESCE(m.district, 'Unknown') as donor_district,
        COALESCE(m.block, 'Unknown') as donor_block,
        dt.donation_to_member_id,
        d.nominee_name as recipient_name,
        dt.amount,
        dt.created_at
    FROM donation_transactions dt
    LEFT JOIN members m ON dt.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
    LEFT JOIN death_claims d ON dt.donation_to_member_id COLLATE utf8mb4_unicode_ci = d.member_id COLLATE utf8mb4_unicode_ci
    WHERE dt.donation_to_member_id = ? 
    AND dt.application_type = 'Death_Claims'
    ORDER BY dt.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$member_id]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'donations' => $donations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
