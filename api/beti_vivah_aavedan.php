<?php
// API for Beti Vivah Aavedan Form Submission
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';

// Initialize responses
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Get JSON or POST data based on content type
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($content_type, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

// Determine member ID from session or form submission
$member_id = null;

// First, check if user is logged in
if (isset($_SESSION['member_id'])) {
    $member_id = $_SESSION['member_id'];
} else if (isset($data['hidden_member_id']) && !empty($data['hidden_member_id'])) {
    // Non-logged-in user submitting with verified member ID (from form field)
    $member_id = trim($data['hidden_member_id']);
    
    // Verify this member exists in database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'डेटाबेस कनेक्शन विफल']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT member_id FROM members WHERE member_id = ? OR login_id = ? LIMIT 1");
    $stmt->bind_param('ss', $member_id, $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'अमान्य सदस्य ID']);
        exit;
    }
    $conn->close();
} else if (isset($data['member_id']) && !empty($data['member_id'])) {
    // Non-logged-in user submitting with member ID (appended by JavaScript)
    $member_id = trim($data['member_id']);
    
    // Verify this member exists in database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'डेटाबेस कनेक्शन विफल']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT member_id FROM members WHERE member_id = ? OR login_id = ? LIMIT 1");
    $stmt->bind_param('ss', $member_id, $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'अमान्य सदस्य ID']);
        exit;
    }
    $conn->close();
} else {
    // No authentication found
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'कृपया पहले सदस्य ID सत्यापित करें']);
    exit;
}

