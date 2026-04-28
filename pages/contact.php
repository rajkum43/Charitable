<?php
// Contact Page
session_start();
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>संपर्क करें - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #e6f0ff 0%, #f0f5ff 100%);
            background-image: linear-gradient(135deg, #e6f0ff 0%, #f0f5ff 100%);
            background-repeat: no-repeat;
            background-position: center;
            background-size: 500px;
            background-attachment: fixed;
            min-height: 100vh;
            padding: 60px 20px;
        }

        /* watermark effect */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("../assets/images/girl.png") center no-repeat;
            background-size: 500px;
            opacity: 0.08;
            z-index: -1;
            pointer-events: none;
        }

        .contact-section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
            animation: slideInDown 0.6s ease;
        }

        .section-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #f5f3f3;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        /* Contact Information Cards */
        .contact-infoc {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: slideInLeft 0.6s ease;
        }

        .contact-infoc h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateX(10px);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .info-content h4 {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .info-content p {
            color: #666;
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-content a {
            color: #0d6efd;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .info-content a:hover {
            text-decoration: underline;
            color: #0b5ed7;
        }

        /* Contact Form */
        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: slideInRight 0.6s ease;
        }

        .contact-form h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            padding: 14px 40px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            color: white;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.4s ease;
            z-index: 0;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(13, 110, 253, 0.3);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        /* Success/Error Messages */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
            animation: slideInDown 0.4s ease;
        }

        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #198754;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
        }

        .social-link {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .section-header h1 {
                font-size: 2rem;
            }

            .contact-form {
                padding: 30px;
            }

            .contact-infoc {
                padding: 30px;
            }
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Status Message */
        .form-status {
            display: none;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            font-weight: 500;
        }

        .form-status.show {
            display: block;
        }

        .form-status.success {
            background-color: #d4edda;
            border-left: 4px solid #198754;
            color: #155724;
        }

        .form-status.error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        /* Loading spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .spinner.show {
            display: inline-block;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- External CSS for Death Aavedan Form -->
    <link rel="stylesheet" href="../assets/css/death_aavedan.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Member Directory CSS -->
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include '../components/navbar.php'; ?>
    
    <div class="contact-section">
        <!-- Header -->
        <div class="section-header" data-aos="fade-up">
            <h1><i class="fas fa-envelope me-2"></i>हमसे संपर्क करें</h1>
            <p>आपके सवालों और सुझावों के लिए हम सदा तैयार हैं। हमसे संपर्क करने में संकोच न करें।</p>
        </div>

        <!-- Contact Container -->
        <div class="contact-container">
            <!-- Contact Information -->
            <div class="contact-infoc" data-aos="fade-up" data-aos-delay="100">
                <h2><i class="fas fa-info-circle me-2"></i>संपर्क जानकारी</h2>
                
                <!-- Phone -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h4>फोन</h4>
                        <p>
                            <a href="tel:+919876543210">+91 99363 85189</a><br>
                            <a href="tel:+919876543211">+91 98765 43211</a>
                        </p>
                        <small style="color: #999;">सोमवार - शुक्रवार: 9:00 AM - 6:00 PM</small>
                    </div>
                </div>

                <!-- Email -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h4>ईमेल</h4>
                        <p>
                            <a href="mailto:brctbharat@gmail.com">brctbharat@gmail.com</a><br>
                            <a href="mailto:brctbharat@gmail.com">brctbharat@gmail.com</a>
                        </p>
                    </div>
                </div>

                <!-- Address -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>पता</h4>
                        <p>
                            BRCT Bharat Trust<br>
                            समाज सेवा केंद्र<br>
                            राज नगर, कानपुर<br>
                            उत्तर प्रदेश - 208001<br>
                            भारत
                        </p>
                    </div>
                </div>

                <!-- Office Hours -->
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h4>कार्यालय का समय</h4>
                        <p>
                            सोमवार - शुक्रवार: 9:00 AM - 6:00 PM<br>
                            शनिवार: 10:00 AM - 4:00 PM<br>
                            रविवार: बंद
                        </p>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form" data-aos="fade-up" data-aos-delay="200">
                <h2><i class="fas fa-edit me-2"></i>संदेश भेजें</h2>

                <?php
                // Display success/error messages
                if (isset($_SESSION['contact_message'])) {
                    $message = $_SESSION['contact_message'];
                    $message_type = isset($_SESSION['contact_message_type']) ? $_SESSION['contact_message_type'] : 'error';
                    
                    if ($message_type === 'success') {
                        echo '<div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($message) . '
                        </div>';
                    } else {
                        echo '<div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($message) . '
                        </div>';
                    }
                    
                    unset($_SESSION['contact_message']);
                    unset($_SESSION['contact_message_type']);
                }
                ?>

                <form id="contactForm" method="POST" action="../api/contact.php">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">आपका नाम <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="अपना पूरा नाम दर्ज करें" required maxlength="100">
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">ईमेल <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="आपका ईमेल दर्ज करें" required maxlength="100">
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone" class="form-label">फोन नंबर <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="10 अंकों का फोन नंबर दर्ज करें" required maxlength="10" pattern="\d{10}">
                    </div>

                    <!-- Subject -->
                    <div class="form-group">
                        <label for="subject" class="form-label">विषय <span class="text-danger">*</span></label>
                        <select class="form-select" id="subject" name="subject" required>
                            <option value="">-- चुनें --</option>
                            <option value="membership">सदस्यता संबंधी</option>
                            <option value="benefits">सुविधाओं के बारे में</option>
                            <option value="complaint">शिकायत</option>
                            <option value="suggestion">सुझाव</option>
                            <option value="donation">दान संबंधी</option>
                            <option value="other">अन्य</option>
                        </select>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label for="message" class="form-label">संदेश <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" placeholder="अपना संदेश यहाँ लिखें..." required maxlength="500"></textarea>
                        <small class="text-muted" style="display: block; margin-top: 5px;">अधिकतम 500 शब्द</small>
                    </div>

                    <!-- Status Message -->
                    <div class="form-status" id="formStatus"></div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="spinner" id="spinner"></span>
                        <i class="fas fa-paper-plane me-2"></i>संदेश भेजें
                    </button>
                </form>

                <!-- Help Text -->
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <small style="color: #666;">
                        <i class="fas fa-shield-alt me-2"></i>
                        आपकी व्यक्तिगत जानकारी सुरक्षित है और केवल आपके संदेश का जवाब देने के लिए उपयोग की जाएगी।
                    </small>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Format phone number to accept only digits
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        // Form submission
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const formStatus = document.getElementById('formStatus');
            
            // Disable button and show spinner
            submitBtn.disabled = true;
            spinner.classList.add('show');
            formStatus.classList.remove('show');

            try {
                const formData = new FormData(this);
                const response = await fetch('../api/contact.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    formStatus.className = 'form-status show success';
                    formStatus.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + (result.message || 'आपका संदेश सफलतापूर्वक भेज दिया गया है। जल्द ही हम आपसे संपर्क करेंगे।');
                    
                    // Reset form
                    this.reset();
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        formStatus.classList.remove('show');
                    }, 5000);
                } else {
                    formStatus.className = 'form-status show error';
                    formStatus.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + (result.message || 'संदेश भेजने में त्रुटि हुई। कृपया पुनः प्रयास करें।');
                }
            } catch (error) {
                formStatus.className = 'form-status show error';
                formStatus.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>त्रुटि: ' + error.message;
            } finally {
                // Enable button and hide spinner
                submitBtn.disabled = false;
                spinner.classList.remove('show');
            }
        });

        // Set current date in the form
        const today = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
