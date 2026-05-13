<?php
/**
 * Get Poll Option Distribution
 * Returns count of members assigned to each poll option
 * GET /api/get_poll_option_distribution.php
 */

header('Content-Type: application/json; charset=UTF-8');

require_once '../includes/config.php';

try {
    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('डेटाबेस कनेक्शन विफल: ' . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");

    // Get all poll options and their member counts
    $query = "
        SELECT 
            p.poll AS poll_option,
            COUNT(m.member_id) AS member_count
        FROM (SELECT DISTINCT poll FROM poll ORDER BY poll ASC) p
        LEFT JOIN members m ON m.poll_option = p.poll AND m.status = 1
        GROUP BY p.poll
        ORDER BY p.poll ASC
    ";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $distribution = [];
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $distribution[] = [
            'poll_option' => $row['poll_option'],
            'member_count' => (int)$row['member_count'],
            'percentage' => 0
        ];
        $total += (int)$row['member_count'];
    }

    // Calculate percentages
    if ($total > 0) {
        foreach ($distribution as &$item) {
            $item['percentage'] = round(($item['member_count'] / $total) * 100, 2);
        }
    }

    // Return response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'total_members_with_poll' => $total,
        'distribution' => $distribution,
        'message' => 'Poll option distribution fetched successfully'
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'त्रुटि: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
