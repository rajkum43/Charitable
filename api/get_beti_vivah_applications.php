<?php
// Get Beti Vivah Applications API
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
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    $types = '';

    // Add search filter
    if (!empty($search)) {
        $where_conditions[] = "(application_number LIKE ? OR member_name LIKE ? OR bride_name LIKE ? OR member_id LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ssss';
    }

    // Add district filter (from members table)
    if (!empty($district)) {
        $where_conditions[] = "m.district = ?";
        $params[] = $district;
        $types .= 's';
    }

    // Add block filter (from members table)
    if (!empty($block)) {
        $where_conditions[] = "m.block = ?";
        $params[] = $block;
        $types .= 's';
    }

    // Add status filter
    if (!empty($status)) {
        $where_conditions[] = "bva.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get total count with JOIN
    $count_query = "SELECT COUNT(*) as total FROM beti_vivah_aavedan bva 
                    LEFT JOIN members m ON bva.member_id = m.member_id 
                    " . $where_clause;
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

    // Get applications with member details from members table
    $query = "SELECT 
                bva.id, 
                bva.application_number, 
                bva.member_id, 
                bva.member_name, 
                bva.bride_name, 
                bva.wedding_date,
                bva.address, 
                COALESCE(m.district, bva.district, 'Unknown') as district, 
                COALESCE(m.block, bva.block, 'Unknown') as block, 
                COALESCE(m.state, bva.state, 'Unknown') as state,
                COALESCE(m.permanent_address, bva.address) as permanent_address,
                bva.created_at, 
                bva.updated_at, 
                bva.status, 
                bva.remarks, 
                bva.marriage_certificate
              FROM beti_vivah_aavedan bva
              LEFT JOIN members m ON bva.member_id = m.member_id 
              " . $where_clause . "
              ORDER BY bva.created_at DESC 
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
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Process applications to add full image paths
    foreach ($applications as &$app) {
        if (!empty($app['marriage_certificate'])) {
            $app['marriage_certificate'] = 'uploads/beti_vivah/' . $app['marriage_certificate'];
        }
    }

    // Get statistics
    $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                    FROM beti_vivah_aavedan";
    
    $stats_result = $conn->query($stats_query);
    $stats = $stats_result->fetch_assoc();

    // Get unique districts from members table
    $districts_query = "SELECT DISTINCT m.district 
                        FROM members m 
                        INNER JOIN beti_vivah_aavedan bva ON m.member_id = bva.member_id 
                        WHERE m.district IS NOT NULL AND m.district != '' AND m.district != 'Unknown' 
                        ORDER BY m.district ASC";
    $districts_result = $conn->query($districts_query);
    $districts = [];
    while ($row = $districts_result->fetch_assoc()) {
        if (!empty($row['district'])) {
            $districts[] = $row['district'];
        }
    }

    // Get blocks for selected district from members table
    $blocks = [];
    if (!empty($district)) {
        $blocks_query = "SELECT DISTINCT m.block 
                        FROM members m 
                        INNER JOIN beti_vivah_aavedan bva ON m.member_id = bva.member_id 
                        WHERE m.district = ? AND m.block IS NOT NULL AND m.block != '' AND m.block != 'Unknown' 
                        ORDER BY m.block ASC";
        $blocks_stmt = $conn->prepare($blocks_query);
        $blocks_stmt->bind_param('s', $district);
        $blocks_stmt->execute();
        $blocks_result = $blocks_stmt->get_result();
        while ($row = $blocks_result->fetch_assoc()) {
            if (!empty($row['block'])) {
                $blocks[] = $row['block'];
            }
        }
        $blocks_stmt->close();
    }

    $response['success'] = true;
    $response['message'] = 'डेटा सफलतापूर्वक लोड हुआ';
    $response['data'] = [
        'applications' => $applications,
        'stats' => [
            'total' => intval($stats['total'] ?? 0),
            'pending' => intval($stats['pending'] ?? 0),
            'approved' => intval($stats['approved'] ?? 0),
            'rejected' => intval($stats['rejected'] ?? 0)
        ],
        'districts' => $districts,
        'blocks' => $blocks,
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'totalPages' => ceil($total / $limit)
    ];

    $conn->close();
    
} catch (Exception $e) {
    http_response_code(400);
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Beti Vivah API Error: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
