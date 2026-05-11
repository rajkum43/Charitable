<?php
// Prevent output before headers
ob_start();

// Set headers safely if not already sent
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
set_error_handler(function($severity, $message, $file, $line) {
    error_log("[$severity] $message in $file:$line");
    return false;
});

$alerts = [];
$debug_mode = isset($_GET['debug']) ? $_GET['debug'] : false;
$response = [
    'success' => true,
    'data' => [],
    'debug' => $debug_mode ? [] : null
];

try {
    // Verify include path exists
    $config_path = dirname(__DIR__) . '/includes/config.php';
    if (!file_exists($config_path)) {
        throw new Exception('Config file not found: ' . $config_path);
    }

    require_once $config_path;
    error_log('Config file loaded successfully');

    // Test PDO connection
    $pdo = null;

    if (function_exists('getPDOConnection')) {
        $pdo = getPDOConnection();
        error_log('Using getPDOConnection()');
    }

    if (!$pdo && isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
        error_log('Using $GLOBALS["pdo"]');
    }

    if (!$pdo && isset($pdo)) {
        // If the included config file set a local $pdo variable, use it.
        error_log('Using local $pdo variable from config');
    }

    if (!$pdo && defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            error_log('Created PDO directly from DB constants');
        } catch (PDOException $e) {
            error_log('Direct PDO creation failed: ' . $e->getMessage());
            $pdo = null;
        }
    }

    if (!$pdo) {
        throw new Exception('No PDO connection available');
    }

    error_log('Database connection successful');

    $current_date = date('Y-m-d');

    // Fetch polls with alert >= 1
    $poll_query = "SELECT * FROM poll WHERE alert >= 1 ORDER BY created_at DESC";
    $stmt = $pdo->prepare($poll_query);
    $stmt->execute();

    $poll_count = 0;
    while ($poll = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $poll_count++;
        error_log('Fetched poll #' . $poll_count . ': ID=' . $poll['id'] . ', claim_number=' . $poll['claim_number'] . ', app_type=' . $poll['application_type'] . ', alert=' . $poll['alert']);
        $alert_data = null;

        try {
            $app_type = trim($poll['application_type']);

            // Get application details based on type
            if ($app_type === 'Death_Claims' || strpos(strtolower($app_type), 'death') !== false) {
                $death_query = "SELECT * FROM death_claims WHERE claim_id = ?";
                $death_stmt = $pdo->prepare($death_query);
                $death_stmt->execute([$poll['claim_number']]);
                $death_data = $death_stmt->fetch(PDO::FETCH_ASSOC);
                error_log('Death_Claims lookup for claim_id=' . $poll['claim_number'] . ': ' . ($death_data ? 'FOUND' : 'NOT FOUND'));

                if ($death_data) {
                    // Get donation stats
                    $donation_query = "SELECT COUNT(*) as total_donations, SUM(amount) as total_amount,
                                      SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_count
                                      FROM donation_transactions
                                      WHERE claim_number = ? AND application_type = 'Death_Claims'";
                    $donation_stmt = $pdo->prepare($donation_query);
                    $donation_stmt->execute([$poll['claim_number']]);
                    $donation_stats = $donation_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Calculate pending members: total members with same poll_option minus verified donations
                    $pending_count = 0;
                    try {
                        // Get total members with this poll option
                        $total_members_query = "SELECT COUNT(*) as total_members FROM members WHERE poll_option = ?";
                        $total_members_stmt = $pdo->prepare($total_members_query);
                        $total_members_stmt->execute([$poll['poll']]);
                        $total_members_result = $total_members_stmt->fetch(PDO::FETCH_ASSOC);
                        $total_members_with_option = (int)($total_members_result['total_members'] ?? 0);
                        
                        // Verified donations for this specific claim
                        $verified_donations = (int)($donation_stats['verified_count'] ?? 0);
                        
                        // Pending = total members with option - verified donations for this claim
                        $pending_count = max(0, $total_members_with_option - $verified_donations);
                    } catch (Exception $e) {
                        error_log('Error calculating pending count for death claim ' . $poll['claim_number'] . ': ' . $e->getMessage());
                        $pending_count = 0;
                    }

                    $alert_data = [
                        'applicant_name' => $death_data['full_name'] ?? $death_data['member_name'] ?? 'N/A',
                        'beneficiary_name' => $death_data['nominee_name'] ?? 'N/A',
                        'event_date' => $death_data['death_date'] ?? 'N/A',
                        'location' => $death_data['death_place'] ?? $death_data['address'] ?? 'Unknown',
                        'address' => $death_data['address'] ?? 'Unknown',
                        'member_id' => $death_data['member_id'] ?? null,
                        'claim_number' => $poll['claim_number'],
                        'payment_info' => [
                            'upi_id' => $death_data['upi_id'] ?? null,
                            'bank_name' => $death_data['bank_name'] ?? null,
                            'account_holder_name' => $death_data['account_holder_name'] ?? null,
                            'account_number' => $death_data['account_number'] ?? null,
                            'ifsc_code' => $death_data['ifsc_code'] ?? null
                        ],
                        'donation_stats' => [
                            'total_donations' => (int)($donation_stats['total_donations'] ?? 0),
                            'total_amount' => (float)($donation_stats['total_amount'] ?? 0),
                            'verified_count' => (int)($donation_stats['verified_count'] ?? 0),
                            'pending_count' => $pending_count
                        ]
                    ];
                }
            } elseif ($app_type === 'Beti_Vivah' || strpos(strtolower($app_type), 'beti') !== false || strpos(strtolower($app_type), 'vivah') !== false) {
                $beti_query = "SELECT * FROM beti_vivah_aavedan WHERE application_number = ?";
                $beti_stmt = $pdo->prepare($beti_query);
                $beti_stmt->execute([$poll['claim_number']]);
                $beti_data = $beti_stmt->fetch(PDO::FETCH_ASSOC);
                error_log('Beti_Vivah lookup for application_number=' . $poll['claim_number'] . ': ' . ($beti_data ? 'FOUND' : 'NOT FOUND'));

                if ($beti_data) {
                    // Get donation stats
                    $donation_query = "SELECT COUNT(*) as total_donations, SUM(amount) as total_amount,
                                      SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_count
                                      FROM donation_transactions
                                      WHERE claim_number = ? AND application_type = 'Beti_Vivah'";
                    $donation_stmt = $pdo->prepare($donation_query);
                    $donation_stmt->execute([$poll['claim_number']]);
                    $donation_stats = $donation_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Calculate pending members: total members with same poll_option minus verified donations
                    $pending_count = 0;
                    try {
                        // Get total members with this poll option
                        $total_members_query = "SELECT COUNT(*) as total_members FROM members WHERE poll_option = ?";
                        $total_members_stmt = $pdo->prepare($total_members_query);
                        $total_members_stmt->execute([$poll['poll']]);
                        $total_members_result = $total_members_stmt->fetch(PDO::FETCH_ASSOC);
                        $total_members_with_option = (int)($total_members_result['total_members'] ?? 0);
                        
                        // Verified donations for this specific claim
                        $verified_donations = (int)($donation_stats['verified_count'] ?? 0);
                        
                        // Pending = total members with option - verified donations for this claim
                        $pending_count = max(0, $total_members_with_option - $verified_donations);
                    } catch (Exception $e) {
                        error_log('Error calculating pending count for beti vivah ' . $poll['claim_number'] . ': ' . $e->getMessage());
                        $pending_count = 0;
                    }

                    $alert_data = [
                        'applicant_name' => $beti_data['member_name'] ?? 'N/A',
                        'beneficiary_name' => $beti_data['bride_name'] ?? 'N/A',
                        'event_date' => $beti_data['wedding_date'] ?? 'N/A',
                        'location' => trim(($beti_data['city'] ?? '') . ', ' . ($beti_data['district'] ?? '')),
                        'address' => $beti_data['address'] ?? 'Unknown',
                        'member_id' => $beti_data['member_id'] ?? null,
                        'claim_number' => $poll['claim_number'],
                        'payment_info' => [
                            'upi_id' => $beti_data['upi_id'] ?? null,
                            'bank_name' => $beti_data['bank_name'] ?? null,
                            'account_holder_name' => $beti_data['account_holder_name'] ?? null,
                            'account_number' => $beti_data['account_number'] ?? null,
                            'ifsc_code' => $beti_data['ifsc_code'] ?? null
                        ],
                        'donation_stats' => [
                            'total_donations' => (int)($donation_stats['total_donations'] ?? 0),
                            'total_amount' => (float)($donation_stats['total_amount'] ?? 0),
                            'verified_count' => (int)($donation_stats['verified_count'] ?? 0),
                            'pending_count' => $pending_count
                        ]
                    ];
                }
            }

            // Build alert object if we have data
            if ($alert_data) {
                $alert_type = ($app_type === 'Death_Claims' || strpos(strtolower($app_type), 'death') !== false)
                    ? 'मृत्यु सहयोग'
                    : 'बेटी विवाह सहायता';

                // Format event date
                $formatted_event_date = 'N/A';
                if ($alert_data['event_date'] !== 'N/A' && !empty($alert_data['event_date'])) {
                    $event_ts = strtotime($alert_data['event_date']);
                    if ($event_ts !== false) {
                        $formatted_event_date = date('d/m/Y', $event_ts);
                    }
                }

                // Calculate donation period
                $donation_period = [
                    'start' => 'N/A',
                    'end' => 'N/A',
                    'days_left' => 0
                ];

                try {
                    $expire_date = (!empty($poll['expire_poll_date']) && $poll['expire_poll_date'] != '0000-00-00')
                        ? new DateTime($poll['expire_poll_date'])
                        : new DateTime($current_date);

                    $donation_end = clone $expire_date;
                    $donation_end->modify('+20 days');

                    $current_dt = new DateTime($current_date);
                    $days_left = $current_dt->diff($donation_end)->days;

                    $donation_period = [
                        'start' => $expire_date->format('d/m/Y'),
                        'end' => $donation_end->format('d/m/Y'),
                        'days_left' => $days_left
                    ];
                } catch (Exception $e) {
                    error_log('DateTime calculation error: ' . $e->getMessage());
                }

                $alert = [
                    'alert' => $poll['alert'],
                    'type' => $alert_type,
                    'poll_option' => $poll['poll'],
                    'claim_number' => $alert_data['claim_number'],
                    'beneficiary_name' => $alert_data['beneficiary_name'],
                    'event_date' => $formatted_event_date,
                    'applicant_name' => $alert_data['applicant_name'],
                    'location' => $alert_data['location'] ?: 'Unknown',
                    'address' => $alert_data['address'],
                    'created_at' => $poll['created_at'],
                    'payment_info' => $alert_data['payment_info'],
                    'donation_period' => $donation_period,
                    'donation_stats' => $alert_data['donation_stats']
                ];

                $alerts[] = $alert;
                error_log('Alert generated for poll ID=' . $poll['id'] . ', total alerts now: ' . count($alerts));
            } else {
                error_log('No alert_data for poll ID=' . $poll['id']);
            }
        } catch (Exception $e) {
            error_log('Error processing poll ' . ($poll['id'] ?? 'unknown') . ': ' . $e->getMessage());
            continue;
        }
    }

    // Assign alerts to response
    $response['data'] = $alerts;

    // Add debug info if requested
    if ($debug_mode === '1' || $debug_mode === true) {
        $response['debug_info'] = [
            'current_date' => $current_date,
            'poll_count_fetched' => $poll_count,
            'alerts_generated' => count($alerts),
            'total_records_in_response' => count($response['data'])
        ];

        // Try to get database stats
        try {
            $stats_query = "SELECT
                (SELECT COUNT(*) FROM poll) as poll_total,
                (SELECT COUNT(*) FROM poll WHERE alert >= 1) as poll_with_alert,
                (SELECT COUNT(*) FROM beti_vivah_aavedan WHERE poll_status = 1) as beti_with_poll_status,
                (SELECT COUNT(*) FROM death_claims WHERE poll_status = 1) as death_with_poll_status,
                (SELECT COUNT(*) FROM donation_transactions) as total_donations";

            $stats_stmt = $pdo->prepare($stats_query);
            $stats_stmt->execute();
            $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

            $response['debug_info']['database_stats'] = $stats;
        } catch (Exception $e) {
            $response['debug_info']['database_stats_error'] = $e->getMessage();
        }

        $response['debug'] = $response['debug_info'];
    } elseif ($debug_mode === 'full') {
        // Full debug mode - show all table data
        $debug_data = [
            'current_date' => $current_date,
            'poll_count_fetched' => $poll_count,
            'alerts_generated' => count($alerts)
        ];

        // Get all poll records
        try {
            $poll_stmt = $pdo->query("SELECT * FROM poll ORDER BY id");
            $debug_data['poll_records'] = $poll_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $debug_data['poll_error'] = $e->getMessage();
        }

        // Get all beti_vivah_aavedan records
        try {
            $beti_stmt = $pdo->query("SELECT * FROM beti_vivah_aavedan ORDER BY id");
            $debug_data['beti_vivah_records'] = $beti_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $debug_data['beti_vivah_error'] = $e->getMessage();
        }

        // Get all death_claims records
        try {
            $death_stmt = $pdo->query("SELECT * FROM death_claims ORDER BY id");
            $debug_data['death_claims_records'] = $death_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $debug_data['death_claims_error'] = $e->getMessage();
        }

        // Get donation_transactions summary
        try {
            $donation_stmt = $pdo->query("SELECT claim_number, application_type, COUNT(*) as count, SUM(amount) as total FROM donation_transactions GROUP BY claim_number, application_type ORDER BY claim_number");
            $debug_data['donation_summary'] = $donation_stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $debug_data['donation_error'] = $e->getMessage();
        }

        // Generate HTML debug page
        ob_clean();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>API Debug - Full Database Data</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1, h2 { color: #333; }
                table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .error { color: red; }
                .success { color: green; }
                .summary { background-color: #e8f4f8; padding: 10px; border-radius: 5px; }
            </style>
        </head>
        <body>
            <h1>API Debug - Full Database Data</h1>
            <div class="summary">
                <h2>Summary</h2>
                <p><strong>Current Date:</strong> <?php echo $debug_data['current_date']; ?></p>
                <p><strong>Polls Fetched:</strong> <?php echo $debug_data['poll_count_fetched']; ?></p>
                <p><strong>Alerts Generated:</strong> <?php echo $debug_data['alerts_generated']; ?></p>
            </div>

            <h2>Poll Table Records</h2>
            <?php if (isset($debug_data['poll_error'])): ?>
                <p class="error">Error fetching poll records: <?php echo $debug_data['poll_error']; ?></p>
            <?php elseif (empty($debug_data['poll_records'])): ?>
                <p class="error">No poll records found!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($debug_data['poll_records'][0]) as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug_data['poll_records'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Beti Vivah Aavedan Table Records</h2>
            <?php if (isset($debug_data['beti_vivah_error'])): ?>
                <p class="error">Error fetching beti_vivah records: <?php echo $debug_data['beti_vivah_error']; ?></p>
            <?php elseif (empty($debug_data['beti_vivah_records'])): ?>
                <p class="error">No beti_vivah records found!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($debug_data['beti_vivah_records'][0]) as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug_data['beti_vivah_records'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Death Claims Table Records</h2>
            <?php if (isset($debug_data['death_claims_error'])): ?>
                <p class="error">Error fetching death_claims records: <?php echo $debug_data['death_claims_error']; ?></p>
            <?php elseif (empty($debug_data['death_claims_records'])): ?>
                <p class="error">No death_claims records found!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($debug_data['death_claims_records'][0]) as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug_data['death_claims_records'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Donation Transactions Summary</h2>
            <?php if (isset($debug_data['donation_error'])): ?>
                <p class="error">Error fetching donation summary: <?php echo $debug_data['donation_error']; ?></p>
            <?php elseif (empty($debug_data['donation_summary'])): ?>
                <p>No donation records found!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Claim Number</th>
                            <th>Application Type</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug_data['donation_summary'] as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['claim_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['application_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['count']); ?></td>
                                <td><?php echo htmlspecialchars($row['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Data Matching Analysis</h2>
            <div style="background-color: #f0f0f0; padding: 15px; border-radius: 5px;">
                <?php
                $issues = [];
                
                if (!empty($debug_data['poll_records'])) {
                    foreach ($debug_data['poll_records'] as $poll) {
                        if ($poll['alert'] >= 1) {
                            $found = false;
                            if ($poll['application_type'] === 'Beti_Vivah') {
                                foreach ($debug_data['beti_vivah_records'] as $beti) {
                                    if ($beti['application_number'] === $poll['claim_number']) {
                                        $found = true;
                                        break;
                                    }
                                }
                            } elseif ($poll['application_type'] === 'Death_Claims') {
                                foreach ($debug_data['death_claims_records'] as $death) {
                                    if ($death['claim_id'] === $poll['claim_number']) {
                                        $found = true;
                                        break;
                                    }
                                }
                            }
                            if (!$found) {
                                $issues[] = "Poll ID {$poll['id']} (claim_number: {$poll['claim_number']}, type: {$poll['application_type']}) - No matching application record found!";
                            }
                        }
                    }
                }
                
                if (empty($issues)) {
                    echo "<p class='success'>✓ All poll records with alert >= 1 have matching application records.</p>";
                } else {
                    echo "<ul class='error'>";
                    foreach ($issues as $issue) {
                        echo "<li>$issue</li>";
                    }
                    echo "</ul>";
                }
                ?>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    // Ensure clean output and encode
    ob_clean();
    $json_output = json_encode($response, JSON_UNESCAPED_UNICODE);
    if ($json_output === false) {
        error_log('json_encode failed: ' . json_last_error_msg());
        echo json_encode(['success' => true, 'data' => []]);
    } else {
        echo $json_output;
    }

} catch (Throwable $e) {

    error_log('Fatal error: ' . $e->getMessage());

    ob_clean();

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'error_code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    exit;
}
?>
