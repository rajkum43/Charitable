<?php
/**
 * API to fetch approved Beti Vivah applications with account holder details
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Fetch only approved applications with district and block from members table
    $query = "SELECT 
                b.id,
                b.member_id,
                b.member_name,
                b.account_holder_name,
                b.wedding_date,
                COALESCE(m.district, b.district) as district,
                COALESCE(m.block, b.block) as block,
                b.account_number,
                b.bank_name,
                b.ifsc_code
              FROM beti_vivah_aavedan b
              LEFT JOIN members m ON b.member_id = m.member_id
              WHERE b.status = 'Approved' 
              ORDER BY b.wedding_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'applications' => $applications,
        'total' => count($applications)
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_ac_holder_betvivah_applications.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch applications',
        'message' => $e->getMessage()
    ]);
}
?>
