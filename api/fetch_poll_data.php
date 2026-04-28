<?php
/**
 * Fetch Poll Data API
 * Returns death_claims + beti_vivah_aavedan records
 * Only poll_status = 0 records
 * URL: /api/fetch_poll_data.php
 */

header('Content-Type: application/json; charset=UTF-8');

// ---------------------------
// ERROR REPORTING
// ---------------------------
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ---------------------------
// LOG DIRECTORY
// ---------------------------
$logsDir = __DIR__ . '/../logs';

if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// ---------------------------
// CUSTOM ERROR HANDLER
// ---------------------------
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logsDir) {

    $msg = "[" . date('Y-m-d H:i:s') . "] "
         . "Error No: $errno | $errstr | File: $errfile | Line: $errline\n";

    error_log($msg, 3, $logsDir . '/fetch_poll_data_errors.log');

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error'
    ], JSON_UNESCAPED_UNICODE);

    exit;
});

// ---------------------------
// LOAD CONFIG
// ---------------------------
require_once __DIR__ . '/../includes/config.php';

// ---------------------------
// START SESSION
// ---------------------------
session_start();

// ---------------------------
// AUTH CHECK
// ---------------------------
if (!isset($_SESSION['admin_id'])) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {

    // ---------------------------
    // PDO CONNECTION
    // ---------------------------
    global $pdo;
    $conn = $pdo;

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // ---------------------------
    // DEATH CLAIMS QUERY
    // ---------------------------
    $deathSQL = "
        SELECT 
            id,
            COALESCE(claim_id, CONCAT('DC-', id)) AS claim_number,
            member_id AS user_id,
            full_name AS user_name,
            'Death_Claims' AS application_type,
            id AS db_id
        FROM death_claims
        WHERE poll_status = 0
    ";

    // ---------------------------
    // BETI VIVAH QUERY
    // ---------------------------
    $vivahSQL = "
        SELECT
            id,
            COALESCE(application_number, CONCAT('BV-', id)) AS claim_number,
            member_id AS user_id,
            member_name AS user_name,
            'Beti_Vivah' AS application_type,
            id AS db_id
        FROM beti_vivah_aavedan
        WHERE poll_status = 0
    ";

    // ---------------------------
    // EXECUTE DEATH QUERY
    // ---------------------------
    $stmt1 = $conn->prepare($deathSQL);
    $stmt1->execute();
    $deathData = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // ---------------------------
    // EXECUTE BETI VIVAH QUERY
    // ---------------------------
    $stmt2 = $conn->prepare($vivahSQL);
    $stmt2->execute();
    $vivahData = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // ---------------------------
    // MERGE ALL DATA
    // ---------------------------
    $allRecords = array_merge($deathData, $vivahData);

    // ---------------------------
    // SORT BY ID DESC
    // ---------------------------
    usort($allRecords, function ($a, $b) {
        return $b['id'] <=> $a['id'];
    });

    // ---------------------------
    // SUCCESS RESPONSE
    // ---------------------------
    echo json_encode([
        'success' => true,
        'count'   => count($allRecords),
        'data'    => $allRecords
    ], JSON_UNESCAPED_UNICODE);

    exit;

} catch (Exception $e) {

    error_log(
        "[" . date('Y-m-d H:i:s') . "] Exception: "
        . $e->getMessage() . "\n",
        3,
        $logsDir . '/fetch_poll_data_errors.log'
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
?>