<?php
// Poll System - Payment Processing API
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

// Record payment
if (isset($_POST['action']) && $_POST['action'] === 'record_payment') {
    $member_id = htmlspecialchars(trim($_POST['member_id'] ?? ''));
    $poll_id = (int)($_POST['poll_id'] ?? 0);
    $amount = (int)($_POST['amount'] ?? 0);
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''));
    $utr_number = htmlspecialchars(trim($_POST['utr_number'] ?? ''));
    $transaction_id = htmlspecialchars(trim($_POST['transaction_id'] ?? ''));

    if (empty($member_id) || $poll_id === 0 || $amount === 0 || empty($payment_method)) {
        $response['message'] = 'सभी फील्ड भरना अनिवार्य है';
        echo json_encode($response);
        exit;
    }

    // Handle screenshot upload
    $screenshot_path = null;
    if (isset($_FILES['screenshot'])) {
        $upload_dir = '../uploads/poll_payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file = $_FILES['screenshot'];
        if ($file['size'] > 500 * 1024) { // 500KB max
            $response['message'] = 'स्क्रीनशॉट 500KB से बड़ा नहीं होना चाहिए';
            echo json_encode($response);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            $response['message'] = 'केवल JPG, PNG या PDF अनुमति है';
            echo json_encode($response);
            exit;
        }

        $new_filename = 'payment_' . $member_id . '_' . time() . '.' . $ext;
        $file_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $screenshot_path = $new_filename;
        }
    }

    // Update poll_members table
    $stmt = $conn->prepare("
        INSERT INTO poll_members (poll_id, member_id, payment_status, paid_amount, payment_method, utr_number, transaction_id, screenshot_path, payment_date)
        VALUES (?, ?, 'Pending', ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        payment_status = 'Pending',
        paid_amount = ?,
        payment_method = ?,
        utr_number = ?,
        transaction_id = ?,
        screenshot_path = ?,
        payment_date = NOW()
    ");

    $stmt->bind_param('isissssisssi', $poll_id, $member_id, $amount, $payment_method, $utr_number, $transaction_id, $screenshot_path, $amount, $payment_method, $utr_number, $transaction_id, $screenshot_path);

    if ($stmt->execute()) {
        // Also create payment tracking record
        $poll_member_id = 0;
        $stmt2 = $conn->prepare("SELECT id FROM poll_members WHERE poll_id = ? AND member_id = ?");
        $stmt2->bind_param('is', $poll_id, $member_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        if ($row = $result->fetch_assoc()) {
            $poll_member_id = $row['id'];
        }
        $stmt2->close();

        if ($poll_member_id > 0) {
            $stmt3 = $conn->prepare("
                INSERT INTO poll_payments (poll_member_id, poll_id, member_id, amount, payment_date, payment_method, utr_number, transaction_id, screenshot_path, status)
                VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, 'Pending')
            ");
            $stmt3->bind_param('iisissa', $poll_member_id, $poll_id, $member_id, $amount, $payment_method, $utr_number, $transaction_id, $screenshot_path);
            $stmt3->execute();
            $stmt3->close();
        }

        $response['success'] = true;
        $response['message'] = 'भुगतान दर्ज किया गया। प्रशासक द्वारा सत्यापन के लिए प्रतीक्षा करें';
        $response['data'] = ['payment_recorded' => true];
    } else {
        $response['message'] = 'भुगतान दर्ज करने में त्रुटि: ' . $stmt->error;
    }
    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>
