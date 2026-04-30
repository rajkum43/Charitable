<?php
/**
 * API to fetch all donations for a specific alert batch in Beti Vivah
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    $alert_number = isset($_GET['alert']) ? intval($_GET['alert']) : 0;
    
    if ($alert_number <= 0) {
        throw new Exception('Valid alert number is required');
    }
    
    // Fetch all donations for this alert batch
    $query = "SELECT 
                dt.id,
                dt.member_id as donor_member_id,
                m.full_name as donor_name,
                COALESCE(m.district, 'Unknown') as donor_district,
                COALESCE(m.block, 'Unknown') as donor_block,
                dt.donation_to_member_id,
                b.account_holder_name as recipient_name,
                dt.amount,
                dt.created_at,
                p.alert,
                dt.claim_number,
                dt.transaction_number,
                dt.status
              FROM poll p
              INNER JOIN donation_transactions dt ON p.claim_number = dt.claim_number COLLATE utf8mb4_unicode_ci
              LEFT JOIN members m ON dt.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
              LEFT JOIN beti_vivah_aavedan b ON p.claim_number = b.application_number
              WHERE p.alert = ? 
              AND p.application_type = 'Beti_Vivah'
              AND dt.application_type = 'Beti_Vivah'
              ORDER BY dt.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$alert_number]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'donations' => $donations,
        'total' => count($donations),
        'alert' => $alert_number
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_alert_betivivah_donations.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch donations',
        'message' => $e->getMessage()
    ]);
}
?>
