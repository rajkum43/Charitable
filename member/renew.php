<?php
// Member Renewal Page
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../includes/config.php';

// Get member details
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT full_name, mobile_number FROM members WHERE member_id = ?");
$stmt->bind_param("s", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सदस्यता नवीनीकरण - BRCT Bharat Trust</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/renew.css">
</head>
<body>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="renewal-card">
                    <div class="text-center mb-4">
                        <i class="fas fa-refresh text-primary" style="font-size: 2.5rem;"></i>
                        <h1 class="mt-3">सदस्यता नवीनीकरण</h1>
                        <p class="text-secondary">अपनी सदस्यता को नवीनीकृत करें और सेवाओं का लाभ उठाएं</p>
                    </div>

                    <!-- Member Info -->
                    <div class="member-info-card mb-4">
                        <h5><i class="fas fa-user me-2"></i>सदस्य जानकारी</h5>
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>नाम:</strong> <?php echo htmlspecialchars($member['full_name']); ?></p>
                                <p><strong>सदस्य ID:</strong> <?php echo htmlspecialchars($member_id); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <p><strong>मोबाइल:</strong> <?php echo htmlspecialchars($member['mobile_number']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="payment-instructions mb-4">
                        <h5><i class="fas fa-credit-card me-2"></i>भुगतान निर्देश</h5>
                        <div class="alert alert-info">
                            <strong>नवीनीकरण शुल्क: ₹500/-</strong>
                            <p class="mb-2">कृपया नीचे दिए गए किसी भी तरीके से भुगतान करें:</p>
                        </div>

                        <div class="payment-methods">
                            <!-- UPI ID -->
                            <div class="payment-method">
                                <h6><i class="fas fa-mobile-alt me-2"></i>UPI ID</h6>
                                <div class="payment-detail">
                                    <code>brcttrust@upi</code>
                                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('brcttrust@upi')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Bank Details -->
                            <div class="payment-method">
                                <h6><i class="fas fa-university me-2"></i>बैंक खाता</h6>
                                <div class="payment-detail">
                                    <p><strong>खाता संख्या:</strong> 1234567890</p>
                                    <p><strong>IFSC:</strong> SBIN0001234</p>
                                    <p><strong>बैंक:</strong> State Bank of India</p>
                                    <p><strong>खाता धारक:</strong> BRCT Bharat Trust</p>
                                </div>
                            </div>

                            <!-- QR Code Placeholder -->
                            <div class="payment-method text-center">
                                <h6><i class="fas fa-qrcode me-2"></i>QR कोड</h6>
                                <div class="qr-placeholder">
                                    <i class="fas fa-qrcode fa-3x text-muted"></i>
                                    <p class="mt-2">QR कोड जल्द ही उपलब्ध होगा</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Form -->
                    <form id="renewalForm">
                        <div class="mb-3">
                            <label for="transactionId" class="form-label">
                                <i class="fas fa-hashtag me-2"></i>लेन-देन ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="transactionId" name="transactionId"
                                   placeholder="UTR संख्या या लेन-देन ID दर्ज करें" required>
                            <small class="text-muted">भुगतान के बाद प्राप्त UTR संख्या दर्ज करें</small>
                            <small class="text-danger" id="transactionIdError"></small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-check me-2"></i>नवीनीकरण पूरा करें
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>डैशबोर्ड पर वापस जाएं
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div class="position-fixed top-0 end-0 p-3" id="alertContainer" style="z-index: 1050;"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/member-common.js"></script>
    <!-- Renewal JS -->
    <script src="assets/js/renew.js"></script>
</body>
</html>