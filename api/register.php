<?php
// Member Registration API
header('Content-Type: application/json');

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

// Error logging function
function logError($message) {
    $logFile = '../logs/register_errors.log';
    if (!is_dir('../logs')) {
        mkdir('../logs', 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Log received data for debugging
logError('=== New Registration Request ===');
logError('Method: ' . $_SERVER['REQUEST_METHOD']);
logError('POST data received: ' . json_encode($_POST));


// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'केवल POST request स्वीकृत है';
    echo json_encode($response);
    exit;
}

try {
    // Database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        $error = 'डेटाबेस कनेक्शन विफल: ' . $conn->connect_error;
        logError($error);
        throw new Exception($error);
    }
    
    $conn->set_charset("utf8mb4");

    // Get form data
    $paymentConfirm = isset($_POST['paymentConfirm']) ? 1 : 0;
    $utrNumber = isset($_POST['utrNumber']) ? trim($_POST['utrNumber']) : '';
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $aadharNumber = isset($_POST['aadharNumber']) ? preg_replace('/\s+/', '', trim($_POST['aadharNumber'])) : '';
    $fatherName = isset($_POST['fatherName']) ? trim($_POST['fatherName']) : '';
    $dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
    $mobile = isset($_POST['mobile']) ? preg_replace('/\s+/', '', trim($_POST['mobile'])) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $occupation = isset($_POST['occupation']) ? trim($_POST['occupation']) : '';
    $officeName = isset($_POST['officeName']) ? trim($_POST['officeName']) : '';
    $officeAddress = isset($_POST['officeAddress']) ? trim($_POST['officeAddress']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $manualState = isset($_POST['manualStateName']) ? trim($_POST['manualStateName']) : '';
    $district = isset($_POST['district']) ? trim($_POST['district']) : '';
    $manualDistrict = isset($_POST['manualDistrict']) ? trim($_POST['manualDistrict']) : '';
    $block = isset($_POST['block']) ? trim($_POST['block']) : '';
    $manualBlock = isset($_POST['manualBlock']) ? trim($_POST['manualBlock']) : '';
    $permanentAddress = isset($_POST['permanentAddress']) ? trim($_POST['permanentAddress']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $terms = isset($_POST['terms']) ? 1 : 0;
    
    // New fields - Nominee and Referrer
    $nomineeName = isset($_POST['nomineeName']) ? trim($_POST['nomineeName']) : '';
    $nomineeRelation = isset($_POST['nomineeRelation']) ? trim($_POST['nomineeRelation']) : '';
    $nomineeMobile = isset($_POST['nomineeMobile']) ? preg_replace('/\s+/', '', trim($_POST['nomineeMobile'])) : '';
    $nomineeAadhar = isset($_POST['nomineeAadhar']) ? preg_replace('/\s+/', '', trim($_POST['nomineeAadhar'])) : '';
    $referrerMemberId = isset($_POST['referrerMemberId']) ? trim($_POST['referrerMemberId']) : '';

    // Field to Tab mapping for error navigation
    $fieldTabMap = [
        'paymentConfirm' => 'payment',
        'utrNumber' => 'payment',
        'fullName' => 'personal',
        'aadharNumber' => 'personal',
        'fatherName' => 'personal',
        'dob' => 'personal',
        'mobile' => 'personal',
        'gender' => 'personal',
        'occupation' => 'additional',
        'officeName' => 'additional',
        'officeAddress' => 'additional',
        'nomineeName' => 'additional',
        'nomineeRelation' => 'additional',
        'nomineeMobile' => 'additional',
        'nomineeAadhar' => 'additional',
        'state' => 'location',
        'district' => 'location',
        'block' => 'location',
        'permanentAddress' => 'location',
        'email' => 'account',
        'password' => 'account',
        'terms' => 'account',
        'referrerMemberId' => 'account',
        'paymentReceipt' => 'account'
    ];

    // Validation
    $errors = [];

    // Payment confirmation validation
    if (!$paymentConfirm) {
        $errors['paymentConfirm'] = 'भुगतान की पुष्टि आवश्यक है';
    }

    // UTR Number validation
    if (empty($utrNumber)) {
        $errors['utrNumber'] = 'UTR नंबर आवश्यक है';
    } else {
        $utrClean = strtoupper(preg_replace('/\s+/', '', $utrNumber));
        if (!preg_match('/^[A-Z0-9]{12,22}$/', $utrClean)) {
            $errors['utrNumber'] = 'वैध UTR नंबर (12-22 वर्णाक्षर) दर्ज करें';
        } else {
            $utrNumber = $utrClean; // Update to uppercase
        }
    }

    // Full name validation
    if (empty($fullName)) {
        $errors['fullName'] = 'पूरा नाम आवश्यक है';
    } elseif (strlen($fullName) < 3) {
        $errors['fullName'] = 'नाम कम से कम 3 वर्ण होना चाहिए';
    }

    // Aadhar number validation
    if (empty($aadharNumber)) {
        $errors['aadharNumber'] = 'आधार संख्या आवश्यक है';
    } elseif (!preg_match('/^\d{12}$/', $aadharNumber)) {
        $errors['aadharNumber'] = 'आधार संख्या 12 अंकों की होनी चाहिए';
    }

    // Check if aadhar already exists
    if (!empty($aadharNumber) && preg_match('/^\d{12}$/', $aadharNumber)) {
        $checkStmt = $conn->prepare("SELECT member_id FROM members WHERE aadhar_number = ?");
        $checkStmt->bind_param("s", $aadharNumber);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $errors['aadharNumber'] = 'यह आधार नंबर पहले से रजिस्ट्रित है';
        }
        $checkStmt->close();
    }

    // Father/Husband name validation
    if (empty($fatherName)) {
        $errors['fatherName'] = 'पिता/पति का नाम आवश्यक है';
    } elseif (strlen($fatherName) < 2) {
        $errors['fatherName'] = 'नाम कम से कम 2 वर्ण होना चाहिए';
    }

    // Date of birth validation
    if (empty($dob)) {
        $errors['dob'] = 'जन्म तारीख आवश्यक है';
    } else {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $birthYear = $dobDate->format('Y');
        
        if ($dobDate >= $today) {
            $errors['dob'] = 'जन्म तारीख आज से पहले की होनी चाहिए';
        } elseif ($birthYear < 1950) {
            $errors['dob'] = 'जन्म तारीख 1950 के बाद की होनी चाहिए';
        }
    }

    // Mobile validation
    if (empty($mobile)) {
        $errors['mobile'] = 'मोबाइल नंबर आवश्यक है';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors['mobile'] = '10 अंकीय मोबाइल नंबर (6-9 से शुरू) दर्ज करें';
    }

    // Mobile number can be shared by multiple users (family members, etc.)
    // No duplicate check needed

    // Gender validation
    if (empty($gender)) {
        $errors['gender'] = 'लिंग का चयन आवश्यक है';
    }

    // Occupation validation
    if (empty($occupation)) {
        $errors['occupation'] = 'व्यवसाय का चयन आवश्यक है';
    }

    // State validation
    $finalState = (!empty($manualState)) ? $manualState : $state;
    if (empty($finalState)) {
        $errors['state'] = 'राज्य दर्ज करें या चुनें';
    }

    // District validation
    $finalDistrict = (!empty($manualDistrict)) ? $manualDistrict : $district;
    if (empty($finalDistrict)) {
        $errors['district'] = 'जिला दर्ज करें या चुनें';
    }

    // Block validation
    $finalBlock = (!empty($manualBlock)) ? $manualBlock : $block;
    if (empty($finalBlock)) {
        $errors['block'] = 'ब्लॉक दर्ज करें या चुनें';
    }

    // Email validation
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'वैध ईमेल पता दर्ज करें';
        }
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = 'पासवर्ड आवश्यक है';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'पासवर्ड कम से कम 6 वर्ण होना चाहिए';
    }

    // Terms acceptance validation
    if (!$terms) {
        $errors['terms'] = 'नियम और शर्तों को स्वीकार करें';
    }

    // ===== NEW VALIDATION: Nominee Fields =====
    // Nominee name validation
    if (empty($nomineeName)) {
        $errors['nomineeName'] = 'नामांकित व्यक्ति का नाम आवश्यक है';
    } elseif (strlen($nomineeName) < 3) {
        $errors['nomineeName'] = 'नाम कम से कम 3 वर्ण होना चाहिए';
    }

    // Nominee relation validation
    if (empty($nomineeRelation)) {
        $errors['nomineeRelation'] = 'संबंध का चयन आवश्यक है';
    }

    // Nominee mobile validation (optional, but if provided should be valid)
    if (!empty($nomineeMobile)) {
        if (!preg_match('/^[6-9]\d{9}$/', $nomineeMobile)) {
            $errors['nomineeMobile'] = '10 अंकीय मोबाइल नंबर (6-9 से शुरू) दर्ज करें';
        }
    }

    // Nominee aadhar validation (optional, but if provided should be valid)
    if (!empty($nomineeAadhar)) {
        if (!preg_match('/^\d{12}$/', $nomineeAadhar)) {
            $errors['nomineeAadhar'] = 'वैध आधार संख्या (12 अंक) दर्ज करें';
        }
    }

    // Referrer Member ID validation (optional but if provided, should check if member exists)
    if (!empty($referrerMemberId)) {
        // Remove spaces and convert to standard format
        $referrerMemberId = preg_replace('/\s+/', '', $referrerMemberId);
        
        // Check if referrer member exists (optional validation - doesn't fail if not found)
        // This can be added later when needed, for now just validate format
        if (!preg_match('/^\d{1,8}$/', $referrerMemberId)) {
            $errors['referrerMemberId'] = 'वैध Member ID दर्ज करें';
        }
    }
    // ===== END NEW VALIDATION =====



    // If there are errors, return them with tab information
    if (!empty($errors)) {
        http_response_code(400);
        
        // Add tab information to each error
        $errorsWithTabs = [];
        foreach ($errors as $field => $message) {
            $errorsWithTabs[$field] = [
                'message' => $message,
                'tab' => $fieldTabMap[$field] ?? 'payment'
            ];
        }
        
        // Get the first error's tab to redirect to
        $firstErrorTab = reset($errorsWithTabs)['tab'] ?? 'payment';
        
        $response['message'] = 'कृपया सभी त्रुटियों को ठीक करें';
        $response['errors'] = $errorsWithTabs;
        $response['firstErrorTab'] = $firstErrorTab;
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // Generate member_id and login_id based on last 8 digits of aadhar
    $memberId = substr($aadharNumber, -8);
    $loginId = substr($aadharNumber, -8);

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Set default status
    $status = 1;

    // Select next poll_option dynamically based on minimum current count
    $pollOption = null;
    $pollOptionQuery = "
        SELECT p.poll AS poll_option, COUNT(m.member_id) AS member_count
        FROM (SELECT DISTINCT poll FROM poll) p
        LEFT JOIN members m ON m.poll_option = p.poll AND m.status = 1
        GROUP BY p.poll
        ORDER BY member_count ASC, p.poll ASC
        LIMIT 1
    ";

    $pollOptionResult = $conn->query($pollOptionQuery);
    if ($pollOptionResult) {
        if ($pollOptionResult->num_rows > 0) {
            $row = $pollOptionResult->fetch_assoc();
            $pollOption = $row['poll_option'];
        }
    } else {
        $error = 'Poll option selection failed: ' . $conn->error;
        logError($error);
        throw new Exception($error);
    }

    // Prepare SQL statement
    $insertColumns = [
        'member_id', 'login_id', 'password', 'full_name', 'aadhar_number',
        'father_husband_name', 'date_of_birth', 'mobile_number', 'gender',
        'occupation', 'office_name', 'office_address', 'state', 'district',
        'block', 'permanent_address', 'email', 'utr_number', 'payment_verified',
        'nominee_name', 'nominee_relation', 'nominee_mobile', 'nominee_aadhar',
        'referrer_member_id', 'poll_option', 'status'
    ];

    $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
    $sql = "INSERT INTO members (" . implode(', ', $insertColumns) . ") VALUES ($placeholders)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $error = 'SQL तैयारी विफल: ' . $conn->error . ' -- Query: ' . $sql;
        logError($error);
        throw new Exception($error);
    }

    // Bind parameters (all 26 fields now)
    $stmt->bind_param(
        str_repeat('s', 18) . 'i' . str_repeat('s', 6) . 'i',
        $memberId,
        $loginId,
        $hashedPassword,
        $fullName,
        $aadharNumber,
        $fatherName,
        $dob,
        $mobile,
        $gender,
        $occupation,
        $officeName,
        $officeAddress,
        $finalState,
        $finalDistrict,
        $finalBlock,
        $permanentAddress,
        $email,
        $utrNumber,
        $paymentConfirm,
        $nomineeName,
        $nomineeRelation,
        $nomineeMobile,
        $nomineeAadhar,
        $referrerMemberId,
        $pollOption,
        $status
    );

    // Execute statement
    if (!$stmt->execute()) {
        $error = 'सदस्य डेटा सहेजने में विफल: ' . $stmt->error;
        logError($error);
        logError('Debug - Variables: ' . json_encode([
            'memberId' => $memberId,
            'aadharNumber' => $aadharNumber,
            'fullName' => $fullName,
            'mobile' => $mobile
        ]));
        throw new Exception($error);
    }

    $stmt->close();

    // Success response
    http_response_code(201);
    $response['success'] = true;
    $response['message'] = 'रजिस्ट्रेशन सफल है। आपका सदस्य ID: ' . $memberId;
    
    $response['data'] = [
        'member_id' => $memberId,
        'login_id' => $loginId,
        'aadhar_number' => substr($aadharNumber, -4, 4),  // Show only last 4 digits for security
        'member_name' => $fullName,
        'receipt_download' => 'generate_receipt.php?member_id=' . $memberId . '&utr=' . $utrNumber
    ];

    echo json_encode($response);
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $errorMsg = $e->getMessage();
    logError('Exception: ' . $errorMsg);
    logError('Stack: ' . $e->getTraceAsString());
    $response['message'] = 'त्रुटि: ' . $errorMsg;
    echo json_encode($response);
    exit;
}
?>
