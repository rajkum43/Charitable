<?php
/**
 * API: Get Death Sahyog Donations by Alert
 * Returns JSON array of all donations for a specific alert (publish batch)
 */

header('Content-Type: application/json');

require_once '../includes/config.php';

try {
    // Get alert number from query parameter
    $alert_number = isset($_GET['alert']) ? intval($_GET['alert']) : 0;
    
    if ($alert_number <= 0) {
        throw new Exception('Valid alert number is required');
    }
    
    // Fetch donations for this alert
    $query = "SELECT 
                dt.id,
                dt.member_id as donor_member_id,
                m.full_name as donor_name,
                COALESCE(m.district, 'Unknown') as donor_district,
                COALESCE(m.block, 'Unknown') as donor_block,
                dt.donation_to_member_id,
                COALESCE(d.full_name, dt.claim_number) as recipient_name,
                dt.amount,
                dt.created_at,
                p.alert,
                dt.claim_number,
                dt.transaction_number,
                dt.status
              FROM poll p
              INNER JOIN donation_transactions dt ON p.claim_number = dt.claim_number COLLATE utf8mb4_unicode_ci
              LEFT JOIN members m ON dt.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
              LEFT JOIN death_claims d ON dt.claim_number COLLATE utf8mb4_unicode_ci = d.claim_id COLLATE utf8mb4_unicode_ci
              WHERE p.alert = ? 
              AND p.application_type = 'Death_Claims'
              AND dt.application_type = 'Death_Claims'
              ORDER BY dt.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$alert_number]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response
    echo json_encode([
        'success' => true,
        'donations' => $donations,
        'total' => count($donations),
        'alert' => $alert_number
    ]);
    
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
}
?>