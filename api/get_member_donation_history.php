<?php
/**
 * Get Member Donation History
 * Fetches all donations made by the logged-in member
 */
session_start();
header('Content-Type: application/json');

// Add CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../includes/config.php';

$member_id = $_SESSION['member_id'];

try {
    // Query to fetch all donations made by the member
    $query = "
        SELECT 
            dt.id as transaction_id,
            dt.member_id as donor_id,
            dt.donation_to_member_id,
            dt.claim_number,
            dt.application_type,
            dt.amount,
            dt.transaction_number,
            dt.status,
            dt.created_at as donation_date,
            CASE 
                WHEN dt.application_type = 'Death_Claims' THEN dc.full_name
                WHEN dt.application_type = 'Beti_Vivah' THEN bv.bride_name
            END as recipient_name,
            CASE 
                WHEN dt.application_type = 'Death_Claims' THEN dc.nominee_name
                WHEN dt.application_type = 'Beti_Vivah' THEN bv.groom_name
            END as secondary_name,
            CASE 
                WHEN dt.application_type = 'Death_Claims' THEN CONCAT(dc.full_name, ' (', dc.nominee_name, ')')
                WHEN dt.application_type = 'Beti_Vivah' THEN CONCAT(bv.bride_name, ' & ', bv.groom_name)
            END as display_name,
            CASE
                WHEN dt.application_type = 'Beti_Vivah' THEN bv.member_name
                WHEN dt.application_type = 'Death_Claims' THEN COALESCE(dcm.full_name, m.full_name)
            END as applicant_name,
            m.full_name as donor_name,
            m.block as donor_block,
            m.district as donor_district
        FROM donation_transactions dt
        LEFT JOIN members m ON dt.member_id = m.member_id
        LEFT JOIN death_claims dc ON dt.application_type = 'Death_Claims' AND dt.claim_number = CAST(dc.claim_id AS CHAR) COLLATE utf8mb4_unicode_ci
        LEFT JOIN members dcm ON dc.member_id COLLATE utf8mb4_unicode_ci = dcm.member_id COLLATE utf8mb4_unicode_ci
        LEFT JOIN beti_vivah_aavedan bv ON dt.application_type = 'Beti_Vivah' AND dt.claim_number = CAST(bv.application_number AS CHAR) COLLATE utf8mb4_unicode_ci
        WHERE dt.member_id = ?
        ORDER BY dt.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$member_id]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($donations),
        'donations' => $donations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
