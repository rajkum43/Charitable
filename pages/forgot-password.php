<?php
// Member Forgot Password Page
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>पासवर्ड रीसेट - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #e6f0ff 0%, #f0f5ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .reset-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 40px;
            animation: slideInUp 0.5s ease;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-card h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 10px;
        }

        .reset-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(13, 110, 253, 0.3);
            color: white;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #198754;
            color: #155724;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div style="text-align: center; margin-bottom: 30px;">
                <i class="fas fa-key text-primary" style="font-size: 3rem;"></i>
                <h1>पासवर्ड रीसेट</h1>
                <p>अपना नया पासवर्ड नির्धारित करने के लिए अपनी जानकारी दर्ज करें</p>
            </div>

            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>महत्वपूर्ण:</strong> यह सुविधा वर्तमान में उपलब्ध नहीं है। कृपया व्यवस्थापक से संपर्क करें।
            </div>

            <form method="POST" action="../api/forgot-password.php">
                <div class="mb-3">
                    <label for="aadhar" class="form-label">आधार संख्या <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="aadhar" name="aadhar" placeholder="12 अंकों का आधार नंबर दर्ज करें" required maxlength="12">
                    <small class="text-muted">आपके खाते की पहचान के लिए आवश्यक</small>
                </div>

                <div class="mb-3">
                    <label for="loginId" class="form-label">Login ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="loginId" name="loginId" placeholder="आपकी 8 अंकीय Member ID दर्ज करें" required maxlength="8">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">ईमेल या मोबाइल नंबर <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="registeredEmail@example.com या 10 अंकों का नंबर" required>
                    <small class="text-muted">सत्यापन के लिए</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg" disabled>
                    <i class="fas fa-paper-plane me-2"></i>सत्यापन लिंक भेजें
                </button>
            </form>

            <div class="back-link">
                <a href="login.php"><i class="fas fa-arrow-left me-2"></i>लॉगिन पेज पर वापस जाएं</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
