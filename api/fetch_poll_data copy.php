<?php
/**
 * Fetch Poll Data API
 * Returns death claims and beti vivah aavedan records with poll_status = 0
 * 
 * GET /api/fetch_poll_data.php
 */

// Set JSON response header first to prevent HTML errors





header('Content-Type: application/json');

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logsDir) {
    $errorMessage = "[$errno] $errstr in $errfile:$errline";
    error_log($errorMessage, 3, $logsDir . '/fetch_poll_data_errors.log');
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr,
        'debug' => [
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    exit;
});

require_once __DIR__ . '/../includes/config.php';

// Start session
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

try {
    // PDO connection from config.php
    $conn = getPDOConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Ensure poll_status column exists (for backward compatibility)
    $alterDeathClaimsSQL = "ALTER TABLE death_claims ADD COLUMN poll_status TINYINT(1) DEFAULT 0 AFTER status";
    $alterBetiVivahSQL = "ALTER TABLE beti_vivah_aavedan ADD COLUMN poll_status TINYINT(1) DEFAULT 0 AFTER status";

    try {
        $conn->exec($alterDeathClaimsSQL);
    } catch (PDOException $e) {
        // Column might already exist - ignore
    }

    try {
        $conn->exec($alterBetiVivahSQL);
    } catch (PDOException $e) {
        // Column might already exist - ignore
    }

    // Query for death claims with poll_status = 0
    $deathClaimsSQL = "
        SELECT 
            dc.id,
            COALESCE(dc.claim_id, CONCAT('CLAIM-', dc.id)) as claim_number,
            dc.member_id as user_id,
            dc.full_name as user_name,
            'Death_Claims' as application_type,
            dc.claim_id as db_claim_id,
            dc.id as db_id
        FROM death_claims dc
        WHERE dc.poll_status = 0
        ORDER BY dc.created_at DESC
    ";

    // Query for beti vivah aavedan with poll_status = 0
    $betiVivahSQL = "
        SELECT 
            bv.id,
            COALESCE(bv.application_number, CONCAT('APP-', bv.id)) as claim_number,
            bv.member_id as user_id,
            bv.member_name as user_name,
            'Beti_Vivah' as application_type,
            bv.application_number as db_application_number,
            bv.id as db_id
        FROM beti_vivah_aavedan bv
        WHERE bv.poll_status = 0
        ORDER BY bv.created_at DESC
    ";

    // Execute queries
    $deathClaimsStmt = $conn->prepare($deathClaimsSQL);
    $deathClaimsStmt->execute();
    $deathClaims = $deathClaimsStmt->fetchAll(PDO::FETCH_ASSOC);

    $betiVivahStmt = $conn->prepare($betiVivahSQL);
    $betiVivahStmt->execute();
    $betiVivah = $betiVivahStmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge results - death_claims first, then beti vivah
    $allRecords = array_merge($deathClaims, $betiVivah);

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $allRecords,
        'count' => count($allRecords)
    ]);

} catch (Exception $e) {
    $errorMessage = "Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    error_log($errorMessage, 3, $logsDir . '/fetch_poll_data_errors.log');
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'exception' => get_class($e),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    exit;
}
?>
