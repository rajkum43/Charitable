<?php
// Get Approved Members API - FIXED VERSION
header('Content-Type: application/json');

require_once '../includes/config.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल: ' . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    // Get filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $district = isset($_GET['district']) ? trim($_GET['district']) : '';
    $block = isset($_GET['block']) ? trim($_GET['block']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;

    // Build WHERE clause
    $where_conditions = ["status = 1"];
    $params = [];
    $types = '';

    // Add search filter
    if (!empty($search)) {
        $where_conditions[] = "(member_id LIKE ? OR full_name LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }

    // Add district filter
    if (!empty($district)) {
        $where_conditions[] = "district = ?";
        $params[] = $district;
        $types .= 's';
    }

    // Add block filter
    if (!empty($block)) {
        $where_conditions[] = "block = ?";
        $params[] = $block;
        $types .= 's';
    }

    $where_clause = implode(" AND ", $where_conditions);

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM members WHERE " . $where_clause;
    $count_stmt = $conn->prepare($count_query);
    
    if (!$count_stmt) {
        throw new Exception('Count query prepare failed: ' . $conn->error);
    }
    
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total = $count_row['total'] ?? 0;
    $count_stmt->close();

    // Get members
    $query = "SELECT 
                id, member_id, full_name, district, block, status, created_at, poll_option, 
                permanent_address, state, father_husband_name, nominee_name, nominee_relation
              FROM members 
              WHERE " . $where_clause . "
              ORDER BY created_at DESC 
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Main query prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        // Keep date in ISO format for JavaScript
        $row['created_at'] = $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : null;
        $members[] = $row;
    }

    $stmt->close();

    // Get unique districts
    $district_query = "SELECT DISTINCT district FROM members WHERE status = 1 AND district IS NOT NULL AND district != '' ORDER BY district ASC";
    $district_result = $conn->query($district_query);
    $districts = [];
    if ($district_result) {
        while ($row = $district_result->fetch_assoc()) {
            $districts[] = $row['district'];
        }
    }

    // Get unique blocks if district is selected
    $blocks = [];
    if (!empty($district)) {
        $block_query = "SELECT DISTINCT block FROM members WHERE status = 1 AND district = ? AND block IS NOT NULL AND block != '' ORDER BY block ASC";
        $block_stmt = $conn->prepare($block_query);
        if ($block_stmt) {
            $block_stmt->bind_param('s', $district);
            $block_stmt->execute();
            $block_result = $block_stmt->get_result();
            while ($row = $block_result->fetch_assoc()) {
                $blocks[] = $row['block'];
            }
            $block_stmt->close();
        }
    }

    // Get statistics
    $stats_query = "SELECT 
                    COUNT(*) as total_members,
                    COALESCE(SUM(CASE WHEN payment_verified = 1 THEN 1 ELSE 0 END), 0) as verified_members,
                    (SELECT COUNT(DISTINCT district) FROM members WHERE status = 1 AND district IS NOT NULL AND district != '') as district_count,
                    (SELECT COUNT(DISTINCT block) FROM members WHERE status = 1 AND block IS NOT NULL AND block != '') as block_count
                    FROM members WHERE status = 1";
    $stats_result = $conn->query($stats_query);
    $stats = $stats_result ? $stats_result->fetch_assoc() : [];

    $response['success'] = true;
    $response['message'] = 'डेटा सफलतापूर्वक लोड हुआ';
    $response['data'] = [
        'members' => $members,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit),
        'districts' => $districts,
        'blocks' => $blocks,
        'stats' => $stats
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
