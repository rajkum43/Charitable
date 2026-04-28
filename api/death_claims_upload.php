<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Prevent any output before JSON
ob_start();

try {
    require_once '../includes/config.php';
    
    // Make sure PDO exists
    if (!isset($pdo)) {
        throw new Exception('Database connection failed');
    }

    $action = $_POST['action'] ?? null;

    if ($action === 'upload_documents') {
        // Validate required files
        $required_files = ['file_aadhaar_deceased', 'file_death_certificate', 'file_nominee_aadhaar'];
        
        foreach ($required_files as $field) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['size'] === 0) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Required file missing: {$field}"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $result = uploadDeathClaimDocuments();
        
        ob_end_clean();
        echo json_encode($result, JSON_UNESCAPED_UNICODE);

    } else {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    error_log('Death Claims Upload - PDO Error: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'debug' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Death Claims Upload Error: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Upload Death Claim Documents
 */
function uploadDeathClaimDocuments() {
    $upload_dir = '../uploads/death_claims/';
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'अपलोड डायरेक्टरी नहीं बनाई जा सकी'
            ];
        }
    }

    // Set folder permissions
    chmod($upload_dir, 0755);

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

            // Validate file size
            if ($file['size'] > $max_size) {
                $errors[] = "{$db_field_name}: फाइल आकार 2MB से अधिक है";
                continue;
            }

            // Validate file type
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "{$db_field_name}: फाइल प्रकार समर्थित नहीं है। JPG, PNG या PDF का उपयोग करें।";
                continue;
            }

            // Check upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "{$db_field_name}: फाइल अपलोड में त्रुटि (कोड: {$file['error']})";
                continue;
            }

            // Sanitize filename
            $original_name = basename($file['name']);
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            
            // Generate unique filename
            $filename = 'death_claim_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
            $filepath = $upload_dir . $filename;

            // Additional security: validate file content
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_types)) {
                $errors[] = "{$db_field_name}: फाइल सामग्री समर्थित नहीं है";
                continue;
            }

            // Move file to upload directory
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Set file permissions
                chmod($filepath, 0644);
                $uploaded_files[$db_field_name] = $filename;
            } else {
                $errors[] = "{$db_field_name}: फाइल अपलोड विफल";
                error_log('File move failed: ' . $file['tmp_name'] . ' -> ' . $filepath);
            }
        }
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode('; ', $errors),
            'files' => []
        ];
    }

    return [
        'success' => true,
        'message' => 'सभी फाइलें सफलतापूर्वक अपलोड हो गईं',
        'files' => $uploaded_files
    ];
}
?>
