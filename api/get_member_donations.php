<?php
/**
 * Member Donations API
 * GET /api/get_member_donations.php
 * 
 * Returns active donation requests for logged-in member
 */

header('Content-Type: application/json; charset=UTF-8');

session_start();

// ===========================
// LOGIN CHECK
// ===========================
if (!isset($_SESSION['member_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

require_once __DIR__ . '/../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => [
        'member' => null,
        'donations' => []
    ]
];

try {
    global $pdo;
    $conn = $pdo;
    
    $memberId = (string)$_SESSION['member_id'];

    // ===========================
    // FETCH MEMBER INFO
    // ===========================
    $memberStmt = $conn->prepare("
        SELECT member_id, full_name, poll_option
        FROM members
        WHERE member_id = ?
        LIMIT 1
    ");
    $memberStmt->execute([$memberId]);
    $memberData = $memberStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$memberData) {
        throw new Exception('Member not found');
    }
    
    $pollOption = $memberData['poll_option'];
    
    $response['data']['member'] = [
        'member_id' => $memberData['member_id'],
        'full_name' => $memberData['full_name'],
        'poll_option' => $pollOption
    ];

    // ===========================
    // FETCH ACTIVE POLLS
    // ===========================
    if ($pollOption) {
        $pollStmt = $conn->prepare("
            SELECT id, claim_number, user_id, poll, application_type, 
                   start_poll_date, expire_poll_date, created_at
            FROM poll
            WHERE poll = ?
            AND CURDATE() BETWEEN start_poll_date AND expire_poll_date
            ORDER BY id DESC
        ");
        $pollStmt->execute([$pollOption]);
        $activePolls = $pollStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log what polls we found
        error_log("DEBUG: Found " . count($activePolls) . " active polls for option: " . $pollOption);

        // ===========================
        // FETCH DONATION RECORDS
        // ===========================
        foreach ($activePolls as $poll) {
            $claimNumber = $poll['claim_number'];
            error_log("DEBUG: Processing poll - Type: " . $poll['application_type'] . ", Claim: " . $claimNumber);
            $applicationType = $poll['application_type'];
            
            // ===========================
            // CHECK IF USER ALREADY DONATED
            // ===========================
            $checkDonationStmt = $conn->prepare("
                SELECT COUNT(*) as donation_count
                FROM donation_transactions
                WHERE member_id = ?
                AND claim_number = ? 
            ");
            $checkDonationStmt->execute([$memberId, $claimNumber]);
            $donationCheck = $checkDonationStmt->fetch(PDO::FETCH_ASSOC);
            
            // Skip this claim if user already donated
            if ($donationCheck['donation_count'] > 0) {
                continue;
            }
            
            if ($applicationType === 'Death' || $applicationType === 'Death_Claims') {
                // Fetch Death Claims from death_claims table
                $deathStmt = $conn->prepare("
                    SELECT id, member_id, full_name, father_name, 
                           dob, address, nominee_name, nominee_relation, 
                           death_date, age, status,
                           bank_name, account_number, account_holder_name, 
                           ifsc_code, upi_id, branch_name, claim_id
                    FROM death_claims
                    WHERE claim_id = ?
                    LIMIT 1
                ");
                $deathStmt->execute([$claimNumber]);
                $record = $deathStmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("DEBUG: Death query for claim {$claimNumber} - Found: " . ($record ? "YES" : "NO"));
                
                if ($record) {
                    error_log("DEBUG: Death record - Deceased Name: " . $record['full_name'] . ", Nominee: " . $record['nominee_name']);
                    $record['application_type'] = 'Death_Claims';
                    $record['claim_number'] = $claimNumber;
                    $record['poll_option'] = $poll['poll'];
                    $record['start_date'] = $poll['start_poll_date'];
                    $record['expire_date'] = $poll['expire_poll_date'];
                    // Add deceased_name and applicant_name for compatibility with JS
                    $record['deceased_name'] = $record['full_name'];
                    $record['applicant_name'] = $record['nominee_name'];
                    $response['data']['donations'][] = $record;
                } else {
                    error_log("DEBUG: No death_claims record found for claim_id: {$claimNumber}");
                }
                
            } elseif ($applicationType === 'Beti_Vivah') {
                // Fetch Beti Vivah Records
                $vivahStmt = $conn->prepare("
                    SELECT id, application_number, member_id, member_name,
                           bride_name, bride_dob, bride_aadhar, wedding_date,
                           address, district, block, city, state,
                           groom_name, groom_dob, groom_occupation,
                           bank_name, account_number, account_holder_name,
                           ifsc_code, upi_id, family_income, family_members,
                           branch_name
                    FROM beti_vivah_aavedan
                    WHERE application_number = ?
                    LIMIT 1
                ");
                $vivahStmt->execute([$claimNumber]);
                $record = $vivahStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($record) {
                    $record['application_type'] = 'Beti_Vivah';
                    $record['claim_number'] = $claimNumber; // Add consistent claim_number field
                    $record['poll_option'] = $poll['poll'];
                    $record['start_date'] = $poll['start_poll_date'];
                    $record['expire_date'] = $poll['expire_poll_date'];
                    $response['data']['donations'][] = $record;
                }
            }
        }
    }

    $response['success'] = true;
    $response['message'] = count($response['data']['donations']) . ' active donations found';
    
    // Add debug info  
    $response['debug'] = [
        'member_id' => $memberId,
        'poll_option' => $pollOption,
        'total_donations' => count($response['data']['donations']),
        'check_php_logs' => 'Check PHP error log for detailed debug messages'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
