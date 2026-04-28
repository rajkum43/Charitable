<?php
// Member Lookup API for Beti Vivah Aavedan
header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/MembershipValidator.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'डेटाबेस कनेक्शन विफल'
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Only GET requests allowed for lookup
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    $response['message'] = 'केवल GET request स्वीकृत है';
    echo json_encode($response);
    exit;
}

try {
    $member_id = isset($_GET['member_id']) ? trim($_GET['member_id']) : '';
    $mobile = isset($_GET['mobile']) ? trim($_GET['mobile']) : '';

    // Validation - at least one field required
    if (empty($member_id) && empty($mobile)) {
        http_response_code(400);
        $response['message'] = 'Member ID या Mobile नंबर में से कम से कम एक आवश्यक है';
        echo json_encode($response);
        exit;
    }

    // Validate Mobile format if provided
    if (!empty($mobile) && !preg_match('/^[6-9]\d{9}$/', $mobile)) {
        http_response_code(400);
        $response['message'] = 'मोबाइल नंबर 10 अंकों का होना चाहिए';
        echo json_encode($response);
        exit;
    }

    // Build query based on available parameters
    $query = "
        SELECT 
            member_id, 
            login_id, 
            full_name, 
            mobile_number, 
            aadhar_number, 
            father_husband_name,
            date_of_birth,
            gender,
            permanent_address,
            district,
            block,
            state,
            created_at
        FROM members 
        WHERE 1=1
    ";
    
    $params = [];
    $types = '';

    // Add Member ID condition if provided
    if (!empty($member_id)) {
        $query .= " AND (member_id = ? OR login_id = ?)";
        $params[] = $member_id;
        $params[] = $member_id;
        $types .= 'ss';
    }

    // Add Mobile condition if provided
    if (!empty($mobile)) {
        $query .= " AND mobile_number = ?";
        $params[] = $mobile;
        $types .= 's';
    }

    $query .= " LIMIT 1";

    // Check if member exists
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters dynamically
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        $response['message'] = 'सदस्य नहीं मिला। Member ID या Mobile नंबर सत्यापित करें';
        echo json_encode($response);
        exit;
    }

    $member = $result->fetch_assoc();

    // Check membership eligibility using validator
    $scheme = isset($_GET['scheme']) ? $_GET['scheme'] : 'beti_vivah_aavedan';
    $eligibility = MembershipValidator::checkEligibility($member['created_at'], $scheme);

    if (!$eligibility['eligible']) {
        http_response_code(403);
        $response['message'] = $eligibility['message'];
        $response['eligibility'] = $eligibility;
        echo json_encode($response);
        exit;
    }

    // Member is valid
    $response['success'] = true;
    $response['message'] = 'सदस्य सत्यापित';
    $response['data'] = [
        'member_id' => $member['member_id'],
        'login_id' => $member['login_id'],
        'full_name' => $member['full_name'],
        'mobile_number' => $member['mobile_number'],
        'aadhar_number' => $member['aadhar_number'],
        'father_name' => $member['father_husband_name'],
        'dob' => $member['date_of_birth'],
        'gender' => $member['gender'],
        'address' => $member['permanent_address'],
        'district' => $member['district'],
        'block' => $member['block'],
        'state' => $member['state']
    ];
    $response['eligibility'] = [
        'days_passed' => $eligibility['days_passed'],
        'requirement_days' => $eligibility['requirement_days']
    ];
    http_response_code(200);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'त्रुटि: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
