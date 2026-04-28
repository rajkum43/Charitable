<?php
// API for Death Aavedan Form Submission
ob_start(); // Start output buffering to catch any errors
header('Content-Type: application/json');
session_start();

// Suppress error output to prevent HTML in JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Error logging function (define early)
function logError($message) {
    $logFile = '../logs/death_aavedan_errors.log';
    if (!is_dir('../logs')) {
        mkdir('../logs', 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Custom error handler to prevent HTML output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("Error [$errno]: $errstr in $errfile on line $errline");
    // Don't output error, just log it
    return true;
});

require_once '../includes/config.php';
require_once '../config/membership_requirements.php';

// Load membership requirements
$membership_requirements = require '../config/membership_requirements.php';
$min_membership_days = $membership_requirements['death_aavedan'] ?? 365;

// Initialize response
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
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'डेटाबेस कनेक्शन विफल: ' . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'केवल POST request स्वीकृत है';
    ob_end_clean();
    echo json_encode($response);
    exit;
}

// Get POST data - handle both JSON and form data
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

if (strpos($contentType, 'application/json') !== false) {
    // Handle JSON input
    $jsonData = file_get_contents('php://input');
    $postData = json_decode($jsonData, true);
    if ($postData === null) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
} else {
    // Handle form data
    $postData = $_POST;
}

