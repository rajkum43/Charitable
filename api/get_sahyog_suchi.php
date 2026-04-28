<?php
/**
 * Get Sahyog Suchi API
 * GET /pages/api/get_sahyog_suchi.php?type=beti_vivah
 * 
 * Fetches donation transactions filtered by type fined by application type (Beti Vivah or Death Claims) along with member details and applicant names.
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/config.php';

try {
    global $pdo;
    
    // Get type parameter
    $type = isset($_GET['type']) ? strtolower($_GET['type']) : 'beti_vivah';
    
    // Map type to application_type enum value
    $appType = $type === 'beti_vivah' ? 'Beti_Vivah' : 'Death_Claims';

    // Build query to fetch donation transactions with member details    
    $query = "
        SELECT 
            dt.id,
            dt.member_id,
            dt.donation_to_member_id,
            dt.claim_number,
            dt.application_type,
            dt.transaction_number,
            dt.amount,
            dt.created_at,
            dt.status,
            COALESCE(m_donor.full_name, 'N/A') as full_name,
            COALESCE(m_donor.district, 'N/A') as district,
            COALESCE(m_donor.block, 'N/A') as block,
            COALESCE(m_recipient.full_name, 'N/A') as recipient_name
        FROM donation_transactions dt
        LEFT JOIN members m_donor ON CAST(dt.member_id AS CHAR) = CAST(m_donor.member_id AS CHAR)
        LEFT JOIN members m_recipient ON CAST(dt.donation_to_member_id AS CHAR) = CAST(m_recipient.member_id AS CHAR)
        WHERE dt.application_type = ?
        ORDER BY dt.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([$appType]);
    
    if (!$result) {
        throw new Exception('Query execution failed: ' . implode(', ', $stmt->errorInfo()));
    }
    
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Data fetched successfully',
        'count' => count($donations),
        'data' => $donations
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'type' => $appType ?? 'undefined',
            'query_error' => true
        ]
    ]);
}
?>
