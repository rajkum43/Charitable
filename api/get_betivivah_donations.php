<?php
/**
 * API to fetch all donations received by a member for Beti Vivah
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    $member_id = isset($_GET['member_id']) ? htmlspecialchars($_GET['member_id']) : null;
    
    if (!$member_id) {
        throw new Exception('Member ID is required');
    }
    
    // Fetch all verified donations to this member for Beti Vivah
    $query = "SELECT 
                dt.id,
                dt.member_id as donor_member_id,
                m.full_name as donor_name,
                COALESCE(m.district, 'Unknown') as donor_district,
                COALESCE(m.block, 'Unknown') as donor_block,
                dt.donation_to_member_id,
                b.account_holder_name as recipient_name,
                dt.amount,
                dt.created_at
              FROM donation_transactions dt
              LEFT JOIN members m ON dt.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
              LEFT JOIN beti_vivah_aavedan b ON dt.donation_to_member_id COLLATE utf8mb4_unicode_ci = b.member_id COLLATE utf8mb4_unicode_ci
              WHERE dt.donation_to_member_id = ? 
              AND dt.application_type = 'Beti_Vivah'
              ORDER BY dt.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$member_id]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'donations' => $donations,
        'total' => count($donations)
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_betivivah_donations.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch donations',
        'message' => $e->getMessage()
    ]);
}
?>
