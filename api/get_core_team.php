<?php
// API - Get Core Team Members
header('Content-Type: application/json');

try {
    require_once '../includes/config.php';
    
    $query = "SELECT id, full_name, mobile_number, post_name, photo, uploaded_at 
              FROM core_team_members 
              WHERE status = 'active' 
              ORDER BY uploaded_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($members) {
        // Add full image path
        foreach ($members as &$member) {
            $member['photo_url'] = '../uploads/core-team/' . $member['photo'];
        }
        echo json_encode(['success' => true, 'data' => $members]);
    } else {
        echo json_encode(['success' => true, 'data' => [], 'message' => 'No team members found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
