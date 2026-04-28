<?php
/**
 * Get Death Sahyog Suchi API
 * GET /api/get_death_sahyog_suchi.php
 * 
 * Fetches death claims donation transactions with member details
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/config.php';

try {
    global $pdo;

    // Build query to fetch death claim donation transactions with member details    
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
        WHERE dt.application_type = 'Death_Claims'
        ORDER BY dt.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute();
    
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
            'query_error' => true
        ]
    ]);
}
?>