try {
    // Validate required fields (using snake_case to match form field names)
    $required_fields = [
        'applicant_name', 'applicant_dob', 'applicant_relation', 'applicant_parent_name',
        'deceased_name', 'deceased_member_id', 'deceased_dob', 'deceased_age', 'death_date', 'deceased_relationship',
        'cause_of_death', 'family_income', 'family_members',
        'bank_name', 'branch_name', 'account_number', 'ifsc_code',
        'account_holder_name'
    ];

    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($postData[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        http_response_code(400);
        $error_msg = 'कृपया सभी आवश्यक फील्ड भरें: ' . implode(', ', $missing_fields);
        logError("Missing required fields: " . implode(', ', $missing_fields));
        $response['message'] = $error_msg;
        ob_end_clean();
        echo json_encode($response);
        exit;
    }

    // Get applicant details
    $applicant_name = htmlspecialchars(trim($postData['applicant_name']));
    $applicant_dob = $postData['applicant_dob'];
    $applicant_relation = htmlspecialchars(trim($postData['applicant_relation']));
    $applicant_parent_name = htmlspecialchars(trim($postData['applicant_parent_name']));
    
    // Get deceased and family details
    $deceased_name = htmlspecialchars(trim($postData['deceased_name']));
    $deceased_member_id = htmlspecialchars(trim($postData['deceased_member_id']));
    $deceased_dob = $postData['deceased_dob'];
    $deceased_age = (int)$postData['deceased_age'];
    $death_date = $postData['death_date'];
    $deceased_relationship = htmlspecialchars(trim($postData['deceased_relationship']));
    $cause_of_death = htmlspecialchars(trim($postData['cause_of_death']));

    // Family details
    $family_income = (int)$postData['family_income'];
    $family_members = (int)$postData['family_members'];

    // Bank details
    $bank_name = htmlspecialchars(trim($postData['bank_name']));
    $branch_name = htmlspecialchars(trim($postData['branch_name']));
    $account_number = htmlspecialchars(trim($postData['account_number']));
    $ifsc_code = htmlspecialchars(trim($postData['ifsc_code']));
    $account_holder_name = htmlspecialchars(trim($postData['account_holder_name']));
    $upi_id = !empty($postData['upi_id']) ? htmlspecialchars(trim($postData['upi_id'])) : '';
    $remarks = !empty($postData['remarks']) ? htmlspecialchars(trim($postData['remarks'])) : '';

    // SERVER-SIDE VALIDATIONS
    
    // VALIDATE APPLICANT DETAILS
    
    // 1. Validate applicant name
    if (empty($applicant_name)) {
        http_response_code(400);
        $response['message'] = 'आवेदक का नाम आवश्यक है';        ob_end_clean();        echo json_encode($response);
        exit;
    }
    
    // 2. Validate applicant DOB
    if (empty($applicant_dob)) {
        http_response_code(400);
        $response['message'] = 'आवेदक की जन्म तिथि आवश्यक है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 3. Validate applicant relation
    if (empty($applicant_relation) || !in_array($applicant_relation, ['पिता', 'पुत्री', 'पत्नी'])) {
        http_response_code(400);
        $response['message'] = 'संवंध (पिता/पुत्री/पत्नी) आवश्यक है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // VALIDATE DECEASED DETAILS
    
    // 1. Validate deceased member ID is provided
    if (empty($deceased_member_id)) {
        http_response_code(400);
        $response['message'] = 'मृत व्यक्ति का सदस्य ID आवश्यक है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 2. Validate deceased DOB is provided
    if (empty($deceased_dob)) {
        http_response_code(400);
        $response['message'] = 'मृत व्यक्ति की जन्म तिथि आवश्यक है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 3. Validate deceased age is between 18-60
    if ($deceased_age < 18 || $deceased_age > 60) {
        http_response_code(400);
        $response['message'] = 'मृत्यु के समय आयु 18 से 60 वर्ष के बीच होनी चाहिए (दर्ज की गई आयु: ' . $deceased_age . ')';
        logError("Invalid age: " . $deceased_age . " (must be between 18-60)");
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 4. Validate death date (must be in past or today)
    try {
        $death_date_obj = new DateTime($death_date);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($death_date_obj > $today) {
            http_response_code(400);
            $response['message'] = 'मृत्यु की तिथि भविष्य की नहीं हो सकती';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        $response['message'] = 'मृत्यु की तिथि सही प्रारूप में नहीं है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    
    // 5. Check if deceased member exists in members table
    $check_deceased_stmt = $conn->prepare("SELECT member_id, full_name, date_of_birth, created_at FROM members WHERE member_id = ?");
    $check_deceased_stmt->bind_param("s", $deceased_member_id);
    $check_deceased_stmt->execute();
    $deceased_result = $check_deceased_stmt->get_result();
    
    if ($deceased_result->num_rows == 0) {
        http_response_code(400);
        $response['message'] = 'मृत व्यक्ति का सदस्य ID डेटाबेस में नहीं मिला';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    $deceased_member = $deceased_result->fetch_assoc();
    $check_deceased_stmt->close();
    
    // 6. Validate that DOB matches the member's DOB in database
    $db_dob = $deceased_member['date_of_birth'];
    if ($db_dob !== $deceased_dob) {
        http_response_code(400);
        $response['message'] = 'दर्ज की गई जन्म तिथि डेटाबेस में दर्ज जन्म तिथि से मेल नहीं खाती';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 7. Check if member was registered for minimum required duration
    $created_at = new DateTime($deceased_member['created_at']);
    $today = new DateTime();
    $interval = $created_at->diff($today);
    $total_days = ($interval->y * 365) + ($interval->m * 30) + $interval->d;
    $years = $interval->y;
    
    if ($total_days < $min_membership_days) {
        $required_years = ceil($min_membership_days / 365);
        http_response_code(400);
        $response['message'] = 'मृत व्यक्ति कम से कम ' . $required_years . ' वर्ष का सदस्य होना चाहिए। वर्तमान में सदस्यता ' . $years . ' वर्ष, ' . $interval->m . ' महीने और ' . $interval->d . ' दिन पुरानी है।';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 8. Validate account number format (9-18 digits)
    if (!preg_match('/^\d{9,18}$/', $account_number)) {
        http_response_code(400);
        $response['message'] = 'खाता संख्या 9 से 18 अंकों की होनी चाहिए। दर्ज की गई संख्या की लंबाई: ' . strlen($account_number);
        logError("Invalid account number format: " . $account_number . " (length: " . strlen($account_number) . ")");
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 9. Validate IFSC format (standard: ABCD0123456)
    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc_code)) {
        http_response_code(400);
        $response['message'] = 'IFSC कोड सही प्रारूप में नहीं है। उदाहरण: SBIN0001234। दर्ज: ' . $ifsc_code;
        logError("Invalid IFSC format: " . $ifsc_code);
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 10. Validate family members >= 1
    if ($family_members < 1) {
        http_response_code(400);
        $response['message'] = 'परिवार के सदस्यों की संख्या कम से कम 1 होनी चाहिए';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 12. Validate family income >= 0
    if ($family_income < 0) {
        http_response_code(400);
        $response['message'] = 'पारिवारिक आय नकारात्मक नहीं हो सकती';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // 13. Validate UPI format if provided (optional field)
    if (!empty($upi_id)) {
        if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z]{3,}$/', $upi_id)) {
            http_response_code(400);
            $response['message'] = 'UPI ID सही प्रारूप में नहीं है (उदाहरण: yourname@upi या yourname@okaxis)';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
    }
    
    // 14. Check if duplicate application exists for same deceased member
    $check_duplicate_stmt = $conn->prepare("SELECT id FROM death_aavedan WHERE deceased_member_id = ? AND status IN ('Pending', 'Under Review')");
    $check_duplicate_stmt->bind_param("s", $deceased_member_id);
    $check_duplicate_stmt->execute();
    $duplicate_result = $check_duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        http_response_code(400);
        $response['message'] = 'इस मृत व्यक्ति के लिए पहले से एक आवेदन लंबित है';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    $check_duplicate_stmt->close();

    // Generate Application Number
    $application_number = 'DA' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // Fetch the current member details (applicant's details)
    $member_details_stmt = $conn->prepare("SELECT full_name, date_of_birth, mobile_number, permanent_address, father_husband_name FROM members WHERE member_id = ?");
    
    if (!$member_details_stmt) {
        logError("Member details prepare failed: " . $conn->error);
        http_response_code(500);
        $response['message'] = 'डेटाबेस त्रुटि: ' . $conn->error;
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    $member_details_stmt->bind_param("s", $member_id);
    
    if (!$member_details_stmt->execute()) {
        logError("Member details execute failed: " . $member_details_stmt->error);
        http_response_code(500);
        $response['message'] = 'डेटाबेस त्रुटि: ' . $member_details_stmt->error;
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    $member_details_result = $member_details_stmt->get_result();
    
    if ($member_details_result->num_rows === 0) {
        logError("Member details not found for member_id: " . $member_id);
        http_response_code(400);
        $response['message'] = 'आवेदक का विवरण डेटाबेस में नहीं मिला';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    $current_member = $member_details_result->fetch_assoc();
    $member_name = $current_member['full_name'];
    $member_dob = $current_member['date_of_birth'];
    $member_mobile = $current_member['mobile_number'];
    $member_address = $current_member['permanent_address'];
    $father_name = $current_member['father_husband_name'];
    $member_details_stmt->close();

    // ========== HANDLE FILE UPLOADS ==========
    
    $upload_dir = '../uploads/death_aavedan/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $uploaded_files = [];
    $file_fields = ['deceased_aadhar', 'death_certificate', 'post_mortem_report'];
    $required_files = ['deceased_aadhar', 'death_certificate'];

    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$field]['tmp_name'];
            $file_name = $_FILES[$field]['name'];
            $file_size = $_FILES[$field]['size'];

            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                http_response_code(400);
                $response['message'] = $field . ' फाइल 5MB से बड़ी नहीं होनी चाहिए';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            // Validate file type
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_types)) {
                http_response_code(400);
                $response['message'] = 'केवल PDF, JPG या PNG फाइलें अनुमति हैं';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            // Generate unique filename
            $new_file_name = $application_number . '_' . $field . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $uploaded_files[$field] = $new_file_name;
                logError("File uploaded successfully: $new_file_name for $field");
            } else {
                logError("File upload failed for $field: $file_name");
                http_response_code(500);
                $response['message'] = 'फाइल अपलोड विफल: ' . $field;
                ob_end_clean();
                echo json_encode($response);
                exit;
            }
        } elseif (in_array($field, $required_files) && (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK)) {
            http_response_code(400);
            $response['message'] = "कृपया सभी आवश्यक दस्तावेज़ अपलोड करें: $field आवश्यक है";
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
    }

    // Set file variables for database insert
    $deceased_aadhar = $uploaded_files['deceased_aadhar'] ?? null;
    $death_certificate = $uploaded_files['death_certificate'] ?? null;
    $post_mortem_report = $uploaded_files['post_mortem_report'] ?? null;

    // ========== END FILE UPLOADS ==========

    // Check if table exists, if not create it
    $table_check = $conn->query("SHOW TABLES LIKE 'death_aavedan'");
    if ($table_check->num_rows == 0) {
        $create_table_sql = "CREATE TABLE death_aavedan (
            id INT PRIMARY KEY AUTO_INCREMENT,
            application_number VARCHAR(20) UNIQUE NOT NULL,
            
            member_id VARCHAR(50) NOT NULL,
            
            applicant_name VARCHAR(100) NOT NULL,
            applicant_dob DATE NOT NULL,
            applicant_relation VARCHAR(50) NOT NULL,
            applicant_parent_name VARCHAR(100) NOT NULL,
            
            deceased_name VARCHAR(100) NOT NULL,
            deceased_member_id VARCHAR(50) NOT NULL,
            deceased_dob DATE NOT NULL,
            deceased_age INT NOT NULL,
            death_date DATE NOT NULL,
            deceased_relationship VARCHAR(50) NOT NULL,
            cause_of_death TEXT NOT NULL,
            
            family_income INT NOT NULL,
            family_members INT NOT NULL,
            
            bank_name VARCHAR(100) NOT NULL,
            branch_name VARCHAR(100) NOT NULL,
            account_number VARCHAR(20) NOT NULL,
            ifsc_code VARCHAR(11) NOT NULL,
            account_holder_name VARCHAR(100) NOT NULL,
            upi_id VARCHAR(50),
            
            deceased_aadhar VARCHAR(255),
            death_certificate VARCHAR(255),
            post_mortem_report VARCHAR(255),
            
            remarks TEXT,
            
            status ENUM('Pending', 'Under Review', 'Approved', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_deceased_member_id (deceased_member_id),
            INDEX idx_member_id (member_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$conn->query($create_table_sql)) {
            logError("Table creation failed: " . $conn->error);
            http_response_code(500);
            $response['message'] = 'डेटाबेस टेबल बनाने में विफल';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
    }

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO death_aavedan (
        application_number, member_id,
        member_name, member_dob, member_mobile, member_address,
        applicant_name, applicant_dob, applicant_relation, applicant_parent_name,
        deceased_name, deceased_member_id, deceased_dob, deceased_age, death_date, deceased_relationship, cause_of_death,
        family_income, family_members,
        bank_name, branch_name, account_number, ifsc_code, account_holder_name, upi_id,
        deceased_aadhar, death_certificate, post_mortem_report,
        remarks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        logError("Prepare statement failed: " . $conn->error);
        http_response_code(500);
        $response['message'] = 'डेटाबेस विफल';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param(
        'ssssssssssssissdisssssssssss',
        $application_number, $member_id,
        $member_name, $member_dob, $member_mobile, $member_address,
        $applicant_name, $applicant_dob, $applicant_relation, $applicant_parent_name,
        $deceased_name, $deceased_member_id, $deceased_dob, $deceased_age, $death_date, $deceased_relationship, $cause_of_death,
        $family_income, $family_members,
        $bank_name, $branch_name, $account_number, $ifsc_code, $account_holder_name, $upi_id,
        $deceased_aadhar, $death_certificate, $post_mortem_report,
        $remarks
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
        $response['message'] = 'आवेदन सहेजने में विफल';
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'एक त्रुटि हुई: ' . $e->getMessage();
} catch (Error $e) {
    logError("Error: " . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'एक गंभीर त्रुटि हुई';
}

// Clear output buffer and send clean JSON response
ob_end_clean();
echo json_encode($response);
exit;
?>
