<?php
// Admin: Download Payment Receipt
session_start();
require_once '../../../includes/config.php';

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        die('अनुमति प्राप्त नहीं');
    }

    $member_id = isset($_GET['member_id']) ? trim($_GET['member_id']) : '';

    if (empty($member_id)) {
        http_response_code(400);
        die('सदस्य ID आवश्यक है');
    }

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // Get receipt file from database
    $stmt = $conn->prepare("
        SELECT receipt_file_name, receipt_file_path 
        FROM payment_receipts 
        WHERE member_id = ? 
        LIMIT 1
    ");
    
    $stmt->bind_param('s', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        die('भुगतान रसीद नहीं मिली');
    }

    $receipt = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Construct file path
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/payment_receipts/' . $receipt['receipt_file_name'];

    // Check if file exists
    if (!file_exists($file_path)) {
        http_response_code(404);
        die('फ़ाइल नहीं मिली');
    }

    // Get file information
    $file_size = filesize($file_path);
    $file_type = mime_content_type($file_path);
    
    // If mime type detection fails, set default
    if (!$file_type) {
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_types = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $file_type = $mime_types[$file_ext] ?? 'application/octet-stream';
    }

    // Set headers for download
    header('Content-Type: ' . $file_type);
    header('Content-Length: ' . $file_size);
    header('Content-Disposition: attachment; filename="' . basename($receipt['receipt_file_name']) . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');

    // Output file
    readfile($file_path);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die('त्रुटि: ' . $e->getMessage());
}
?>
