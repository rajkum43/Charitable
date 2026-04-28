<?php
// API - Delete Core Team Member
header('Content-Type: application/json');

try {
    require_once '../includes/config.php';
    
    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get member ID
    $member_id = intval($_POST['id'] ?? 0);
    
    if ($member_id <= 0) {
        throw new Exception('Invalid member ID');
    }
    
    // Get member details before deletion (for file cleanup)
    $query = "SELECT photo FROM core_team_members WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        throw new Exception('Member not found');
    }
    
    // Delete image file
    $file_path = '../uploads/core-team/' . $member['photo'];
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            throw new Exception('Failed to delete image file');
        }
    }
    
    // Delete from database
    $query = "DELETE FROM core_team_members WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([':id' => $member_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Team member deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete from database');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
