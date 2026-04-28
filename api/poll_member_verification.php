<?php
// Poll System - Member Verification API
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

// Verify member and get active polls
if (isset($_POST['action']) && $_POST['action'] === 'verify_member') {
    $member_id = htmlspecialchars(trim($_POST['member_id'] ?? ''));
    $mobile = htmlspecialchars(trim($_POST['mobile'] ?? ''));

    if (empty($member_id) || empty($mobile)) {
        $response['message'] = 'Member ID और Mobile नंबर दोनों अनिवार्य हैं';
        echo json_encode($response);
        exit;
    }

    // Check if member exists
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

    // Get active polls for this member
    $stmt = $conn->prepare("
        SELECT p.*, pm.payment_status 
        FROM polls p
        LEFT JOIN poll_members pm ON p.id = pm.poll_id AND pm.member_id = ?
        WHERE p.status = 'Active' 
        AND p.start_date <= CURDATE()
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param('s', $member_id);
    $stmt->execute();
    $polls_result = $stmt->get_result();

    $polls = [];
    while ($poll = $polls_result->fetch_assoc()) {
        $polls[] = $poll;
    }
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'सदस्य सत्यापित';
    $response['data'] = [
        'member' => $member,
        'polls' => $polls
    ];
}

// Get poll details with beneficiary info
elseif (isset($_POST['action']) && $_POST['action'] === 'get_poll_details') {
    $poll_id = (int)($_POST['poll_id'] ?? 0);

    if ($poll_id === 0) {
        $response['message'] = 'Poll ID अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT p.*, m.upi_id, m.father_husband_name, 
               (SELECT COUNT(*) FROM poll_members WHERE poll_id = p.id AND payment_status = 'Paid') as paid_count,
               (SELECT SUM(paid_amount) FROM poll_members WHERE poll_id = p.id AND payment_status = 'Paid') as total_paid
        FROM polls p
        LEFT JOIN members m ON p.beneficiary_id = m.member_id
        WHERE p.id = ?
    ");
    $stmt->bind_param('i', $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'Poll नहीं मिला';
        echo json_encode($response);
        exit;
    }

    $poll = $result->fetch_assoc();
    $stmt->close();

    $response['success'] = true;
    $response['data'] = $poll;
}

echo json_encode($response);
$conn->close();
?>
