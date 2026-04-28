<?php
// Set headers first - ALWAYS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

// Global error handler
function handleError($errno, $errstr, $errfile, $errline) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'error' => $errstr
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

set_error_handler('handleError');

try {
    // Load database config
    $config_path = dirname(__DIR__) . '/includes/config.php';
    
    if (!file_exists($config_path)) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Configuration file not found'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    require_once $config_path;
    
    // Verify PDO connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection not available'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !is_array($input)) {
        $input = $_POST ?? [];
    }
    
    $action = $input['action'] ?? null;

    if ($action === 'search_member') {
        $aadhaar = trim($input['aadhaar'] ?? '');

        if (!$aadhaar || strlen($aadhaar) !== 8 || !is_numeric($aadhaar)) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'कृपया आधार के अंतिम 8 अंक दर्ज करें'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Search for member by aadhaar
        $query = "SELECT member_id, full_name, father_husband_name, date_of_birth, 
                  permanent_address FROM members WHERE aadhar_number LIKE ? LIMIT 1";
        
        try {
            $stmt = $pdo->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement');
            }
            
            $result = $stmt->execute(['%' . $aadhaar]);
            if (!$result) {
                throw new Exception('Failed to execute query');
            }
            
            $member = $stmt->fetch();

            if ($member) {
                ob_end_clean();
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'member_id' => (string)$member['member_id'],
                        'full_name' => (string)$member['full_name'],
                        'father_name' => (string)($member['father_husband_name'] ?? ''),
                        'dob' => (string)($member['date_of_birth'] ?? ''),
                        'address' => (string)($member['permanent_address'] ?? '')
                    ]
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                ob_end_clean();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'सदस्य नहीं मिला। कृपया सही आधार नंबर दर्ज करें।'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (PDOException $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database query error'
            ], JSON_UNESCAPED_UNICODE);
            error_log('Death Claims Fetch Query Error: ' . $e->getMessage());
            exit;
        }
    } else {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ], JSON_UNESCAPED_UNICODE);
    error_log('Death Claims Fetch Error: ' . $e->getMessage());
    exit;
}
