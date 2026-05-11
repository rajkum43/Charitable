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
$debug_mode = isset($_GET['debug']) ? (bool)$_GET['debug'] : false;
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

    // Test PDO connection - handle both function and direct $pdo
    $pdo = null;

    if (function_exists('getPDOConnection')) {
        $pdo = getPDOConnection();
        error_log('Using getPDOConnection() function');
    }

    if (!$pdo && isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
        error_log('Using global $pdo from GLOBALS');
    }

    if (!$pdo && isset($pdo)) {
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
        throw new Exception('PDO connection is null');
    }

    error_log('Database connection successful');
    
    $current_date = date('Y-m-d');
    
    // Helper: check whether a column exists on a table
    function columnExists(PDO $pdo, string $table, string $column): bool {
        try {
            $columnStmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $columnStmt->execute([$column]);
            return (bool) $columnStmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Column check error for ' . $table . '.' . $column . ': ' . $e->getMessage());
            return false;
        }
    }

    // Helper: calculate donation stats and pending member count by poll option and claim
    function getDonationStatsAndPending(PDO $pdo, string $claimNumber, string $applicationType, string $pollOption): array {
        $stats = [
            'total_donations' => 0,
            'total_amount' => 0.0,
            'verified_count' => 0,
            'donating_members' => 0,
            'total_members' => 0,
            'pending_count' => 0
        ];

        try {
            $donationQuery = "SELECT COUNT(*) as total_donations, SUM(amount) as total_amount, 
                              SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_count,
                              COUNT(DISTINCT member_id) as donating_members
                              FROM donation_transactions 
                              WHERE claim_number = ? AND application_type = ?";
            $donationStmt = $pdo->prepare($donationQuery);
            $donationStmt->execute([$claimNumber, $applicationType]);
            $donationData = $donationStmt->fetch(PDO::FETCH_ASSOC);

            $stats['total_donations'] = (int)($donationData['total_donations'] ?? 0);
            $stats['total_amount'] = (float)($donationData['total_amount'] ?? 0);
            $stats['verified_count'] = (int)($donationData['verified_count'] ?? 0);
            $stats['donating_members'] = (int)($donationData['donating_members'] ?? 0);

            $memberQuery = "SELECT COUNT(*) as total_members FROM members WHERE poll_option = ?";
            $memberStmt = $pdo->prepare($memberQuery);
            $memberStmt->execute([$pollOption]);
            $memberData = $memberStmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_members'] = (int)($memberData['total_members'] ?? 0);
            $stats['pending_count'] = max(0, $stats['total_members'] - $stats['donating_members']);
        } catch (Exception $e) {
            error_log('getDonationStatsAndPending error for ' . $claimNumber . ': ' . $e->getMessage());
        }

        return $stats;
    }

    $useDateFilter = false;
    try {
        $useDateFilter = columnExists($pdo, 'poll', 'start_poll_date') && columnExists($pdo, 'poll', 'expire_poll_date');
        error_log('Date filter enabled: ' . ($useDateFilter ? 'yes' : 'no'));
    } catch (Exception $e) {
        error_log('Column check failed: ' . $e->getMessage());
        $useDateFilter = false;
    }

    // Use date filter to get active polls
    if ($useDateFilter) {
        $poll_query = "SELECT * FROM poll WHERE alert >= 1 
                      AND (start_poll_date IS NULL OR start_poll_date = '0000-00-00' OR start_poll_date <= ?) 
                      AND (expire_poll_date IS NULL OR expire_poll_date = '0000-00-00' OR expire_poll_date >= ?)
                      ORDER BY created_at DESC";
    } else {
        $poll_query = "SELECT * FROM poll WHERE alert >= 1 ORDER BY created_at DESC";
    }

    $stmt = $pdo->prepare($poll_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare query');
    }

    try {
        if ($useDateFilter) {
            $stmt->execute([$current_date, $current_date]);
        } else {
            $stmt->execute();
        }
        error_log('Poll query executed successfully');
    } catch (PDOException $e) {
        error_log('Poll query PDOException: ' . $e->getMessage());
        ob_clean();
        echo json_encode($response);
        exit;
    }

    // Debug: Check total poll records
    $debug_query = "SELECT COUNT(*) as total_polls, MIN(alert) as min_alert, MAX(alert) as max_alert FROM poll";
    try {
        $debug_stmt = $pdo->prepare($debug_query);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->fetch(PDO::FETCH_ASSOC);
        error_log('DEBUG: Total polls in database: ' . $debug_result['total_polls'] . ', alert range: ' . $debug_result['min_alert'] . '-' . $debug_result['max_alert']);
    } catch (Exception $e) {
        error_log('DEBUG: Could not fetch poll stats: ' . $e->getMessage());
    }
    
    // Additional debug info
    if ($debug_mode) {
        error_log('DEBUG MODE ON: Current date: ' . $current_date);
        error_log('DEBUG MODE ON: Date filter enabled: ' . ($useDateFilter ? 'yes' : 'no'));
    }

    $poll_count = 0;
    $debug_details = $debug_mode ? [] : null;
    
    while ($poll = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $poll_count++;
        $poll_debug = $debug_mode ? [
            'poll_id' => $poll['id'],
            'claim_number' => $poll['claim_number'],
            'app_type' => $poll['application_type'],
            'steps' => []
        ] : null;
        
        error_log('Processing poll #' . $poll_count . ' - ID: ' . $poll['id'] . ', claim_number: ' . $poll['claim_number'] . ', app_type: ' . $poll['application_type']);
        
        $alert_data = null;
        $member_data = null;
        $donation_stats = null;
        
        try {
            $app_type = trim($poll['application_type']);
            if ($poll_debug) $poll_debug['steps'][] = 'App type determined: ' . $app_type;
            error_log('  App type: ' . $app_type);
            
            // Get application details based on type
            if ($app_type === 'Death_Claims' || strpos(strtolower($app_type), 'death') !== false) {
                if ($poll_debug) $poll_debug['steps'][] = 'Looking for Death_Claims with claim_id: ' . $poll['claim_number'];
                
                // Look up by claim_number instead of user_id
                $death_query = "SELECT * FROM death_claims WHERE claim_id = ?";
                $death_stmt = $pdo->prepare($death_query);
                $death_stmt->execute([$poll['claim_number']]);
                $death_data = $death_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($death_data) {
                    if ($poll_debug) $poll_debug['steps'][] = 'Death claim found in database';
                    $donation_stats = getDonationStatsAndPending($pdo, $poll['claim_number'], 'Death_Claims', $poll['poll']);
                    if ($poll_debug) {
                        $poll_debug['steps'][] = 'Donation stats fetched: ' . json_encode($donation_stats);
                        $poll_debug['steps'][] = 'Pending calculation: ' . $donation_stats['total_members'] . ' members with option ' . $poll['poll'] . ' - ' . $donation_stats['donating_members'] . ' donating members = ' . $donation_stats['pending_count'] . ' pending';
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
                            'donating_members' => (int)($donation_stats['donating_members'] ?? 0),
                            'total_members' => (int)($donation_stats['total_members'] ?? 0),
                            'pending_count' => (int)($donation_stats['pending_count'] ?? 0)
                        ]
                    ];
                    if ($poll_debug) $poll_debug['steps'][] = 'Alert data created successfully';
                } else {
                    if ($poll_debug) $poll_debug['steps'][] = 'No death claim found for claim_id: ' . $poll['claim_number'];
                }
            } else if ($app_type === 'Beti_Vivah' || strpos(strtolower($app_type), 'beti') !== false || strpos(strtolower($app_type), 'vivah') !== false) {
                if ($poll_debug) $poll_debug['steps'][] = 'Looking for Beti_Vivah with application_number: ' . $poll['claim_number'];
                
                // Look up by application_number instead of user_id
                $beti_query = "SELECT * FROM beti_vivah_aavedan WHERE application_number = ?";
                error_log('  Beti_Vivah query for claim_number: ' . $poll['claim_number']);
                $beti_stmt = $pdo->prepare($beti_query);
                $beti_stmt->execute([$poll['claim_number']]);
                $beti_data = $beti_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($beti_data) {
                    if ($poll_debug) $poll_debug['steps'][] = 'Beti_Vivah data found for: ' . $poll['claim_number'];
                    error_log('  ✓ Beti_Vivah data found for: ' . $poll['claim_number']);
                    
                    $donation_stats = getDonationStatsAndPending($pdo, $poll['claim_number'], 'Beti_Vivah', $poll['poll']);
                    if ($poll_debug) {
                        $poll_debug['steps'][] = 'Donation stats fetched: ' . json_encode($donation_stats);
                        $poll_debug['steps'][] = 'Pending calculation: ' . $donation_stats['total_members'] . ' members with option ' . $poll['poll'] . ' - ' . $donation_stats['donating_members'] . ' donating members = ' . $donation_stats['pending_count'] . ' pending';
                    }
                    
                    $city = $beti_data['city'] ?? '';
                    $district = $beti_data['district'] ?? '';
                    $block = $beti_data['block'] ?? '';
                    
                    $location = '';
                    if (!empty($district) && $district !== 'Unknown') {
                        $location .= $district;
                    }
                    if (!empty($block) && $block !== 'Unknown') {
                        if (!empty($location)) $location .= ', ';
                        $location .= $block;
                    }
                    if (!empty($city) && $city !== 'Unknown') {
                        if (!empty($location)) $location .= ', ';
                        $location .= $city;
                    }
                    
                    if (empty($location)) {
                        $location = $beti_data['address'] ?? 'Unknown';
                    }
                    
                    $alert_data = [
                        'applicant_name' => $beti_data['member_name'] ?? 'N/A',
                        'beneficiary_name' => $beti_data['bride_name'] ?? 'N/A',
                        'event_date' => $beti_data['wedding_date'] ?? 'N/A',
                        'location' => $location,
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
                            'donating_members' => (int)($donation_stats['donating_members'] ?? 0),
                            'total_members' => (int)($donation_stats['total_members'] ?? 0),
                            'pending_count' => (int)($donation_stats['pending_count'] ?? 0)
                        ]
                    ];
                    if ($poll_debug) $poll_debug['steps'][] = 'Alert data created successfully';
                } else {
                    if ($poll_debug) $poll_debug['steps'][] = 'No Beti_Vivah data found for: ' . $poll['claim_number'];
                    error_log('  ✗ No Beti_Vivah data found for: ' . $poll['claim_number']);
                }
            }
            
            // Get member payment info only if not already available from application
            if ($alert_data && empty($alert_data['payment_info']['upi_id']) && empty($alert_data['payment_info']['bank_name'])) {
                if (!empty($alert_data['member_id'])) {
                    if ($poll_debug) $poll_debug['steps'][] = 'Fetching member data for member_id: ' . $alert_data['member_id'];
                    $member_query = "SELECT upi_id, bank_name, account_number, ifsc_code, account_holder_name FROM members WHERE member_id = ? LIMIT 1";
                    $member_stmt = $pdo->prepare($member_query);
                    $member_stmt->execute([$alert_data['member_id']]);
                    $member_data = $member_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($member_data) {
                        if ($poll_debug) $poll_debug['steps'][] = 'Member data found';
                        $alert_data['payment_info'] = [
                            'upi_id' => $member_data['upi_id'] ?? null,
                            'bank_name' => $member_data['bank_name'] ?? null,
                            'account_holder_name' => $member_data['account_holder_name'] ?? null,
                            'account_number' => $member_data['account_number'] ?? null,
                            'ifsc_code' => $member_data['ifsc_code'] ?? null
                        ];
                    }
                }
            }
            
            // Build alert object if we have data
            if ($alert_data) {
                error_log('  Building alert object for poll #' . $poll_count);
                $alert_type = ($app_type === 'Death_Claims' || strpos(strtolower($app_type), 'death') !== false) 
                    ? 'मृत्यु सहयोग' 
                    : 'बेटी विवाह सहायता';
                
                // Safely format event date
                $formatted_event_date = 'N/A';
                if ($alert_data['event_date'] !== 'N/A' && !empty($alert_data['event_date'])) {
                    try {
                        $event_ts = strtotime($alert_data['event_date']);
                        if ($event_ts !== false) {
                            $formatted_event_date = date('d/m/Y', $event_ts);
                        }
                    } catch (Exception $e) {
                        error_log('Date formatting error for event_date: ' . $alert_data['event_date']);
                    }
                }
                
                // Calculate donation period using database dates
                $donation_period = [
                    'start' => 'N/A',
                    'end' => 'N/A',
                    'days_left' => 0
                ];
                
                try {
                    // Use the actual database dates from poll table
                    $start_date = null;
                    $expire_date = null;
                    
                    // Get start date from poll table
                    if (!empty($poll['start_poll_date']) && $poll['start_poll_date'] != '0000-00-00') {
                        $start_date = new DateTime($poll['start_poll_date']);
                    }
                    
                    // Get expire date from poll table
                    if (!empty($poll['expire_poll_date']) && $poll['expire_poll_date'] != '0000-00-00') {
                        $expire_date = new DateTime($poll['expire_poll_date']);
                    }
                    
                    // Fallback to current date if no dates in database
                    if (!$expire_date) {
                        $expire_date = new DateTime($current_date);
                        error_log('Warning: No expire_poll_date found, using current date as fallback');
                    }
                    
                    if (!$start_date) {
                        $start_date = clone $expire_date;
                        $start_date->modify('-16 days'); // Default to 16 days before expiry
                        error_log('Warning: No start_poll_date found, using calculated start date');
                    }
                    
                    // Calculate end date (expire_date + 20 days)
                    $donation_end = clone $expire_date;
                    $donation_end->modify('+0 days');
                    
                    // Calculate days left
                    $current_dt = new DateTime($current_date);
                    $days_left = $current_dt->diff($donation_end)->days;
                    
                    // If end date is in the past, days_left should be 0
                    if ($donation_end < $current_dt) {
                        $days_left = 0;
                    }
                    
                    $donation_period = [
                        'start' => $start_date->format('d/m/Y'),
                        'end' => $donation_end->format('d/m/Y'),
                        'days_left' => $days_left
                    ];
                    
                    if ($debug_mode) {
                        error_log('Donation period calculation:');
                        error_log('  - Database start_poll_date: ' . ($poll['start_poll_date'] ?? 'NULL'));
                        error_log('  - Database expire_poll_date: ' . ($poll['expire_poll_date'] ?? 'NULL'));
                        error_log('  - Calculated start: ' . $donation_period['start']);
                        error_log('  - Calculated end: ' . $donation_period['end']);
                        error_log('  - Days left: ' . $donation_period['days_left']);
                    }
                    
                } catch (Exception $e) {
                    error_log('DateTime calculation error: ' . $e->getMessage());
                    error_log('  - poll start_poll_date: ' . ($poll['start_poll_date'] ?? 'NULL'));
                    error_log('  - poll expire_poll_date: ' . ($poll['expire_poll_date'] ?? 'NULL'));
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
                    'donation_stats' => $alert_data['donation_stats'] ?? [
                        'total_donations' => 0,
                        'total_amount' => 0,
                        'verified_count' => 0,
                        'pending_count' => 0
                    ]
                ];
                
                $alerts[] = $alert;
                error_log('  ✓ Alert added to response array (total: ' . count($alerts) . ')');
            } else {
                error_log('  ✗ No alert_data available, skipping this poll');
            }
        } catch (Exception $e) {
            // Skip this poll if there's an error processing it
            error_log('  ✗ Error processing poll ' . ($poll['id'] ?? 'unknown') . ': ' . $e->getMessage());
            continue;
        }
    }
    
    error_log('Poll processing complete: ' . $poll_count . ' polls found, ' . count($alerts) . ' alerts added');
    
    // Fallback: If no alerts found and we used date filter, try without date filter
    if (empty($alerts) && $useDateFilter) {
        error_log('No alerts found with date filter. Trying without date filters as fallback...');
        
        $fallback_query = "SELECT * FROM poll WHERE alert >= 1 ORDER BY created_at DESC";
        $fallback_stmt = $pdo->prepare($fallback_query);
        $fallback_stmt->execute();
        
        $fallback_count = 0;
        while ($poll = $fallback_stmt->fetch(PDO::FETCH_ASSOC)) {
            $fallback_count++;
            error_log('  Fallback poll #' . $fallback_count . ' - ID: ' . $poll['id'] . ', claim_number: ' . $poll['claim_number']);
            
            // Same processing logic as above
            $alert_data = null;
            try {
                $app_type = trim($poll['application_type']);
                
                if ($app_type === 'Death_Claims' || strpos(strtolower($app_type), 'death') !== false) {
                    $death_query = "SELECT * FROM death_claims WHERE claim_id = ?";
                    $death_stmt = $pdo->prepare($death_query);
                    $death_stmt->execute([$poll['claim_number']]);
                    $death_data = $death_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($death_data) {
                        $donation_stats = getDonationStatsAndPending($pdo, $poll['claim_number'], 'Death_Claims', $poll['poll']);
                        
                        $alert_data = [
                            'applicant_name' => $death_data['full_name'] ?? 'N/A',
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
                } else if ($app_type === 'Beti_Vivah' || strpos(strtolower($app_type), 'beti') !== false) {
                    $beti_query = "SELECT * FROM beti_vivah_aavedan WHERE application_number = ?";
                    $beti_stmt = $pdo->prepare($beti_query);
                    $beti_stmt->execute([$poll['claim_number']]);
                    $beti_data = $beti_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($beti_data) {
                        $donation_stats = getDonationStatsAndPending($pdo, $poll['claim_number'], 'Beti_Vivah', $poll['poll']);
                        
                        $city = $beti_data['city'] ?? '';
                        $district = $beti_data['district'] ?? '';
                        $block = $beti_data['block'] ?? '';
                        
                        $location = '';
                        if (!empty($district) && $district !== 'Unknown') {
                            $location .= $district;
                        }
                        if (!empty($block) && $block !== 'Unknown') {
                            if (!empty($location)) $location .= ', ';
                            $location .= $block;
                        }
                        if (!empty($city) && $city !== 'Unknown') {
                            if (!empty($location)) $location .= ', ';
                            $location .= $city;
                        }
                        
                        if (empty($location)) {
                            $location = $beti_data['address'] ?? 'Unknown';
                        }
                        
                        $alert_data = [
                            'applicant_name' => $beti_data['member_name'] ?? 'N/A',
                            'beneficiary_name' => $beti_data['bride_name'] ?? 'N/A',
                            'event_date' => $beti_data['wedding_date'] ?? 'N/A',
                            'location' => $location,
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
                                'donating_members' => (int)($donation_stats['donating_members'] ?? 0),
                                'total_members' => (int)($donation_stats['total_members'] ?? 0),
                                'pending_count' => (int)($donation_stats['pending_count'] ?? 0)
                            ]
                        ];
                    }
                }
                
                if ($alert_data) {
                    $alert_type = ($app_type === 'Death_Claims' || strpos(strtolower($app_type), 'death') !== false) 
                        ? 'मृत्यु सहयोग' 
                        : 'बेटी विवाह सहायता';
                    
                    $formatted_event_date = 'N/A';
                    if ($alert_data['event_date'] !== 'N/A' && !empty($alert_data['event_date'])) {
                        try {
                            $event_ts = strtotime($alert_data['event_date']);
                            if ($event_ts !== false) {
                                $formatted_event_date = date('d/m/Y', $event_ts);
                            }
                        } catch (Exception $e) {}
                    }
                    
                    // Calculate donation period using database dates for fallback as well
                    $donation_period = [
                        'start' => 'N/A',
                        'end' => 'N/A',
                        'days_left' => 0
                    ];
                    
                    try {
                        // Use the actual database dates from poll table
                        $start_date = null;
                        $expire_date = null;
                        
                        // Get start date from poll table
                        if (!empty($poll['start_poll_date']) && $poll['start_poll_date'] != '0000-00-00') {
                            $start_date = new DateTime($poll['start_poll_date']);
                        }
                        
                        // Get expire date from poll table
                        if (!empty($poll['expire_poll_date']) && $poll['expire_poll_date'] != '0000-00-00') {
                            $expire_date = new DateTime($poll['expire_poll_date']);
                        }
                        
                        // Fallback to current date if no dates in database
                        if (!$expire_date) {
                            $expire_date = new DateTime($current_date);
                        }
                        
                        if (!$start_date) {
                            $start_date = clone $expire_date;
                            $start_date->modify('-16 days');
                        }
                        
                        // Calculate end date (expire_date + 20 days)
                        $donation_end = clone $expire_date;
                        $donation_end->modify('+0 days');
                        
                        // Calculate days left
                        $current_dt = new DateTime($current_date);
                        $days_left = $current_dt->diff($donation_end)->days;
                        
                        // If end date is in the past, days_left should be 0
                        if ($donation_end < $current_dt) {
                            $days_left = 0;
                        }
                        
                        $donation_period = [
                            'start' => $start_date->format('d/m/Y'),
                            'end' => $donation_end->format('d/m/Y'),
                            'days_left' => $days_left
                        ];
                        
                    } catch (Exception $e) {
                        error_log('Fallback DateTime calculation error: ' . $e->getMessage());
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
                        'donation_stats' => $alert_data['donation_stats'] ?? [
                            'total_donations' => 0,
                            'total_amount' => 0,
                            'verified_count' => 0,
                            'pending_count' => 0
                        ]
                    ];
                    
                    $alerts[] = $alert;
                    error_log('  ✓ Fallback alert added (total: ' . count($alerts) . ')');
                }
            } catch (Exception $e) {
                error_log('  ✗ Fallback processing error: ' . $e->getMessage());
                continue;
            }
        }
        error_log('Fallback processing complete: ' . $fallback_count . ' fallback polls processed, total alerts now: ' . count($alerts));
    }
    
    // Assign alerts to response
    $response['data'] = $alerts;
    
    // Add debug info always for troubleshooting
    $response['debug_info'] = [
        'current_date' => $current_date,
        'poll_count_fetched' => $poll_count,
        'alerts_generated' => count($alerts),
        'date_filter_used' => $useDateFilter,
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
    
    // Add debug info if requested
    if ($debug_mode) {
        $response['debug'] = $response['debug_info'];
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
    error_log('get_active_poll_alerts fatal error: ' . $e->getMessage());
    ob_clean();
    // Always return valid JSON response
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
}
?>