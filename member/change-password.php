<?php
session_start();

if (!isset($_SESSION['member_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../includes/config.php';
$memberId = $_SESSION['member_id'];
$memberName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'सदस्य';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>पासवर्ड बदलें - BRCT Bharat Trust</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/member-change-password.css">
      <link rel="stylesheet" href="assets/css/member.css">
    <!-- Member Donation CSS -->
    <link rel="stylesheet" href="assets/css/member_donation.css">
</head>
<body>
    <div class="member-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="content-wrapper py-4">
                <div class="change-password-header mb-4">
                    <div>
                        <h2>पासवर्ड बदलें</h2>
                        <p class="text-muted">अपने सदस्य खाते के लिए नया पासवर्ड सेट करें।</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> डैशबोर्ड पर वापस जाएं
                        </a>
                    </div>
                </div>

                <div class="card change-password-card">
                    <div class="card-body">
                        <div class="mb-4">
                            <p class="mb-1 text-secondary">सदस्य ID</p>
                            <h5 class="fw-bold"><?php echo htmlspecialchars($memberId); ?></h5>
                        </div>

                        <div id="formAlert"></div>

                        <form id="changePasswordForm" novalidate>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">नया पासवर्ड</label>
                                <div class="input-group">
                                    <input type="password" id="newPassword" name="new_password" class="form-control" placeholder="नया पासवर्ड दर्ज करें" minlength="6" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="newPassword" aria-label="पासवर्ड दिखाएं/छुपाएं">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">कम से कम 6 वर्ण दर्ज करें।</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">पासवर्ड दोबारा दर्ज करें</label>
                                <div class="input-group">
                                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder="पासवर्ड दोबारा दर्ज करें" minlength="6" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="confirmPassword" aria-label="पासवर्ड दिखाएं/छुपाएं">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary px-4" id="passwordSubmitBtn">
                                <i class="fas fa-key me-1"></i> पासवर्ड अपडेट करें
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/member-change-password.js"></script>
</body>
</html>
