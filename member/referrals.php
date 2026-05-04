<?php
// Member Referrals Page
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
    <title>मेरे रेफरल - BRCT Bharat Trust</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Member Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/member.css">
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

                <!-- Referrals Section -->
                <div class="content-box active">
                    <div class="mb-4">
                        <h2 class="mb-3"><i class="fas fa-users me-2"></i>मेरे रेफरल</h2>
                        <p class="text-muted">आपके द्वारा रेफर किए गए सदस्यों की सूची।</p>
                    </div>

                    <!-- Referrals Table -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>रेफर किए गए सदस्य</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="referralsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>क्र.सं.</th>
                                            <th>सदस्य ID</th>
                                            <th>नाम</th>
                                            <th>पिता/पति का नाम</th>
                                            <th>मोबाइल</th>
                                            <th>पता</th>
                                            <th>शामिल होने की तारीख</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Loading indicator -->
                            <div id="loadingIndicator" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">लोड हो रहा है...</span>
                                </div>
                                <p class="mt-2 text-muted">रेफरल डेटा लोड हो रहा है...</p>
                            </div>

                            <!-- No data message -->
                            <div id="noDataMessage" class="text-center py-4 d-none">
                                <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                <p class="text-muted">कोई रेफरल नहीं मिला</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Config JS -->
    <script src="../assets/js/config.js"></script>
    <!-- Referrals JS -->
    <script src="assets/js/referrals.js"></script>
</body>
</html>