<?php
/**
 * API: Upload Donation Receipt
 * 
 * Purpose: Handle file uploads for donation transaction receipts
 * 
 * POST Parameters:
 * - donation_id: ID of the donation being funded
 * - claim_number: Application number (from death_aavedan or beti_vivah_aavedan)
 * - application_type: 'Death' or 'Beti_Vivah'
 * - transaction_number: Transaction ID or UTR number
 * - receipt_file: File upload (max 500KB)
 * 
 * Returns: JSON
 */

session_start();
header('Content-Type: application/json');

try {
    // 1. Authentication Check
    if (!isset($_SESSION['member_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    // 2. Include Database Config
    require_once '../includes/config.php';

    // 3. Validate Required Fields
    if (!isset($_POST['claim_number']) || !isset($_POST['application_type']) || !isset($_POST['transaction_number']) || !isset($_POST['donation_amount'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: claim_number, application_type, transaction_number, donation_amount'
        ]);
        exit;
    }

    // 4. Validate Donation Amount
    $donationAmount = trim($_POST['donation_amount']);
    if (empty($donationAmount) || !is_numeric($donationAmount) || floatval($donationAmount) <= 0 || floatval($donationAmount) > 999999) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid donation amount. Please enter a valid amount between 1 and 999999'
        ]);
        exit;
    }
    $donationAmount = floatval($donationAmount);

    // 5. Validate Transaction Number Format
    $transactionNumber = trim($_POST['transaction_number']);
    if (empty($transactionNumber) || strlen($transactionNumber) > 100) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid transaction number'
        ]);
        exit;
    }

    // 6. Validate File Upload
    if (!isset($_FILES['receipt_file'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No file uploaded'
        ]);
        exit;
    }

    $file = $_FILES['receipt_file'];

    // 7. Validate File
    $maxFileSize = 500 * 1024; // 500 KB
    $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'File upload error: ' . $file['error']
        ]);
        exit;
    }

    if ($file['size'] > $maxFileSize) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'File size exceeds maximum allowed size of 500KB'
        ]);
        exit;
    }

    if (!in_array($file['type'], $allowedMimes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Allowed: PDF, JPG, PNG'
        ]);
        exit;
    }

    // 8. Create Upload Directory
    $uploadDir = '../uploads/donations/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create upload directory'
            ]);
            exit;
        }
    }

    // 9. Resolve logged-in member and validate against members table
    $sessionMemberId = isset($_SESSION['member_id']) ? trim($_SESSION['member_id']) : '';
    $sessionLoginId = isset($_SESSION['login_id']) ? trim($_SESSION['login_id']) : '';
    $sessionNumericId = ctype_digit($sessionMemberId) ? $sessionMemberId : '';

    if (empty($sessionMemberId) && empty($sessionLoginId)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'अमान्य सदस्य सत्र। कृपया फिर से लॉगिन करें।'
        ]);
        exit;
    }

    $memberId = null;
    $resolutionSource = null;

    if (!empty($sessionMemberId)) {
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE member_id = ? LIMIT 1");
        $stmt->execute([$sessionMemberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $memberId = trim($row['member_id']);
            $resolutionSource = 'member_id';
        }
    }

    if (!$memberId && !empty($sessionNumericId)) {
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE id = ? LIMIT 1");
        $stmt->execute([$sessionNumericId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $memberId = trim($row['member_id']);
            $resolutionSource = 'id';
        }
    }

    if (!$memberId && !empty($sessionLoginId)) {
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE login_id = ? LIMIT 1");
        $stmt->execute([$sessionLoginId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $memberId = trim($row['member_id']);
            $resolutionSource = 'login_id';
        }
    }

    if (!$memberId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'सदस्य आईडी मान्य नहीं है। कृपया फिर से लॉगिन करें।'
        ]);
        exit;
    }

    // Confirm that resolved member_id exists exactly in members
    $confirmStmt = $pdo->prepare("SELECT id, member_id, login_id FROM members WHERE member_id = ? LIMIT 1");
    $confirmStmt->execute([$memberId]);
    $confirmRow = $confirmStmt->fetch(PDO::FETCH_ASSOC);

    $memberExists = (bool)$confirmRow;
    $memberCountStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM members WHERE member_id = ?");
    $memberCountStmt->execute([$memberId]);
    $memberCount = $memberCountStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    if (!$memberExists) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'सत्र सदस्य आईडी सत्यापित नहीं हो पाया। कृपया व्यवस्थापक से संपर्क करें।'
        ]);
        exit;
    }

    // 10. Generate Safe Filename
    $timestamp = time();
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "receipt_{$memberId}_{$timestamp}.{$fileExtension}";
    $filepath = $uploadDir . $filename;

    // 11. Move File
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save uploaded file'
        ]);
        exit;
    }

    // 12. Save Transaction Record to Database
    $claimNumber = trim($_POST['claim_number']);
    $applicationType = trim($_POST['application_type']);
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : NULL;

    // 11a. Fetch donation_to_member_id from the respective table
    $donationToMemberId = NULL;
    
    if ($applicationType === 'Death' || $applicationType === 'Death_Claims') {
        // Look in death_claims table
        $sqlFetch = "SELECT member_id FROM death_claims WHERE claim_id = ? LIMIT 1";
        $stmtFetch = $pdo->prepare($sqlFetch);
        $stmtFetch->execute([$claimNumber]);
        $result = $stmtFetch->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $donationToMemberId = $result['member_id'];
        }
    } elseif ($applicationType === 'Beti_Vivah') {
        // Look in beti_vivah_aavedan table
        $sqlFetch = "SELECT member_id FROM beti_vivah_aavedan WHERE application_number = ? LIMIT 1";
        $stmtFetch = $pdo->prepare($sqlFetch);
        $stmtFetch->execute([$claimNumber]);
        $result = $stmtFetch->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $donationToMemberId = $result['member_id'];
        }
    }

    $sql = "INSERT INTO donation_transactions 
            (member_id, donation_to_member_id, claim_number, application_type, amount, transaction_number, receipt_file_path, file_size, file_mime_type, remarks, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $memberId,
        $donationToMemberId,
        $claimNumber,
        $applicationType,
        $donationAmount,
        $transactionNumber,
        'uploads/donations/' . $filename,
        $file['size'],
        $file['type'],
        $remarks
    ]);

    if (!$result) {
        // Delete the uploaded file if DB insert fails
        unlink($filepath);

        $memberExistsStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM members WHERE member_id = ?");
        $memberExistsStmt->execute([$memberId]);
        $memberExistsCount = $memberExistsStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        $memberRowStmt = $pdo->prepare("SELECT id, member_id, login_id FROM members WHERE member_id = ? LIMIT 1");
        $memberRowStmt->execute([$memberId]);
        $memberRow = $memberRowStmt->fetch(PDO::FETCH_ASSOC);

        $errorInfo = $stmt->errorInfo();

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save transaction record'
        ]);
        exit;
    }

    // 11. Return Success Response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Transaction receipt uploaded successfully. Pending admin verification.',
        'data' => [
            'transaction_id' => $pdo->lastInsertId(),
            'filename' => $filename,
            'size' => $file['size'],
            'status' => 'pending'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
