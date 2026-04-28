<?php
// Admin Poll Application Approval API
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

// Get application details
if (isset($_POST['action']) && $_POST['action'] === 'get_application') {
    $app_id = (int)($_POST['app_id'] ?? 0);

    if ($app_id === 0) {
        $response['message'] = 'आवेदन ID अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT pa.*, m.full_name, m.member_id, m.mobile_number, m.aadhar_number 
        FROM poll_applications pa
        JOIN members m ON pa.member_id = m.member_id
        WHERE pa.id = ?
    ");
    $stmt->bind_param('i', $app_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'आवेदन नहीं मिला';
        echo json_encode($response);
        exit;
    }

    $application = $result->fetch_assoc();
    $stmt->close();

    $response['success'] = true;
    $response['data'] = $application;
}

// Approve application and create poll
elseif (isset($_POST['action']) && $_POST['action'] === 'approve_application') {
    $app_id = (int)($_POST['app_id'] ?? 0);

    if ($app_id === 0) {
        $response['message'] = 'आवेदन ID अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Get application details
        $stmt = $conn->prepare("
            SELECT pa.*, m.member_id 
            FROM poll_applications pa
            JOIN members m ON pa.member_id = m.member_id
            WHERE pa.id = ?
        ");
        $stmt->bind_param('i', $app_id);
        $stmt->execute();
        $app_result = $stmt->get_result();
        $application = $app_result->fetch_assoc();
        $stmt->close();

        // Update application status
        $approved_date = date('Y-m-d');
        $stmt = $conn->prepare("UPDATE poll_applications SET status = 'Approved', approved_date = ? WHERE id = ?");
        $stmt->bind_param('si', $approved_date, $app_id);
        $stmt->execute();
        $stmt->close();

        // Calculate donation start date
        $approval_day = (int)date('d');
        $current_month = date('Y-m');
        
        if ($approval_day <= 15) {
            $donation_start = $current_month . '-01';
        } else {
            $donation_start = date('Y-m-01', strtotime('+1 month'));
        }

        // Get total members count
        $total_members_result = $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 1");
        $total_members_row = $total_members_result->fetch_assoc();
        $total_members = $total_members_row['count'];

        // Count approved applications for load distribution
        $approved_count_result = $conn->query("SELECT COUNT(*) as count FROM poll_applications WHERE status = 'Approved'");
        $approved_count_row = $approved_count_result->fetch_assoc();
        $total_approved = $approved_count_row['count'];

        // Calculate members per poll
        $members_per_poll = ceil($total_members / $total_approved);

        // Create poll
        $poll_code = 'POLL' . date('Ymd') . str_pad($app_id, 4, '0', STR_PAD_LEFT);
        $poll_name = ($application['type'] === 'vivah' ? 'विवाह सहायता' : 'मृत्यु लाभ') . ' - ' . date('M Y');
        $beneficiary_name = $application['member_id']; // Will be updated with actual name
        $poll_type = $application['type'];

        $stmt = $conn->prepare("
            INSERT INTO polls (poll_name, poll_code, application_id, beneficiary_id, beneficiary_name, poll_type, total_members, start_date, donation_amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 50)
        ");
        $stmt->bind_param('sssssssi', $poll_name, $poll_code, $app_id, $application['member_id'], $beneficiary_name, $poll_type, $members_per_poll, $donation_start);
        $stmt->execute();
        $poll_id = $stmt->insert_id;
        $stmt->close();

        // Distribute members to poll
        $start_offset = ($total_approved - 1) * $members_per_poll;
        $stmt = $conn->prepare("
            INSERT INTO poll_members (poll_id, member_id, payment_status)
            SELECT ?, member_id, 'Pending'
            FROM members
            WHERE status = 1
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('iii', $poll_id, $members_per_poll, $start_offset);
        $stmt->execute();
        $stmt->close();

        // Update application with poll_id
        $stmt = $conn->prepare("UPDATE poll_applications SET poll_id = ? WHERE id = ?");
        $stmt->bind_param('ii', $poll_id, $app_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        $response['success'] = true;
        $response['message'] = 'आवेदन अनुमोदित किया गया और पोल बनाया गया';
        $response['data'] = ['poll_id' => $poll_id];

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'त्रुटि: ' . $e->getMessage();
    }
}

// Reject application
elseif (isset($_POST['action']) && $_POST['action'] === 'reject_application') {
    $app_id = (int)($_POST['app_id'] ?? 0);
    $reason = htmlspecialchars(trim($_POST['reason'] ?? ''));

    if ($app_id === 0) {
        $response['message'] = 'आवेदन ID अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("UPDATE poll_applications SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param('i', $app_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'आवेदन अस्वीकार किया गया';
    } else {
        $response['message'] = 'अस्वीकार करने में त्रुटि';
    }
    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>
