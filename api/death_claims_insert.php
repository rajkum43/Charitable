<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Prevent any output before JSON
ob_start();

// Error handler
function handleError($errno, $errstr, $errfile, $errline) {
    ob_end_clean();
    error_log("Death Claims Insert PHP Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
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

    // Create death_claims table if not exists
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `death_claims` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `claim_id` VARCHAR(50) UNIQUE,
      `member_id` VARCHAR(20) DEFAULT NULL,
      `full_name` VARCHAR(100) NOT NULL,
      `father_name` VARCHAR(100) DEFAULT NULL,
      `dob` DATE DEFAULT NULL,
      `address` TEXT,
      `death_date` DATE NOT NULL,
      `death_place` VARCHAR(200) DEFAULT NULL,
      `death_reason` TEXT,
      `age_at_death` INT DEFAULT NULL,
      `nominee_name` VARCHAR(100) NOT NULL,
      `nominee_relation` VARCHAR(50) NOT NULL,
      `nominee_dob` DATE DEFAULT NULL,
      `nominee_mobile` VARCHAR(20) NOT NULL,
      `nominee_address` TEXT,
      `bank_name` VARCHAR(100) DEFAULT NULL,
      `account_number` VARCHAR(50) DEFAULT NULL,
      `ifsc_code` VARCHAR(20) DEFAULT NULL,
      `branch_name` VARCHAR(100) DEFAULT NULL,
      `account_holder_name` VARCHAR(100) DEFAULT NULL,
      `upi_id` VARCHAR(50) DEFAULT NULL,
      `aadhaar_deceased` VARCHAR(255) DEFAULT NULL,
      `postmortem_report` VARCHAR(255) DEFAULT NULL,
      `death_certificate` VARCHAR(255) DEFAULT NULL,
      `nominee_aadhaar` VARCHAR(255) DEFAULT NULL,
      `status` VARCHAR(50) DEFAULT 'Pending',
      `remark` TEXT,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSQL);
    
    // Ensure all required columns exist (for existing tables)
    try {
        // Get existing columns
        $stmt = $pdo->query("DESCRIBE `death_claims`");
        $existingColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = $row['Field'];
        }
        
        // Add missing columns
        $columnsToAdd = [
            'claim_id' => "VARCHAR(50) UNIQUE",
            'death_place' => "VARCHAR(200) DEFAULT NULL",
            'death_reason' => "TEXT",
            'age_at_death' => "INT DEFAULT NULL",
            'nominee_dob' => "DATE DEFAULT NULL",
            'nominee_address' => "TEXT",
            'bank_name' => "VARCHAR(100) DEFAULT NULL",
            'account_number' => "VARCHAR(50) DEFAULT NULL",
            'account_holder_name' => "VARCHAR(100) DEFAULT NULL",
            'ifsc_code' => "VARCHAR(20) DEFAULT NULL",
            'branch_name' => "VARCHAR(100) DEFAULT NULL",
            'upi_id' => "VARCHAR(50) DEFAULT NULL",
            'status' => "VARCHAR(50) DEFAULT 'Pending'",
            'remark' => "TEXT"
        ];
        
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            if (!in_array($columnName, $existingColumns)) {
                try {
                    $pdo->exec("ALTER TABLE `death_claims` ADD COLUMN `{$columnName}` {$columnDefinition}");
                } catch (Exception $e) {
                    // Column might already exist
                }
            }
        }
    } catch (Exception $e) {
        // Log setup error only if needed
    }

    $action = $_POST['action'] ?? null;

    if ($action === 'insert_claim') {
        // Validate required fields
        $required_fields = [
            'member_id', 'member_name', 'member_dob', 
            'death_date', 'death_place', 'age_at_death',
            'nominee_name', 'nominee_relation', 'nominee_mobile',
            'bank_name', 'account_number', 'ifsc_code', 'account_holder_name'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "कृपया सभी जरूरी फील्ड भरें: {$field}"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        // Validate mobile number
        $nominee_mobile = $_POST['nominee_mobile'];
        if (!preg_match('/^\d{10}$/', $nominee_mobile)) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'नॉमिनी का मोबाइल नंबर 10 अंकों का होना चाहिए'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Validate dates
        try {
            $member_dob_str = trim($_POST['member_dob'] ?? '');
            $death_date_str = trim($_POST['death_date'] ?? '');
            
            if (empty($member_dob_str) || empty($death_date_str)) {
                throw new Exception('DOB or Death Date is empty');
            }
            
            $dob = DateTime::createFromFormat('Y-m-d', $member_dob_str);
            $death_date = DateTime::createFromFormat('Y-m-d', $death_date_str);
            
            if ($dob === false || $death_date === false) {
                throw new Exception('Invalid date format. Expected Y-m-d');
            }
            
            if ($death_date <= $dob) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'मृत्यु तिथि जन्म तिथि से बाद की होनी चाहिए'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (Exception $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'अमान्य तिथि प्रारूप: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Upload files
        $upload_result = uploadDeathClaimDocuments();
        
        if (!$upload_result['success']) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'दस्तावेज़ अपलोड विफल: ' . $upload_result['message']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Insert into database
        $query = "INSERT INTO death_claims (
            member_id, full_name, father_name, dob, address,
            death_date, death_place, death_reason, age, age_at_death,
            nominee_name, nominee_relation, relation_type, nominee_dob, nominee_mobile, nominee_address,
            bank_name, account_number, ifsc_code, branch_name, account_holder_name, upi_id,
            aadhaar_deceased, postmortem_report, death_certificate, nominee_aadhaar,
            status
        ) VALUES (
            :member_id, :full_name, :father_name, :dob, :address,
            :death_date, :death_place, :death_reason, :age, :age_at_death,
            :nominee_name, :nominee_relation, :relation_type, :nominee_dob, :nominee_mobile, :nominee_address,
            :bank_name, :account_number, :ifsc_code, :branch_name, :account_holder_name, :upi_id,
            :aadhaar_deceased, :postmortem_report, :death_certificate, :nominee_aadhaar,
            :status
        )";

        $stmt = $pdo->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . implode(' ', $pdo->errorInfo()));
        }

        $execute_params = [
            ':member_id' => $_POST['member_id'],
            ':full_name' => $_POST['member_name'],
            ':father_name' => $_POST['member_father_name'] ?? null,
            ':dob' => $_POST['member_dob'],
            ':address' => $_POST['member_address'] ?? null,
            ':death_date' => $_POST['death_date'],
            ':death_place' => $_POST['death_place'],
            ':death_reason' => $_POST['death_reason'] ?? null,
            ':age' => (int)$_POST['age_at_death'],  // Map age_at_death to age column
            ':age_at_death' => (int)$_POST['age_at_death'],
            ':nominee_name' => $_POST['nominee_name'],
            ':nominee_relation' => $_POST['nominee_relation'],
            ':relation_type' => $_POST['nominee_relation'],  // Map nominee_relation to relation_type column
            ':nominee_dob' => $_POST['nominee_dob'] ?? null,
            ':nominee_mobile' => $nominee_mobile,
            ':nominee_address' => $_POST['nominee_address'] ?? null,
            ':bank_name' => $_POST['bank_name'],
            ':account_number' => $_POST['account_number'],
            ':ifsc_code' => $_POST['ifsc_code'],
            ':branch_name' => $_POST['branch_name'] ?? null,
            ':account_holder_name' => $_POST['account_holder_name'],
            ':upi_id' => $_POST['upi_id'] ?? null,
            ':aadhaar_deceased' => $upload_result['files']['aadhaar_deceased'] ?? null,
            ':postmortem_report' => $upload_result['files']['postmortem_report'] ?? null,
            ':death_certificate' => $upload_result['files']['death_certificate'] ?? null,
            ':nominee_aadhaar' => $upload_result['files']['nominee_aadhaar'] ?? null,
            ':status' => 'Pending'
        ];

        $result = $stmt->execute($execute_params);

        if (!$result) {
            throw new Exception('Failed to insert claim: ' . implode(' | ', $stmt->errorInfo()));
        }

        $db_id = $pdo->lastInsertId();
        
        // Generate formatted claim ID: BRCT-D+yyyymmdd+id
        $today = date('Ymd');
        $formatted_claim_id = 'BRCT-D' . $today . str_pad($db_id, 4, '0', STR_PAD_LEFT);
        
        // Update the claim with the formatted claim_id
        try {
            $updateStmt = $pdo->prepare("UPDATE death_claims SET claim_id = ? WHERE id = ?");
            $updateStmt->execute([$formatted_claim_id, $db_id]);
        } catch (Exception $e) {
            // Update error is non-critical
        }

        ob_end_clean();
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'claim_id' => $formatted_claim_id,
            'message' => 'आवेदन सफलतापूर्वक जमा हो गया है'
        ], JSON_UNESCAPED_UNICODE);

    } else {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'अमान्य कार्रवाई'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'डेटाबेस त्रुटि'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'त्रुटि: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Upload Death Claim Documents
 */
function uploadDeathClaimDocuments() {
    $upload_dir = dirname(__DIR__) . '/uploads/death_claims/';
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'अपलोड निर्देशिका बनाने में विफल'
            ];
        }
    }

    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $file_fields = [
        'file_aadhaar_deceased' => 'aadhaar_deceased',
        'file_death_certificate' => 'death_certificate',
        'file_postmortem' => 'postmortem_report',
        'file_nominee_aadhaar' => 'nominee_aadhaar'
    ];

    $uploaded_files = [];
    $errors = [];

    foreach ($file_fields as $field_name => $db_field_name) {
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['size'] > 0) {
            $file = $_FILES[$field_name];
            
            // Validate file
            if ($file['size'] > $max_size) {
                $errors[] = "{$field_name}: फाइल आकार 2MB से अधिक है";
                continue;
            }

            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "{$field_name}: अनुमति प्राप्त फाइल प्रकार नहीं (MIME: {$file['type']})";
                continue;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "{$field_name}: फाइल अपलोड विफल (Code: {$file['error']})";
                continue;
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'claim_' . uniqid() . '_' . time() . '.' . $ext;
            $filepath = $upload_dir . $filename;
            
            // Move file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $uploaded_files[$db_field_name] = $filename;
            } else {
                $errors[] = "{$field_name}: फाइल स्थानांतरित नहीं की जा सकी";
            }
        }
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode(', ', $errors)
        ];
    }

    return [
        'success' => true,
        'files' => $uploaded_files
    ];
}
?>
