<?php
/**
 * Verify Database Connection and Tables
 * Use this to debug connection and data issues
 */

ob_start();

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
}

$response = [
    'success' => false,
    'checks' => [],
    'errors' => []
];

try {
    // 1. Check config file
    $config_path = dirname(__DIR__) . '/includes/config.php';
    $response['checks']['config_file_exists'] = file_exists($config_path);
    
    if (!file_exists($config_path)) {
        throw new Exception('Config file not found: ' . $config_path);
    }
    
    // 2. Load config
    require_once $config_path;
    $response['checks']['config_file_loaded'] = true;
    
    // 3. Check getPDOConnection function
    $response['checks']['getPDOConnection_exists'] = function_exists('getPDOConnection');
    
    // 4. Try to get PDO connection
    try {
        if (function_exists('getPDOConnection')) {
            $pdo = getPDOConnection();
            $response['checks']['pdo_obtained_via_function'] = ($pdo instanceof PDO);
        } elseif (isset($GLOBALS['pdo'])) {
            $pdo = $GLOBALS['pdo'];
            $response['checks']['pdo_obtained_from_globals'] = ($pdo instanceof PDO);
        } else {
            throw new Exception('No PDO connection method available');
        }
        
        if (!($pdo instanceof PDO)) {
            throw new Exception('PDO is not a valid PDO instance');
        }
    } catch (Exception $e) {
        $response['errors'][] = 'PDO Connection Error: ' . $e->getMessage();
        throw $e;
    }
    
    // 5. Test database connection
    try {
        $test_query = "SELECT 1";
        $test_stmt = $pdo->prepare($test_query);
        $test_stmt->execute();
        $response['checks']['database_connection'] = true;
    } catch (Exception $e) {
        $response['errors'][] = 'Database Query Error: ' . $e->getMessage();
        throw $e;
    }
    
    // 6. Check required tables
    $required_tables = ['poll', 'beti_vivah_aavedan', 'death_claims', 'donation_transactions', 'members'];
    
    foreach ($required_tables as $table) {
        try {
            // Use backticks instead of LIKE with parameter (MariaDB compatibility)
            $check_query = "SELECT 1 FROM `" . $table . "` LIMIT 1";
            $result = $pdo->query($check_query);
            if ($result) {
                $response['checks']['table_' . $table . '_exists'] = true;
            } else {
                $response['checks']['table_' . $table . '_exists'] = false;
                $response['errors'][] = 'Table check error for ' . $table . ': Query returned false';
            }
        } catch (Exception $e) {
            $response['checks']['table_' . $table . '_exists'] = false;
            $response['errors'][] = 'Table check error for ' . $table . ': ' . $e->getMessage();
        }
    }
    
    // 7. Check record counts
    $table_stats = [];
    try {
        $tables_to_count = [
            'poll' => ['query' => 'SELECT COUNT(*) as cnt FROM poll'],
            'poll_with_alert' => ['query' => 'SELECT COUNT(*) as cnt FROM poll WHERE alert >= 1'],
            'beti_vivah' => ['query' => 'SELECT COUNT(*) as cnt FROM beti_vivah_aavedan WHERE poll_status = 1'],
            'death_claims' => ['query' => 'SELECT COUNT(*) as cnt FROM death_claims WHERE poll_status = 1'],
            'donations' => ['query' => 'SELECT COUNT(*) as cnt FROM donation_transactions']
        ];
        
        foreach ($tables_to_count as $label => $config) {
            try {
                $stmt = $pdo->prepare($config['query']);
                $stmt->execute();
                $result = $stmt->fetch();
                $table_stats[$label] = (int)$result['cnt'];
            } catch (Exception $e) {
                $table_stats[$label] = 'Error: ' . $e->getMessage();
            }
        }
        
        $response['table_statistics'] = $table_stats;
    } catch (Exception $e) {
        $response['errors'][] = 'Could not fetch table statistics: ' . $e->getMessage();
    }
    
    // 8. Check poll data sample
    try {
        $sample_query = "SELECT id, claim_number, application_type, alert FROM poll WHERE alert >= 1 LIMIT 3";
        $stmt = $pdo->prepare($sample_query);
        $stmt->execute();
        $samples = $stmt->fetchAll();
        $response['sample_polls'] = $samples;
        $response['checks']['sample_polls_found'] = count($samples) > 0;
    } catch (Exception $e) {
        $response['errors'][] = 'Could not fetch sample polls: ' . $e->getMessage();
    }
    
    // If all checks passed
    if (empty($response['errors'])) {
        $response['success'] = true;
        $response['message'] = 'All systems operational! Database connection and tables are ready.';
    }
    
} catch (Throwable $e) {
    $response['success'] = false;
    $response['errors'][] = 'Fatal Error: ' . $e->getMessage();
}

ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
