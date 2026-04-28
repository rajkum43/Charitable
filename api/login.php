<?php
session_start();
header('Content-Type: application/json');

// Include database configuration
require_once '../includes/config.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'डेटाबेस कनेक्शन विफल: ' . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Handle both POST (form submission) and JSON requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if form data or JSON data
        if (isset($_POST['loginId']) && isset($_POST['password'])) {
            $login_id = trim($_POST['loginId']);
            $password = trim($_POST['password']);
        } else {
            // Try JSON
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['loginId']) || !isset($input['password'])) {
                throw new Exception('लॉगिन ID और पासवर्ड दोनों अनिवार्य हैं');
            }
            $login_id = trim($input['loginId']);
            $password = trim($input['password']);
        }

        // Validate input
        if (empty($login_id) || empty($password)) {
            throw new Exception('लॉगिन ID और पासवर्ड दोनों अनिवार्य हैं');
        }

        // Validate login ID format
        if (!preg_match('/^\d{8}$/', $login_id)) {
            throw new Exception('लॉगिन ID 8 अंकों की होनी चाहिए');
        }

        // Query member by login_id
        $stmt = $conn->prepare("SELECT member_id, login_id, password, full_name, aadhar_number, status FROM members WHERE login_id = ?");
        $stmt->bind_param("s", $login_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('गलत लॉगिन ID या पासवर्ड');
        }

        $member = $result->fetch_assoc();
        $stmt->close();

        // Check member status
        if ($member['status'] == 0) {
            throw new Exception('आपका पंजीकरण अभी अनुमोदित नहीं हुआ। कृपया व्यवस्थापक की प्रतीक्षा करें।');
        }

        if ($member['status'] == 2) {
            throw new Exception('आपका पंजीकरण अस्वीकार कर दिया गया है। विवरण के लिए व्यवस्थापक से संपर्क करें।');
        }

        // Define default password
        $defaultPassword = 'Brctbharat1.0';

        // Verify password - check both custom password and default password
        $passwordVerified = false;
        
        // Check custom password first
        if (password_verify($password, $member['password'])) {
            $passwordVerified = true;
        } else {
            // Check default password
            if ($password === $defaultPassword) {
                $passwordVerified = true;
            }
        }

        if (!$passwordVerified) {
            throw new Exception('गलत लॉगिन ID या पासवर्ड');
        }

        // Login successful - set session
        $_SESSION['member_id'] = $member['member_id'];
        $_SESSION['login_id'] = $member['login_id'];
        $_SESSION['full_name'] = $member['full_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // Handle "Remember Me" functionality
        $remember_me = isset($_POST['rememberMe']) ? true : false;
        if ($remember_me) {
            // Create a token for remember me
            $token = bin2hex(random_bytes(16));
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            setcookie('member_remember', $token, $expires, '/', '', false, true);
            setcookie('member_id', $member['member_id'], $expires, '/', '', false, true);
        }

        // Check if this is an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $response['success'] = true;
            $response['message'] = 'लॉगिन सफल!';
            $response['redirect'] = '../member/index.php';
            echo json_encode($response);
        } else {
            // Form submission - redirect to member dashboard
            header('Location: ../member/index.php');
            exit;
        }

    } else {
        throw new Exception('Invalid request method');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(400);
        echo json_encode($response);
    } else {
        // Form submission - redirect back with error
        // Using pages/ since this is in api/ folder
        $_SESSION['login_message'] = $e->getMessage();
        $_SESSION['login_message_type'] = 'error';
        header('Location: ../pages/login.php');
        exit;
    }
} finally {
    // Close database connection
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>
