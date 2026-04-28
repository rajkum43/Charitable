<?php
// Membership Requirements Admin API
// Admin के लिए membership requirements को view/update करने के लिए

header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/MembershipValidator.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Check if method is GET or POST
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // View all requirements
    try {
        $requirements = MembershipValidator::getAllRequirements();
        
        $response['success'] = true;
        $response['message'] = 'Membership requirements fetched successfully';
        $response['data'] = $requirements;
        http_response_code(200);
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = 'त्रुटि: ' . $e->getMessage();
    }
}
elseif ($method === 'POST') {
    // Update requirement (Admin only)
    try {
        // In production, add proper authentication check here
        // if (!isAdmin()) {
        //     http_response_code(403);
        //     $response['message'] = 'केवल Admin इसे access कर सकते हैं';
        //     echo json_encode($response);
        //     exit;
        // }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['scheme']) || !isset($data['days'])) {
            http_response_code(400);
            $response['message'] = 'scheme और days दोनों आवश्यक हैं';
            echo json_encode($response);
            exit;
        }
        
        $scheme = $data['scheme'];
        $days = (int)$data['days'];
        
        // Validate
        if ($days < 0 || $days > 3650) {
            http_response_code(400);
            $response['message'] = 'Days 0 से 3650 के बीच होने चाहिए';
            echo json_encode($response);
            exit;
        }
        
        // Update
        if (MembershipValidator::updateRequirement($scheme, $days)) {
            $response['success'] = true;
            $response['message'] = "$scheme के लिए requirement को $days दिन में update किया गया";
            $response['data'] = [
                'scheme' => $scheme,
                'days' => $days
            ];
            http_response_code(200);
        } else {
            http_response_code(500);
            $response['message'] = 'Update करने में विफल';
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = 'त्रुटि: ' . $e->getMessage();
    }
}
else {
    http_response_code(405);
    $response['message'] = 'Method not allowed';
}

echo json_encode($response);
?>
