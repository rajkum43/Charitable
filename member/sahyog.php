<?php
// Member - Poll Contribution Page
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
    <title>सहयोग (Sahyog) - BRCT भारत ट्रस्ट</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Member Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/member.css">
    <!-- Sahyog Specific CSS -->
    <link rel="stylesheet" href="assets/css/member-sahyog.css">
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
                <section class="sahyog-section">
                    <div class="row mb-5">
                        <div class="col-12">
                            <h2 class="mb-2">
                                <i class="fas fa-hands-helping text-danger me-2"></i>सहयोग (Sahyog)
                            </h2>
                            <p class="text-muted">अपने विश्वास परिवार के सदस्यों की मदद करें</p>
                        </div>
                    </div>

                    <!-- Quick Action Button -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card shadow-sm border-0">
                                <div class="card-body py-4">
                                    <div class="text-center">
                                        <p class="text-muted mb-4">
                                            <i class="fas fa-info-circle me-1"></i>
                                            जिसे सहायता की सबसे ज्यादा आवश्यकता है, उसे देखें और दान करें
                                        </p>
                                        <button type="button" class="btn btn-primary btn-lg px-5 view-beneficiary-btn" 
                                                onclick="loadBeneficiaryInfo()">
                                            <i class="fas fa-search me-2"></i>सहायता की आवश्यकता वाले को देखें
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Member Welcome Message -->
                    <div id="memberInfo" style="display: none; margin-top: 30px;">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="alert alert-success border-0 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong id="memberNameDisplay">सदस्य</strong> (ID: <span id="memberIdDisplay">-</span>) को स्वागत है।
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Beneficiary Needing Help (After Verification) -->
                    <div id="beneficiaryContainer" style="display: none;">
                    </div>
                </section>
            </div>
            
            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>भुगतान करें</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="paymentDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">बंद करें</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Code JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode.js/1.5.3/qrcode.min.js"></script>
    <!-- Pass Member ID to JavaScript -->
    <script>
        const CURRENT_MEMBER_ID = '<?php echo $_SESSION['member_id']; ?>';
        // Set navbar title
        document.addEventListener('DOMContentLoaded', function() {
            const navbarTitle = document.getElementById('navbarTitle');
            if (navbarTitle) {
                navbarTitle.textContent = 'सहयोग (Sahyog)';
            }
        });
    </script>
    <!-- Member Sahyog JS -->
    <script src="assets/js/member-sahyog.js"></script>
    <!-- Mobile Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const navbarToggleMobile = document.querySelector('.navbar-toggle-mobile');
            
            if (navbarToggleMobile && sidebar) {
                // Close sidebar when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggle = event.target.closest('.navbar-toggle-mobile');
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 768) {
                        sidebar.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>
