<?php
// API for member renewal
header('Content-Type: application/json');

try {
    session_start();
    
    if (!isset($_SESSION['member_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    require_once '../includes/config.php';
    
    // Get PDO connection
    $pdo = $GLOBALS['pdo'] ?? null;
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $transaction_id = trim($input['transactionId'] ?? '');

    if (empty($transaction_id)) {
        echo json_encode(['success' => false, 'message' => 'लेन-देन ID आवश्यक है']);
        exit;
    }

    $member_id = $_SESSION['member_id'];

    // Check if transaction_id already exists
    $check_stmt = $pdo->prepare("SELECT id FROM renew WHERE transaction_id = ?");
    $check_stmt->execute([$transaction_id]);
    if ($check_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => false, 'message' => 'यह लेन-देन ID पहले से उपयोग में है']);
        exit;
    }

    // Calculate dates
    $renew_date = date('Y-m-d');
    $renew_exp_date = date('Y-m-d', strtotime('+1 year'));

    // Insert renewal record
    $stmt = $pdo->prepare("INSERT INTO renew (member_id, transaction_id, renew_date, renew_exp_date) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$member_id, $transaction_id, $renew_date, $renew_exp_date])) {
        echo json_encode(['success' => true, 'message' => 'सदस्यता नवीनीकृत की गई']);
    } else {
        echo json_encode(['success' => false, 'message' => 'नवीनीकरण विफल हुआ']);
    }

} catch (Exception $e) {
    error_log('Renewal Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>