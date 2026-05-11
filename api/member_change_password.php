<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/config.php';

/*
|--------------------------------------------------------------------------
| Check Login Session
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'कृपया पहले लॉगिन करें।'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Allow Only POST Request
    |--------------------------------------------------------------------------
    */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('केवल POST अनुरोध स्वीकार किया जाता है।');
    }

    /*
    |--------------------------------------------------------------------------
    | Get JSON Input
    |--------------------------------------------------------------------------
    */
    $rawInput = file_get_contents('php://input');

    if (empty($rawInput)) {
        throw new Exception('अनुरोध डेटा प्राप्त नहीं हुआ।');
    }

    $input = json_decode($rawInput, true);

    if (!is_array($input)) {
        throw new Exception('अमान्य JSON डेटा।');
    }

    /*
    |--------------------------------------------------------------------------
    | Get Password Fields
    |--------------------------------------------------------------------------
    */
    $newPassword = trim($input['new_password'] ?? '');
    $confirmPassword = trim($input['confirm_password'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    if (empty($newPassword) || empty($confirmPassword)) {
        throw new Exception('दोनों पासवर्ड फ़ील्ड आवश्यक हैं।');
    }

    if ($newPassword !== $confirmPassword) {
        throw new Exception('पासवर्ड और पुष्टि पासवर्ड समान होने चाहिए।');
    }

    if (strlen($newPassword) < 6) {
        throw new Exception('पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।');
    }

    /*
    |--------------------------------------------------------------------------
    | Database Connection Check
    |--------------------------------------------------------------------------
    */
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('डेटाबेस कनेक्शन उपलब्ध नहीं है।');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */
    $memberId = $_SESSION['member_id'];

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    if ($hashedPassword === false) {
        throw new Exception('पासवर्ड सुरक्षित नहीं किया जा सका।');
    }

    $stmt = $pdo->prepare("
        UPDATE members 
        SET password = ? 
        WHERE member_id = ?
    ");

    $stmt->execute([
        $hashedPassword,
        $memberId
    ]);

    /*
    |--------------------------------------------------------------------------
    | Check Update Success
    |--------------------------------------------------------------------------
    */
    if ($stmt->rowCount() <= 0) {
        throw new Exception('पासवर्ड अपडेट नहीं हो सका।');
    }

    /*
    |--------------------------------------------------------------------------
    | Success Response
    |--------------------------------------------------------------------------
    */
    echo json_encode([
        'success' => true,
        'message' => 'पासवर्ड सफलतापूर्वक अपडेट किया गया।'
    ]);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}