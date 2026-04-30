<?php
/**
 * Alert-wise Beti Vivah Sahyog Suchi
 * Displays list of all alerts (publish batches) with their details
 */

require_once '../includes/config.php';

// Determine base path
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $root_path = '/Charitable/';
} else {
    $root_path = '/';
}

// Get all distinct alerts from poll table for Beti Vivah applications
try {
    $alerts_query = "SELECT DISTINCT p.alert
                     FROM poll p
                     INNER JOIN beti_vivah_aavedan b ON p.claim_number = b.application_number
                     WHERE p.alert > 0 
                     AND p.application_type = 'Beti_Vivah'
                     ORDER BY p.alert DESC";
    
    $alerts_stmt = $pdo->prepare($alerts_query);
    $alerts_stmt->execute();
    $alerts = $alerts_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log('Error fetching alerts: ' . $e->getMessage());
    $alerts = [];
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सूचना बेटी विवाह सहयोग सूची - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Alert Beti Vivah Sahyog Suchi CSS -->
    <link rel="stylesheet" href="../assets/css/alert-betvivah-suchi.css">
    
    <!-- Config -->
    <script src="../assets/js/config.js"></script>
</head>
<body data-base-path="<?php echo $root_path; ?>">

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include '../components/navbar.php'; ?>

    <!-- Page Header Section -->
    <section class="members-header bg-primary text-white p-2">
        <div class="container">
            <div class="members-header-content">
                <h1>
                    <i class="fas fa-bell me-3"></i>सूचना बेटी विवाह सहयोग सूची
                </h1>
                <p>प्रकाशित सूचियों के अनुसार बेटी विवाह सहायता विवरण</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container alerts-container">
            
            <?php if (!empty($alerts)): ?>
                <div class="alerts-list">
                    <?php foreach ($alerts as $alert): ?>
                        <?php
                            // Get count of records for this alert
                            $count_query = "SELECT COUNT(*) as count
                                          FROM poll p
                                          INNER JOIN beti_vivah_aavedan b ON p.claim_number = b.application_number
                                          WHERE p.alert = ? 
                                          AND p.application_type = 'Beti_Vivah'";
                            $count_stmt = $pdo->prepare($count_query);
                            $count_stmt->execute([$alert]);
                            $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
                            $record_count = $count_result['count'] ?? 0;
                        ?>
                        <div class="alert-card">
                            <div class="alert-info">
                                <div class="alert-badge">
                                    <i class="fas fa-bullhorn"></i> प्रकाशन
                                </div>
                                <div class="alert-number">Alert <?php echo htmlspecialchars($alert); ?></div>
                                <div class="alert-label">सूचना संख्या <?php echo htmlspecialchars($alert); ?></div>
                                <div class="alert-count">
                                    <i class="fas fa-file-invoice"></i> 
                                    <span><?php echo htmlspecialchars($record_count); ?> आवेदन</span>
                                </div>
                            </div>
                            <a href="alert_details_betivivah_sahyogsuchi.php?alert=<?php echo htmlspecialchars($alert); ?>" 
                               class="view-details-btn">
                                <i class="fas fa-eye"></i> विवरण देखें
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-alerts">
                    <i class="fas fa-inbox"></i>
                    <h3>कोई सूचना नहीं</h3>
                    <p class="text-muted">अभी तक कोई बेटी विवाह सहायता प्रकाशित नहीं की गई है।</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Alert Beti Vivah Sahyog Suchi JS -->
    <script src="../assets/js/alert-betvivah-suchi.js"></script>
</body>
</html>