// Validate member ID was determined
if (empty($member_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

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
    $logFile = '../logs/beti_vivah_errors.log';
    if (!is_dir('../logs')) {
        mkdir('../logs', 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

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
    // Debug logging - log all received fields
    $received_fields = array_keys($_POST);
    logError("Form submission received with fields: " . json_encode($received_fields));
    logError("Number of POST fields: " . count($_POST));
    
    // Validate required fields (using snake_case to match form field names)
    $required_fields = [
        'member_name', 'member_id_display',
        'bride_name', 'bride_dob', 'bride_health',
        'groom_name', 'groom_dob', 'groom_occupation', 'groom_father_name',
        'wedding_date',
        'family_income', 'family_members', 'member_address',
        'ifsc_code', 'bank_name', 'branch_name', 'account_number', 'account_holder_name'
    ];

    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        http_response_code(400);
        $response['message'] = 'कृपया सभी आवश्यक फील्ड भरें: ' . implode(', ', $missing_fields);
        $response['debug'] = [
            'missing_fields' => $missing_fields,
            'received_fields' => array_keys($_POST)
        ];
        logError("Missing fields: " . json_encode($missing_fields));
        echo json_encode($response);
        exit;
    }

    // Get member and bride details (using snake_case field names from form)
    $member_id = htmlspecialchars(trim($_POST['member_id_display']));
    $member_name = htmlspecialchars(trim($_POST['member_name']));
    $member_father = htmlspecialchars(trim($_POST['member_name'])); // fallback to member_name if father not provided
    
    $bride_name = htmlspecialchars(trim($_POST['bride_name']));
    $bride_dob = $_POST['bride_dob'];
    $bride_aadhar = !empty($_POST['bride_aadhar']) ? htmlspecialchars(trim($_POST['bride_aadhar'])) : '';
    $bride_education = !empty($_POST['bride_education']) ? htmlspecialchars(trim($_POST['bride_education'])) : '';
    $bride_health = htmlspecialchars(trim($_POST['bride_health']));

    // Family details
    $family_income = (int)$_POST['family_income'];
    $family_members = (int)$_POST['family_members'];
    $address = htmlspecialchars(trim($_POST['member_address']));
    $district = !empty($_POST['district']) ? htmlspecialchars(trim($_POST['district'])) : 'Unknown';
    $block = !empty($_POST['block']) ? htmlspecialchars(trim($_POST['block'])) : 'Unknown';
    $city = !empty($_POST['city']) ? htmlspecialchars(trim($_POST['city'])) : 'Unknown';
    $state = !empty($_POST['state']) ? htmlspecialchars(trim($_POST['state'])) : 'Unknown';

    // Groom details
    $groom_name = htmlspecialchars(trim($_POST['groom_name']));
    $groom_dob = $_POST['groom_dob'];
    $groom_father_name = htmlspecialchars(trim($_POST['groom_father_name']));
    $wedding_date = $_POST['wedding_date'];
    
    // Calculate groom age if groom_age not provided
    if (!empty($_POST['groom_age'])) {
        $groom_age = (int)$_POST['groom_age'];
    } else {
        // Calculate from date of birth
        $birth_date = new DateTime($_POST['groom_dob']);
        $today = new DateTime();
        $groom_age = $today->diff($birth_date)->y;
    }
    
    $groom_occupation = htmlspecialchars(trim($_POST['groom_occupation']));
    $groom_education = !empty($_POST['groom_education']) ? htmlspecialchars(trim($_POST['groom_education'])) : '';

    // Bank details
    $ifsc_code = htmlspecialchars(trim($_POST['ifsc_code']));
    $bank_name = htmlspecialchars(trim($_POST['bank_name']));
    $branch_name = htmlspecialchars(trim($_POST['branch_name']));
    $account_number = htmlspecialchars(trim($_POST['account_number']));
    $account_holder_name = htmlspecialchars(trim($_POST['account_holder_name']));
    $upi_id = !empty($_POST['upi_id']) ? htmlspecialchars(trim($_POST['upi_id'])) : '';

    // ========== COMPREHENSIVE DATA VALIDATION ==========

    // 1. Age Validation with Exception Handling
    try {
        $bride_birth_date = new DateTime($bride_dob);
        $groom_birth_date = new DateTime($groom_dob);
        $wedding_date_obj = new DateTime($wedding_date);
        $today = new DateTime();

        // Calculate ages
        $bride_age = $today->diff($bride_birth_date)->y;
        if (empty($_POST['groom_age'])) {
            $groom_age = $today->diff($groom_birth_date)->y;
        }

        // Bride age validation (>= 18)
        if ($bride_age < 18) {
            http_response_code(400);
            $response['message'] = 'दुल्हन की आयु 18 वर्ष या उससे अधिक होनी चाहिए';
            $response['debug'] = ['bride_age' => $bride_age, 'bride_dob' => $bride_dob];
            echo json_encode($response);
            exit;
        }

        // Groom age validation (>= 18)
        if ($groom_age < 18) {
            http_response_code(400);
            $response['message'] = 'दूल्हे की आयु 18 वर्ष या उससे अधिक होनी चाहिए';
            $response['debug'] = ['groom_age' => $groom_age, 'groom_dob' => $groom_dob];
            echo json_encode($response);
            exit;
        }

        // Wedding date must be in future
        if ($wedding_date_obj <= $today) {
            http_response_code(400);
            $response['message'] = 'विवाह की तारीख आज से आगे की होनी चाहिए';
            $response['debug'] = ['wedding_date' => $wedding_date, 'today' => $today->format('Y-m-d')];
            echo json_encode($response);
            exit;
        }

    } catch (Exception $e) {
        logError("DateTime validation error: " . $e->getMessage() . " | bride_dob: " . $bride_dob . " | groom_dob: " . $groom_dob . " | wedding_date: " . $wedding_date);
        http_response_code(400);
        $response['message'] = 'तारीख का प्रारूप अमान्य है। कृपया सही तारीख दर्ज करें।';
        $response['debug'] = [
            'error' => $e->getMessage(),
            'bride_dob' => $bride_dob,
            'groom_dob' => $groom_dob,
            'wedding_date' => $wedding_date
        ];
        echo json_encode($response);
        exit;
    }

    // 2. Income and Family Members Validation
    if ($family_income < 0) {
        http_response_code(400);
        $response['message'] = 'वार्षिक पारिवारिक आय नकारात्मक नहीं हो सकती';
        echo json_encode($response);
        exit;
    }

    if ($family_members < 1) {
        http_response_code(400);
        $response['message'] = 'परिवार के सदस्यों की संख्या कम से कम 1 होनी चाहिए';
        echo json_encode($response);
        exit;
    }

    // 3. Account Number Format Validation (usually 9-18 digits)
    if (!preg_match('/^\d{9,18}$/', $account_number)) {
        http_response_code(400);
        $response['message'] = 'खाता संख्या 9 से 18 अंकों के बीच होनी चाहिए';
        echo json_encode($response);
        exit;
    }

    // 4. IFSC Code Validation (Format: ABCD0123456)
    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc_code)) {
        http_response_code(400);
        $response['message'] = 'IFSC कोड का प्रारूप अमान्य है। उदाहरण: SBIN0001234';
        echo json_encode($response);
        exit;
    }

    // 5. UPI ID Format Validation (if provided)
    if (!empty($upi_id)) {
        if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+$/', $upi_id)) {
            http_response_code(400);
            $response['message'] = 'UPI ID का प्रारूप अमान्य है। उदाहरण: name@okhdfcbank';
            echo json_encode($response);
            exit;
        }
    }

    // 6. Check for Duplicate Application
    $duplicate_check_stmt = $conn->prepare(
        "SELECT id FROM beti_vivah_aavedan 
         WHERE member_id = ? AND (status = 'Pending' OR status = 'Under Review')"
    );
    
    if ($duplicate_check_stmt) {
        $duplicate_check_stmt->bind_param('s', $member_id);
        $duplicate_check_stmt->execute();
        $duplicate_result = $duplicate_check_stmt->get_result();

        if ($duplicate_result->num_rows > 0) {
            http_response_code(409);
            $response['message'] = 'आपका आवेदन पहले से लंबित है। कृपया पहले आवेदन के परिणाम की प्रतीक्षा करें।';
            echo json_encode($response);
            exit;
        }
        $duplicate_check_stmt->close();
    }

    // ========== END VALIDATION ==========

    // Handle file uploads
    $upload_dir = '../uploads/beti_vivah/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $uploaded_files = [];
    $file_fields = ['aadhar_proof', 'address_proof', 'income_proof', 'marriage_certificate'];
    $required_files = ['aadhar_proof', 'address_proof', 'income_proof', 'marriage_certificate'];

    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$field]['tmp_name'];
            $file_name = $_FILES[$field]['name'];
            $file_size = $_FILES[$field]['size'];

            // Validate file size (300KB max)
            if ($file_size > 300 * 1024) {
                http_response_code(400);
                $response['message'] = $field . ' फाइल 300KB से बड़ी नहीं होनी चाहिए';
                echo json_encode($response);
                exit;
            }

            // Validate file type
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_types)) {
                http_response_code(400);
                $response['message'] = 'केवल PDF, JPG या PNG फाइलें अनुमति हैं';
                echo json_encode($response);
                exit;
            }

            // Generate unique filename
            $new_file_name = $field . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $uploaded_files[$field] = $new_file_name;
            } else {
                logError("File upload failed for $field");
                http_response_code(500);
                $response['message'] = 'फाइल अपलोड विफल';
                echo json_encode($response);
                exit;
            }
        }
    }

    // Validate that all required files are uploaded
    foreach ($required_files as $required_file) {
        if (!isset($uploaded_files[$required_file])) {
            http_response_code(400);
            $response['message'] = "कृपया सभी आवश्यक दस्तावेज़ अपलोड करें: $required_file गुम है";
            echo json_encode($response);
            exit;
        }
    }

    // Generate Application Number
    $application_number = 'BVA' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // Check if table exists, if not create it
    $table_check = $conn->query("SHOW TABLES LIKE 'beti_vivah_aavedan'");
    if ($table_check->num_rows == 0) {
        $create_table_sql = "CREATE TABLE beti_vivah_aavedan (
            id INT PRIMARY KEY AUTO_INCREMENT,
            application_number VARCHAR(20) UNIQUE NOT NULL,
            
            member_id VARCHAR(50) NOT NULL,
            member_name VARCHAR(100) NOT NULL,
            member_father VARCHAR(100) NOT NULL,
            
            bride_name VARCHAR(100) NOT NULL,
            bride_dob DATE NOT NULL,
            bride_aadhar VARCHAR(12),
            bride_education VARCHAR(50),
            bride_health VARCHAR(50) NOT NULL,
            
            family_income INT NOT NULL,
            family_members INT NOT NULL,
            address TEXT NOT NULL,
            district VARCHAR(50),
            block VARCHAR(50),
            city VARCHAR(50) NOT NULL,
            state VARCHAR(50) NOT NULL,
            
            groom_name VARCHAR(100) NOT NULL,
            groom_dob DATE NOT NULL,
            groom_age INT NOT NULL,
            groom_father_name VARCHAR(100) NOT NULL,
            groom_occupation VARCHAR(100) NOT NULL,
            groom_education VARCHAR(50),
            wedding_date DATE NOT NULL,
            
            ifsc_code VARCHAR(11) NOT NULL,
            bank_name VARCHAR(100) NOT NULL,
            branch_name VARCHAR(100) NOT NULL,
            account_number VARCHAR(20) NOT NULL,
            account_holder_name VARCHAR(100) NOT NULL,
            upi_id VARCHAR(100),
            
            aadhar_proof VARCHAR(255),
            address_proof VARCHAR(255),
            income_proof VARCHAR(255),
            marriage_certificate VARCHAR(255),
            
            status ENUM('Pending', 'Under Review', 'Approved', 'Rejected') DEFAULT 'Pending',
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$conn->query($create_table_sql)) {
            logError("Table creation failed: " . $conn->error);
            http_response_code(500);
            $response['message'] = 'डेटाबेस टेबल बनाने में विफल';
            echo json_encode($response);
            exit;
        }
    }

    // ========== AUTO MIGRATION: Add missing columns ==========
    $missing_columns = [
        'groom_father_name' => "ALTER TABLE beti_vivah_aavedan ADD COLUMN groom_father_name VARCHAR(100) NOT NULL AFTER groom_age",
        'wedding_date' => "ALTER TABLE beti_vivah_aavedan ADD COLUMN wedding_date DATE NOT NULL AFTER groom_education",
        'account_holder_name' => "ALTER TABLE beti_vivah_aavedan ADD COLUMN account_holder_name VARCHAR(100) NOT NULL AFTER account_number"
    ];

    foreach ($missing_columns as $col_name => $alter_query) {
        $check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_SCHEMA='" . DB_NAME . "' 
                        AND TABLE_NAME='beti_vivah_aavedan' 
                        AND COLUMN_NAME='" . $col_name . "'";
        
        $result = $conn->query($check_query);
        
        if ($result && $result->num_rows === 0) {
            if (!$conn->query($alter_query)) {
                logError("Failed to add column $col_name: " . $conn->error);
            } else {
                logError("Successfully added column: $col_name");
            }
        }
    }
    // ========== END AUTO MIGRATION ==========

    // Prepare file upload variables (bind_param requires actual variables, not expressions)
    $aadhar_file = $uploaded_files['aadhar_proof'] ?? null;
    $address_file = $uploaded_files['address_proof'] ?? null;
    $income_file = $uploaded_files['income_proof'] ?? null;
    $marriage_file = $uploaded_files['marriage_certificate'] ?? null;

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO beti_vivah_aavedan (
        application_number, member_id, member_name, member_father,
        bride_name, bride_dob, bride_aadhar, bride_education, bride_health,
        family_income, family_members, address, district, block, city, state,
        groom_name, groom_dob, groom_age, groom_father_name, groom_occupation, groom_education,
        wedding_date, ifsc_code, bank_name, branch_name, account_number, account_holder_name, upi_id,
        aadhar_proof, address_proof, income_proof, marriage_certificate
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        logError("Prepare statement failed: " . $conn->error);
        http_response_code(500);
        $response['message'] = 'डेटाबेस विफल';
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param(
        'sssssssssiisssssssissssssssssssss',
        $application_number, $member_id, $member_name, $member_father,
        $bride_name, $bride_dob, $bride_aadhar, $bride_education, $bride_health,
        $family_income, $family_members, $address, $district, $block, $city, $state,
        $groom_name, $groom_dob, $groom_age, $groom_father_name, $groom_occupation, $groom_education,
        $wedding_date, $ifsc_code, $bank_name, $branch_name, $account_number, $account_holder_name, $upi_id,
        $aadhar_file, $address_file, $income_file, $marriage_file
    );

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'आवेदन सफलतापूर्वक जमा हुआ';
        $response['data'] = [
            'application_number' => $application_number,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        http_response_code(201);
    } else {
        logError("Insert failed: " . $stmt->error);
        http_response_code(500);
        $response['message'] = 'आवेदन जमा करने में विफल: ' . $stmt->error;
    }

    $stmt->close();

} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'त्रुटि: ' . $e->getMessage();
} finally {
    $conn->close();
}

echo json_encode($response);
?>
