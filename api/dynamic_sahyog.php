<?php
// Dynamic Sahyog System - Get current beneficiary with least donations
header('Content-Type: application/json');

require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'डेटाबेस कनेक्शन विफल']);
    exit;
}
$conn->set_charset("utf8mb4");

$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

// Verify member and get current active beneficiary
if (isset($_POST['action']) && $_POST['action'] === 'verify_member') {
    $member_id = htmlspecialchars(trim($_POST['member_id'] ?? ''));
    $mobile = htmlspecialchars(trim($_POST['mobile'] ?? ''));

    if (empty($member_id)) {
        $response['message'] = 'Member ID अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    // Check if member exists
    if (!empty($mobile)) {
        // Validate with both member_id and mobile
        $stmt = $conn->prepare("SELECT member_id, full_name, mobile_number FROM members WHERE member_id = ? AND mobile_number = ?");
        $stmt->bind_param('ss', $member_id, $mobile);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $response['message'] = 'Member ID या Mobile नंबर मेल नहीं खाते';
            echo json_encode($response);
            exit;
        }

        $member = $result->fetch_assoc();
        $stmt->close();
    } else {
        // Auto-load - only check member_id (from session)
        $stmt = $conn->prepare("SELECT member_id, full_name, mobile_number FROM members WHERE member_id = ?");
        $stmt->bind_param('s', $member_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $response['message'] = 'Member नहीं मिला';
            echo json_encode($response);
            exit;
        }

        $member = $result->fetch_assoc();
        $stmt->close();
    }

    // Get current active beneficiary with least donations
    $current_beneficiary = getCurrentActiveBeneficiary($conn);

    $response['success'] = true;
    $response['message'] = 'सदस्य सत्यापित';
    $response['data'] = [
        'member' => $member,
        'current_beneficiary' => $current_beneficiary
    ];
}

// Get payment details for current beneficiary
elseif (isset($_POST['action']) && $_POST['action'] === 'get_payment_details') {
    $beneficiary_id = htmlspecialchars(trim($_POST['beneficiary_id'] ?? ''));

    if (empty($beneficiary_id)) {
        $response['message'] = 'Beneficiary ID अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    // Get beneficiary details
    $stmt = $conn->prepare("
        SELECT pa.id, pa.member_id, pa.type, pa.approved_date,
               m.full_name, m.upi_id, m.father_husband_name,
               (SELECT COALESCE(SUM(amount), 0) FROM poll_payments WHERE member_id = m.member_id) as total_collected
        FROM poll_applications pa
        JOIN members m ON pa.member_id = m.member_id
        WHERE pa.member_id = ? AND pa.status = 'Approved'
        LIMIT 1
    ");
    $stmt->bind_param('s', $beneficiary_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'Beneficiary विवरण नहीं मिला';
        echo json_encode($response);
        exit;
    }

    $beneficiary = $result->fetch_assoc();
    $stmt->close();

    // Get bank details if available
    $bank_details = getBankDetails($conn, $beneficiary['member_id']);

    $response['success'] = true;
    $response['data'] = [
        'beneficiary' => $beneficiary,
        'upi' => $beneficiary['upi_id'],
        'bank' => $bank_details,
        'amount' => 50, // Default donation amount
        'total_collected' => $beneficiary['total_collected']
    ];
}

// Record payment
elseif (isset($_POST['action']) && $_POST['action'] === 'record_payment') {
    $beneficiary_id = htmlspecialchars(trim($_POST['beneficiary_id'] ?? ''));
    $member_id = htmlspecialchars(trim($_POST['member_id'] ?? ''));
    $amount = (int)($_POST['amount'] ?? 50);
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? 'UPI'));

    if (empty($beneficiary_id) || empty($member_id)) {
        $response['message'] = 'आवश्यक जानकारी अधूरी है';
        echo json_encode($response);
        exit;
    }

    // Record payment in poll_payments table
    $stmt = $conn->prepare("
        INSERT INTO poll_payments (poll_id, member_id, amount, payment_date, payment_method)
        VALUES (0, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param('sis', $beneficiary_id, $amount, $payment_method);

    if ($stmt->execute()) {
        $stmt->close();
        $response['success'] = true;
        $response['message'] = 'भुगतान दर्ज किया गया';
        
        // Get next beneficiary
        $next_beneficiary = getCurrentActiveBeneficiary($conn);
        $response['data']['next_beneficiary'] = $next_beneficiary;
    } else {
        $response['message'] = 'भुगतान दर्ज करने में त्रुटि: ' . $stmt->error;
        $stmt->close();
    }
}

echo json_encode($response);
$conn->close();

/**
 * Get current active beneficiary with least donations
 * Rules:
 * 1. Pick approved applications not expired yet
 * 2. Find one with least total donations
 * 3. If equal donations, pick the one with earliest approval date
 */
function getCurrentActiveBeneficiary($conn) {
    // Get all approved applications with their total donations
    $query = "
        SELECT 
            pa.id,
            pa.member_id,
            pa.type,
            pa.approved_date,
            m.full_name,
            m.upi_id,
            m.father_husband_name,
            m.aadhar_number,
            COALESCE(SUM(pp.amount), 0) as total_collected
        FROM poll_applications pa
        JOIN members m ON pa.member_id = m.member_id
        LEFT JOIN poll_payments pp ON pa.member_id = pp.member_id AND YEAR(pp.payment_date) = YEAR(CURDATE())
        WHERE pa.status = 'Approved'
        AND (SELECT COUNT(*) FROM poll_payments WHERE member_id = pa.member_id) < 500
        GROUP BY pa.member_id
        ORDER BY total_collected ASC, pa.approved_date ASC
        LIMIT 1
    ";

    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get bank details for member
 */
function getBankDetails($conn, $member_id) {
    // This assumes you have bank details in members table or a separate table
    // Adjust based on your actual database structure
    
    $stmt = $conn->prepare("
        SELECT bank_name, account_number, ifsc_code, account_holder_name
        FROM members
        WHERE member_id = ?
    ");
    $stmt->bind_param('s', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $stmt->close();
        return [
            'account_name' => $data['account_holder_name'] ?? '',
            'account_number' => $data['account_number'] ?? '',
            'ifsc' => $data['ifsc_code'] ?? '',
            'bank_name' => $data['bank_name'] ?? ''
        ];
    }
    
    $stmt->close();
    return null;
}
?>
