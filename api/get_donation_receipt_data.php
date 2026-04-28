<?php
/**
 * Get Donation Receipt Data
 * Fetches all receipt details for a transaction
 * Usage: /api/get_donation_receipt_data.php?txn_id=123
 */

header('Content-Type: application/json');

require_once '../includes/config.php';

try {
    // Get transaction ID from URL
    $txnId = isset($_GET['txn_id']) ? intval($_GET['txn_id']) : 0;
    
    if (!$txnId) {
        throw new Exception('Transaction ID required');
    }
    
    // 1. Fetch Transaction Details
    $txnStmt = $pdo->prepare("
        SELECT id, member_id, donation_to_member_id, claim_number, application_type, 
               amount, transaction_number, receipt_file_path, remarks, 
               status, created_at, updated_at
        FROM donation_transactions
        WHERE id = ?
        LIMIT 1
    ");
    $txnStmt->execute([$txnId]);
    $transaction = $txnStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        throw new Exception('Transaction not found');
    }
    
    // 2. Fetch Donor Details
    $donorStmt = $pdo->prepare("
        SELECT member_id, full_name, mobile_number, email, permanent_address, 
               block, district, state, aadhar_number, created_at
        FROM members
        WHERE member_id = ?
        LIMIT 1
    ");
    $donorStmt->execute([$transaction['member_id']]);
    $donor = $donorStmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Fetch Recipient Details
    $recipient = null;
    
    if ($transaction['application_type'] === 'Death_Claims') {
        $recipientStmt = $pdo->prepare("
            SELECT dc.id, dc.member_id, dc.full_name, dc.nominee_name, dc.nominee_relation,
                   dc.death_date, dc.bank_name, dc.account_number, dc.ifsc_code, dc.upi_id,
                   m.permanent_address, m.block, m.district, m.state
            FROM death_claims dc
            LEFT JOIN members m ON dc.member_id = m.member_id
            WHERE dc.claim_id = ?
            LIMIT 1
        ");
        $recipientStmt->execute([$transaction['claim_number']]);
        $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($transaction['application_type'] === 'Beti_Vivah') {
        $recipientStmt = $pdo->prepare("
            SELECT bv.id, bv.member_id, bv.bride_name, bv.groom_name, bv.member_name, bv.wedding_date,
                   bv.bank_name, bv.account_number, bv.ifsc_code, bv.upi_id,
                   m.permanent_address as address, m.block, m.district, m.state
            FROM beti_vivah_aavedan bv
            LEFT JOIN members m ON bv.member_id = m.member_id
            WHERE bv.application_number = ?
            LIMIT 1
        ");
        $recipientStmt->execute([$transaction['claim_number']]);
        $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$recipient) {
        throw new Exception('Recipient details not found');
    }
    
    // Format dates
    $transactionDate = new DateTime($transaction['created_at']);
    $formattedDate = $transactionDate->format('d/m/Y H:i');
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'transaction' => $transaction,
        'donor' => $donor,
        'recipient' => $recipient,
        'formattedDate' => $formattedDate
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
