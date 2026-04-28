<?php
// Member Login Page
session_start();
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
</head>
<body>

    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div> 

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar (Non-sticky for login page) -->
    <?php $navbar_sticky = false; include '../components/navbar.php'; ?>

    <!-- Login Section -->
    <section class="login-section py-5">
        <div class="login-container" data-aos="fade-up" data-aos-duration="1000">
            <div class="login-card">
                <!-- Logo Section -->
                <div class="logo-section">
                    <div class="logo-icon-wrapper">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h1>सदस्य लॉगिन</h1>
                    <p class="subtitle">
                        <i class="fas fa-lock"></i>
                        सुरक्षित प्रवेश
                        <i class="fas fa-shield-alt"></i>
                    </p>
                </div>

                <?php
                // Display error/success messages from session
                if (isset($_SESSION['login_message'])) {
                    $message = $_SESSION['login_message'];
                    $message_type = isset($_SESSION['login_message_type']) ? $_SESSION['login_message_type'] : 'error';
                    
                    if ($message_type === 'success') {
                        echo '<div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <span>' . htmlspecialchars($message) . '</span>
                        </div>';
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>' . htmlspecialchars($message) . '</span>
                        </div>';
                    }
                    
                    // Clear the message from session after displaying
                    unset($_SESSION['login_message']);
                    unset($_SESSION['login_message_type']);
                }
                ?>

                <form method="POST" action="../api/login.php" id="loginForm" novalidate>
                    <!-- Login ID Field -->
                    <div class="form-floating-group">
                        <div class="form-floating-custom">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="loginId" 
                                   name="loginId" 
                                   placeholder=" " 
                                   required 
                                   maxlength="8"
                                   pattern="\d{8}"
                                   title="8 अंकों की Member ID दर्ज करें">
                            <label for="loginId">Member ID</label>
                            <div class="invalid-feedback">
                                कृपया 8 अंकों की वैध Member ID दर्ज करें
                            </div>
                        </div>
                        <div class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            यह आपके आधार कार्ड के अंतिम 8 अंक हैं
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-floating-group">
                        <div class="form-floating-custom">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder=" " 
                                   required
                                   minlength="6">
                            <label for="password">पासवर्ड</label>
                            <button type="button" class="password-toggle-btn" id="togglePassword" title="पासवर्ड दिखाएं/छुपाएं">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-strength-text" id="strengthText"></div>
                        
                        <div class="field-hint">
                            <i class="fas fa-lightbulb"></i>
                            <div>
                                <strong>Default पासवर्ड:</strong> <strong>Contact Admin</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="checkbox-wrapper">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <span class="checkmark"></span>
                            <span class="checkbox-text">मुझे याद रखें</span>
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login" id="loginBtn">
                        <div class="spinner"></div>
                        <span><i class="fas fa-sign-in-alt"></i>लॉगिन करें</span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>या</span>
                </div>

                <!-- Register Link -->
                <a href="register.php" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    नया सदस्य? रजिस्टर करें
                </a>

                <!-- Forgot Password -->
                <div class="forgot-password">
                    <a href="forgot-password.php">
                        <i class="fas fa-question-circle"></i>
                        पासवर्ड भूल गए?
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src= "../assets/js/login.js" ></script>
    
</body>
</html>