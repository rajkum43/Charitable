<?php
/**
 * Member Donation History Page
 * Shows all donations made by the member
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>दान का इतिहास - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Member Common CSS -->
    <link rel="stylesheet" href="assets/css/member.css">
    <!-- Donation History CSS -->
    <link rel="stylesheet" href="assets/css/donation_history.css">
    
    <!-- Config (Base URL) -->
    <script src="../assets/js/config.js"></script>
</head>
<body>

    <div class="member-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <?php include 'includes/navbar.php'; ?>
            
            <!-- Content Wrapper -->
            <div class="content-wrapper">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Header -->
                <div class="content-header mb-4">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-history text-primary me-2"></i>
                            दान का इतिहास
                        </h2>
                        <p class="text-muted">आपके द्वारा किए गए सभी दान</p>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="stat-content">
                                <h6>कुल दान</h6>
                                <h3 id="totalDonations">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h6>कुल राशि</h6>
                                <h3 id="totalAmount">₹0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h6>सत्यापित दान</h6>
                                <h3 id="verifiedDonations">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">दान का प्रकार</label>
                                <select id="filterType" class="form-select">
                                    <option value="">सभी प्रकार</option>
                                    <option value="Death_Claims">मृत्यु दान</option>
                                    <option value="Beti_Vivah">बेटी विवाह दान</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Donations Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="donationsTable">
                                <thead>
                                    <tr>
                                        <th>लाभार्थी</th>
                                        <th>प्रकार</th>
                                        <th>राशि</th>
                                        <th>तारीख</th>
                                        <th>कार्य</th>
                                    </tr>
                                </thead>
                                <tbody id="donationsBody">
                                    <!-- Loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <div id="emptyState" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">कोई दान अभी तक नहीं किया गया</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/member-common.js"></script>
    <script src="assets/js/donation_history.js"></script>
</body>
</html>
