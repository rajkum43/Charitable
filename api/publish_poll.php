<?php
/**
 * Publish Poll Data API
 * Inserts poll records and updates poll_status
 * POST /api/publish_poll.php
 */

// Set JSON response header FIRST
header('Content-Type: application/json; charset=UTF-8');

// Error handling - must be before any other output
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

// Start session
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['polls']) || !is_array($input['polls'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input: polls array required'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $polls = $input['polls'];

    if (empty($polls)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No polls provided'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Use mysqli connection directly
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // Calculate current month's 10th and 20th
    $currentMonth = date('Y-m');
    $startPollDate = $currentMonth . '-10';
    $expirePollDate = $currentMonth . '-20';

    $insertedCount = 0;
    $insertedClaimNumbers = [];
    $deathClaimsUpdated = 0;
    $betiVivahUpdated = 0;

    // Start transaction
    $conn->begin_transaction();

    // Step 0: Get current alert number (MAX(alert) + 1)
    $alertNumber = 1; // Default to 1 for first publish
    $tableCheckResult = $conn->query("SHOW TABLES LIKE 'poll'");
    
    if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
        // Check if alert column exists
        $columnCheck = $conn->query("SHOW COLUMNS FROM `poll` LIKE 'alert'");
        if ($columnCheck && $columnCheck->num_rows > 0) {
            // Get MAX(alert) from poll table
            $maxAlertResult = $conn->query("SELECT MAX(alert) as max_alert FROM poll");
            if ($maxAlertResult) {
                $maxAlertRow = $maxAlertResult->fetch_assoc();
                $maxAlert = $maxAlertRow['max_alert'];
                $alertNumber = ($maxAlert === null || $maxAlert == 0) ? 1 : $maxAlert + 1;
                error_log("DEBUG: Current alert number = $alertNumber (previous max = $maxAlert)");
            }
        }
    }

    // Step 1: Insert polls into poll table (if table exists)
    $tableCheckResult = $conn->query("SHOW TABLES LIKE 'poll'");
    $insertErrors = [];
    
    if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
        // Check if alert column exists in the table
        $columnCheck = $conn->query("SHOW COLUMNS FROM `poll` LIKE 'alert'");
        $hasAlertColumn = ($columnCheck && $columnCheck->num_rows > 0);
        
        if ($hasAlertColumn) {
            // Insert with alert column
            $insertSQL = "INSERT INTO poll (claim_number, user_id, poll, application_type, alert, start_poll_date, expire_poll_date, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        } else {
            // Insert without alert column (backward compatibility)
            $insertSQL = "INSERT INTO poll (claim_number, user_id, poll, application_type, start_poll_date, expire_poll_date, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        }
        
        $stmt = $conn->prepare($insertSQL);

        if ($stmt) {
            foreach ($polls as $poll) {
                $claimNumber = $poll['claim_number'] ?? '';
                $userId = $poll['user_id'] ?? '';
                $pollOption = $poll['poll'] ?? '';
                $applicationType = $poll['application_type'] ?? '';

                // Debug logging
                error_log("DEBUG: Inserting poll - Claim: $claimNumber, User: $userId, Poll: $pollOption, Type: $applicationType, Alert: $alertNumber");

                if ($hasAlertColumn) {
                    $stmt->bind_param("ssssiis", $claimNumber, $userId, $pollOption, $applicationType, $alertNumber, $startPollDate, $expirePollDate);
                } else {
                    $stmt->bind_param("ssssss", $claimNumber, $userId, $pollOption, $applicationType, $startPollDate, $expirePollDate);
                }
                
                if ($stmt->execute()) {
                    $insertedCount++;
                    $insertedClaimNumbers[] = $claimNumber;
                    error_log("DEBUG: Successfully inserted $claimNumber with alert=$alertNumber");
                } else {
                    $errorMsg = "Poll insert error for claim $claimNumber: " . $stmt->error;
                    error_log($errorMsg);
                    $insertErrors[] = [
                        'claim_number' => $claimNumber,
                        'error' => $stmt->error
                    ];
                }
            }
            $stmt->close();
        } else {
            $insertErrors[] = [
                'error' => 'Failed to prepare insert statement: ' . $conn->error
            ];
        }
    }

    // Step 2: Update poll_status in source tables
    foreach ($polls as $poll) {
        $dbId = $poll['db_id'] ?? null;
        $applicationType = $poll['application_type'] ?? '';

        if ($dbId) {
            if ($applicationType === 'Death_Claims') {
                // Try death_claims table first
                $updateSQL = "UPDATE death_claims SET poll_status = 1 WHERE id = ?";
                $stmt = $conn->prepare($updateSQL);
                if ($stmt) {
                    $stmt->bind_param("i", $dbId);
                    if ($stmt->execute()) {
                        $deathClaimsUpdated += $stmt->affected_rows;
                    }
                    $stmt->close();
                }
            } elseif ($applicationType === 'Beti_Vivah') {
                // Update beti_vivah_aavedan table
                $updateSQL = "UPDATE beti_vivah_aavedan SET poll_status = 1 WHERE id = ?";
                $stmt = $conn->prepare($updateSQL);
                if ($stmt) {
                    $stmt->bind_param("i", $dbId);
                    if ($stmt->execute()) {
                        $betiVivahUpdated += $stmt->affected_rows;
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Commit transaction
    $conn->commit();
    $conn->close();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'पोल सफलतापूर्वक प्रकाशित हो गया!',
        'alert_number' => $alertNumber,
        'inserted' => $insertedCount,
        'total_selected' => count($polls),
        'death_claims_updated' => $deathClaimsUpdated,
        'beti_vivah_updated' => $betiVivahUpdated,
        'claim_numbers' => $insertedClaimNumbers,
        'errors' => $insertErrors
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
