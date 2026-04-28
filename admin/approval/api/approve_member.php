<?php
// Admin: Approve Member
header('Content-Type: application/json');
session_start();

require_once '../../../includes/config.php';

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        $response['message'] = 'अनुमति प्राप्त नहीं';
        echo json_encode($response);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response['message'] = 'केवल POST request स्वीकृत है';
        echo json_encode($response);
        exit;
    }

    $member_id = isset($_POST['member_id']) ? trim($_POST['member_id']) : '';
    $action = isset($_POST['action']) ? trim($_POST['action']) : ''; // approve or reject

    if (empty($member_id) || empty($action)) {
        http_response_code(400);
        $response['message'] = 'सदस्य ID और कार्रवाई आवश्यक है';
        echo json_encode($response);
        exit;
    }

    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        $response['message'] = 'अमान्य कार्रवाई';
        echo json_encode($response);
        exit;
    }

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // If approving, delete payment receipt file from directory
    if ($action === 'approve') {
        // Get receipt file name from payment_receipts table
        $receipt_stmt = $conn->prepare("SELECT receipt_file_name FROM payment_receipts WHERE member_id = ? LIMIT 1");
        $receipt_stmt->bind_param('s', $member_id);
        $receipt_stmt->execute();
        $receipt_result = $receipt_stmt->get_result();

        if ($receipt_result->num_rows > 0) {
            $receipt_row = $receipt_result->fetch_assoc();
            $receipt_file_name = $receipt_row['receipt_file_name'];

            // Construct file path
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/Charitable/uploads/payment_receipts/' . $receipt_file_name;

            // Delete file if it exists
            if (file_exists($file_path)) {
                try {
                    unlink($file_path);
                } catch (Exception $e) {
                    // Log error but don't fail the approval process
                    error_log('Failed to delete receipt file: ' . $e->getMessage());
                }
            }
        }
        $receipt_stmt->close();
    }

    // Determine new status
    $new_status = ($action === 'approve') ? 1 : 2; // 1 = approved, 2 = rejected

    // Update member status
    $stmt = $conn->prepare("UPDATE members SET status = ?, updated_at = NOW() WHERE member_id = ?");
    $stmt->bind_param('is', $new_status, $member_id);
    
    if (!$stmt->execute()) {
        throw new Exception('अपडेट विफल: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('सदस्य नहीं मिला');
    }

    $stmt->close();

    $action_text = ($action === 'approve') ? 'अनुमोदित' : 'अस्वीकृत';
    $response['success'] = true;
    $response['message'] = 'सदस्य सफलतापूर्वक ' . $action_text . ' किया गया';

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
