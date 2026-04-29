<?php
/**
 * Get Approved Death Claims Applications
 * Returns all approved death claim applications with member details
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
        d.id,
        d.member_id,
        d.full_name as deceased_name,
        d.nominee_name as account_holder_name,
        d.death_date,
        COALESCE(m.district, 'Unknown') as district,
        COALESCE(m.block, 'Unknown') as block,
        d.account_number,
        d.bank_name,
        d.ifsc_code
    FROM death_claims d
    LEFT JOIN members m ON d.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
    WHERE d.status IN ('approved', 'Approved')
    ORDER BY d.death_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'applications' => $applications
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
