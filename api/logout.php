<?php
// Root API: Logout
header('Content-Type: application/json');
session_start();

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

try {
    // Destroy session
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();

    // Detect base URL dynamically
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_path = dirname(dirname($script_name)); // Remove /api from path
    
    if ($host === 'localhost') {
        $protocol = 'http';
        $base_url = $protocol . '://' . $host . $base_path;
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base_url = $protocol . '://' . $host . $base_path;
    }
    
    $login_url = $base_url . '/pages/login.php';

    $response['success'] = true;
    $response['message'] = 'आप सफलतापूर्वक लॉगआउट हो गए';
    $response['redirect'] = $login_url;

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'लॉगआउट विफल: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>