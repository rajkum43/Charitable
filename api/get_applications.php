<?php
// Get Applications API
header('Content-Type: application/json');

require_once '../includes/config.php';

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
    'success' => true,
    'applications' => []
];

try {
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $query = "SELECT * FROM beti_vivah_aavedan WHERE 1=1";
    
    if (!empty($status)) {
        $query .= " AND status = ?";
    }
    
    if (!empty($search)) {
        $query .= " AND (member_id LIKE ? OR member_name LIKE ? OR bride_name LIKE ?)";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($status) && !empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bind_param('ssss', $status, $searchTerm, $searchTerm, $searchTerm);
    } elseif (!empty($status)) {
        $stmt->bind_param('s', $status);
    } elseif (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $response['applications'][] = $row;
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = 'त्रुटि: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
