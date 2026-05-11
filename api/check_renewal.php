<?php
// API to check if member needs renewal
header('Content-Type: application/json');

try {
    session_start();
    
    if (!isset($_SESSION['member_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    require_once '../includes/config.php';
    
    // Get PDO connection
    $pdo = $GLOBALS['pdo'] ?? null;
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    $member_id = $_SESSION['member_id'];

    // Check renew table first
    $stmt = $pdo->prepare("SELECT renew_date, renew_exp_date FROM renew WHERE member_id = ? ORDER BY renew_date DESC LIMIT 1");
    $stmt->execute([$member_id]);
    $renewal = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($renewal) {
        $renew_date = new DateTime($renewal['renew_date']);
        $now = new DateTime();
        $interval = $now->diff($renew_date);

        if ($interval->y >= 1 || ($interval->y == 1 && $interval->m >= 0)) {
            echo json_encode(['success' => true, 'needs_renewal' => true, 'source' => 'renew_table']);
            exit;
        } else {
            echo json_encode(['success' => true, 'needs_renewal' => false, 'message' => 'Renewal not due yet']);
            exit;
        }
    }

    // No record in renew table, check members table created_at
    $stmt = $pdo->prepare("SELECT created_at FROM members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        $created_at = new DateTime($member['created_at']);
        $now = new DateTime();
        $interval = $now->diff($created_at);

        if ($interval->y >= 1 || ($interval->y == 1 && $interval->m >= 0)) {
            echo json_encode(['success' => true, 'needs_renewal' => true, 'source' => 'members_table']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'needs_renewal' => false]);

} catch (Exception $e) {
    error_log('Check Renewal Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>