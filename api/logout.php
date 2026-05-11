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

    $response['success'] = true;
    $response['message'] = 'आप सफलतापूर्वक लॉगआउट हो गए';
    $response['redirect'] = '../pages/login.php';

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'लॉगआउट विफल: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>