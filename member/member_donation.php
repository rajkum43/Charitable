<?php
/**
 * Member Donation Page
 * Shows active poll requests based on member's poll_option
 * 
 * This page loads:
 * - API: /api/get_member_donations.php (backend logic)
 * - CSS: assets/css/member_donation.css (styling)
 * - JS: assets/js/member_donation.js (frontend logic)
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
    <title>दान करें - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Member Common CSS -->
    <link rel="stylesheet" href="assets/css/member.css">
    <!-- Member Donation CSS -->
    <link rel="stylesheet" href="assets/css/member_donation.css">
    
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
                <!-- Header Section -->
                <div class="mb-4">
                    <h2 class="mb-2"><i class="fas fa-hand-holding-heart me-2"></i>दान करें</h2>
                    <p class="text-muted">आपके समाज के जरूरतमंद सदस्यों की मदद करें। आपका योगदान अमूल्य है।</p>
                    
                    <div class="alert alert-info mt-3" id="pollOptionLabel">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>आपका पोल विकल्प:</strong> 
                        <span class="badge bg-info" id="memberPollOption">लोड हो रहा है...</span>
                    </div>
                </div>

                <!-- Donation Cards Section -->
                <div id="donationCardsContainer" class="donation-cards-section">
                    <!-- Cards will be rendered here by JavaScript -->
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Member Donation JS -->
    <script src="assets/js/member_donation.js"></script>
</body>
</html>
