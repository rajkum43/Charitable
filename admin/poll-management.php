<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';
require_once '../includes/config.php';

$page_title = 'Poll Management System';

// Fetch total members count
$total_members = 0;
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM members");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_members = $result['total'] ?? 0;
} catch (Exception $e) {
    error_log('Error fetching total members: ' . $e->getMessage());
    $total_members = 0;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - BRCT Bharat</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Common CSS -->
    <link rel="stylesheet" href="css/admin-common.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/poll-system.css">
    
    <!-- Dynamic Base URL Configuration -->
    <script src="../assets/js/config.js"></script>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <div class="main-content">
            <!-- Status Message -->
            <div id="statusMessage" class="status-message"></div>

            <!-- Header -->
            <div class="poll-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="fw-bold mb-2">
                            <i class="fas fa-poll me-2"></i>पोल प्रबंधन प्रणाली
                        </h1>
                        <p class="text-muted">Poll Management System</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="poll-stats">
                            <div class="stat-item">
                                <small class="text-muted">कुल रिकॉर्ड</small>
                                <h3 id="totalCount">0</h3>
                            </div>
                            <div class="stat-item">
                                <small class="text-muted">चयनित</small>
                                <h3 id="selectedCount">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Panel -->
            <div class="controls-panel mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">फ़िल्टर द्वारा:</label>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-filter="all">
                                <i class="fas fa-list me-1"></i>सभी
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-filter="death">
                                <i class="fas fa-heart me-1"></i>मृत्यु लाभ आवेदन
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-filter="vivah">
                                <i class="fas fa-ring me-1"></i>बेटी विवाह
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button id="publishBtn" class="btn btn-success btn-lg" disabled>
                            <i class="fas fa-check-circle me-2"></i>प्रकाशित करें
                        </button>
                        <div id="publishWarning" class="alert alert-warning alert-sm d-inline-block mt-2" style="display:none; font-size: 0.85rem;">
                            <i class="fas fa-exclamation-triangle me-1"></i>महीने के 11-20 दिन के बीच प्रकाशन अनुमति नहीं है
                        </div>
                    </div>
                </div>
            </div>

            <!-- Poll Table -->
            <div class="poll-table-wrapper mb-4">
                <table id="pollTable" class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">क्रम</th>
                            <th style="width: 20%;">दावा संख्या</th>
                            <th style="width: 35%;">सदस्य नाम</th>
                            <th style="width: 15%;">प्रकार</th>
                            <th style="width: 10%; text-align: center;">चयन</th>
                            <th style="width: 15%; text-align: center;">पोल</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-spinner fa-spin me-2"></i>डेटा लोड हो रहा है...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button id="clearSelectionBtn" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>चयन साफ करें
                </button>
                <button id="exportBtn" class="btn btn-outline-info">
                    <i class="fas fa-download me-2"></i>निर्यात करें (CSV)
                </button>
            </div>
        </div>
        </div>
    </div>

    <!-- Publish Confirmation Modal -->
    <div class="modal fade" id="publishConfirmModal" tabindex="-1" aria-labelledby="publishConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="publishConfirmLabel">
                        <i class="fas fa-poll me-2"></i>पोल प्रकाशन की पुष्टि करें
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p class="text-muted">
                            <strong id="recordCountText">0</strong> रिकॉर्ड प्रकाशित किए जाएंगे।
                        </p>
                        <p class="text-muted">
                            सभी <strong><?php echo $total_members; ?> सदस्यों</strong> को निम्नलिखित पोल विकल्पों में वितरित किया जाएगा:
                        </p>
                    </div>

                    <!-- Distribution Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40%;">पोल विकल्प</th>
                                    <th class="text-center" style="width: 60%;">सदस्यों की संख्या</th>
                                </tr>
                            </thead>
                            <tbody id="distributionTableBody">
                                <!-- Distribution data will be filled by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>आप इस कार्रवाई को अभी पूर्ववत नहीं कर सकते। कृपया सावधानी से आगे बढ़ें।</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>रद्द करें
                    </button>
                    <button type="button" class="btn btn-success" id="confirmPublishBtn">
                        <i class="fas fa-check-circle me-1"></i>प्रकाशित करें
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script>
        window.TOTAL_MEMBERS = <?php echo (int)$total_members; ?>;
        console.log('Total Members Loaded:', window.TOTAL_MEMBERS);
    </script>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS - Loads after TOTAL_MEMBERS is set -->
    <script src="js/poll-system.js"></script>
</body>
</html>
