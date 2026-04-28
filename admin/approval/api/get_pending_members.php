<?php
// Admin: Get Pending Members
header('Content-Type: application/json');
session_start();

require_once '../../../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Check if admin is logged in (basic check - you can enhance this)
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        $response['message'] = 'अनुमति प्राप्त नहीं';
        echo json_encode($response);
        exit;
    }

    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल');
    }

    $conn->set_charset("utf8mb4");

    // Get filter parameters
    $status = isset($_GET['status']) ? intval($_GET['status']) : 0; // 0 = pending, 1 = approved, 2 = rejected
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Build query
    $query = "SELECT 
                member_id, login_id, full_name, aadhar_number, 
                mobile_number, email, state, district, block, 
                utr_number, payment_verified, status, created_at 
              FROM members 
              WHERE status = ?";
    
    $params = [$status];
    $types = 'i';

    // Add search filter
    if (!empty($search)) {
        $query .= " AND (full_name LIKE ? OR member_id LIKE ? OR mobile_number LIKE ? OR email LIKE ?)";
        $search_param = '%' . $search . '%';
        $params = array_merge([$status, $search_param, $search_param, $search_param, $search_param]);
        $types = 'issss';
    }

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM members WHERE status = ?";
    if (!empty($search)) {
        $count_query = "SELECT COUNT(*) as total FROM members WHERE status = ? AND (full_name LIKE ? OR member_id LIKE ? OR mobile_number LIKE ? OR email LIKE ?)";
    }
    
    $count_stmt = $conn->prepare($count_query);
    if (empty($search)) {
        $count_stmt->bind_param('i', $status);
    } else {
        $count_stmt->bind_param('issss', $status, $search_param, $search_param, $search_param, $search_param);
    }
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();

    // Add sorting and pagination
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    // Prepare and execute main query
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($params) - 2) . 'ii', ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('d/m/Y H:i', strtotime($row['created_at']));
        $row['aadhar_masked'] = substr($row['aadhar_number'], -4) ? '****-****-' . substr($row['aadhar_number'], -4) : '****-****-****';
        $row['payment_status'] = $row['payment_verified'] == 1 ? 'सत्यापित' : 'लंबित';
        $members[] = $row;
    }

    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'डेटा सफलतापूर्वक लोड हुआ';
    $response['data'] = [
        'members' => $members,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ];

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
