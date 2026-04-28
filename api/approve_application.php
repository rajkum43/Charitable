<?php
// Approve Application API
header('Content-Type: application/json');

require_once '../includes/config.php';

// Check if user is admin (basic check)
if (!isset($_SERVER['HTTP_X_ADMIN']) && !isset($_SESSION['admin_id'])) {
    // Allow for now, in production add proper auth
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'डेटाबेस कनेक्शन विफल'
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Only POST requests allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response['message'] = 'केवल POST request स्वीकृत है';
        echo json_encode($response);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['application_id']) || !isset($input['action'])) {
        http_response_code(400);
        $response['message'] = 'आवश्यक पैरामीटर गुम है';
        echo json_encode($response);
        exit;
    }
    
    $application_id = (int)$input['application_id'];
    $action = trim($input['action']);
    $remarks = isset($input['remarks']) ? trim($input['remarks']) : '';
    
    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        $response['message'] = 'गलत कार्रवाई';
        echo json_encode($response);
        exit;
    }
    
    // Validate remarks for rejection
    if ($action === 'reject' && empty($remarks)) {
        http_response_code(400);
        $response['message'] = 'अस्वीकृति के लिए कारण आवश्यक है';
        echo json_encode($response);
        exit;
    }
    
    // Determine new status
    $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
    
    // Check if application exists
    $check_stmt = $conn->prepare("SELECT id FROM beti_vivah_aavedan WHERE id = ?");
    $check_stmt->bind_param('i', $application_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        http_response_code(404);
        $response['message'] = 'आवेदन नहीं मिला';
        echo json_encode($response);
        exit;
    }
    
    $check_stmt->close();
    
    // Update application status
    $update_stmt = $conn->prepare("UPDATE beti_vivah_aavedan SET status = ?, remarks = ? WHERE id = ?");
    
    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $update_stmt->bind_param('ssi', $new_status, $remarks, $application_id);
    
    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = ($action === 'approve') ? 'आवेदन स्वीकृत हो गया' : 'आवेदन अस्वीकृत हो गया';
        http_response_code(200);
    } else {
        throw new Exception("Update failed: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = 'त्रुटि: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
