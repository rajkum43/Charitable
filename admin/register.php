<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Admin - BRCT Bharat Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <h1><i class="fas fa-user-plus"></i></h1>
            <h1>Register New Admin</h1>
            <p>BRCT Bharat Trust - Admin Management</p>
        </div>

        <!-- Body -->
        <div class="register-body">
            <!-- Alert Messages -->
            <div id="alert-container"></div>

            <!-- Registration Form -->
            <form id="registerForm" onsubmit="handleRegister(event)">
                <!-- Username -->
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            placeholder="Min 3 characters (a-z, 0-9, _)"
                            required
                            minlength="3"
                            pattern="^[a-zA-Z0-9_]+$"
                        >
                    </div>
                    <small class="text-secondary d-block mt-2">Letters, numbers, and underscores only</small>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="Enter valid email"
                            required
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Min 8 characters"
                            required
                            minlength="8"
                            onkeyup="checkPasswordStrength()"
                        >
                    </div>
                    <div class="password-strength">
                        <span id="strength-text" class="text-secondary">Strength: Weak</span>
                        <div class="strength-meter">
                            <div id="strength-bar" class="strength-bar weak"></div>
                        </div>
                        <small class="text-secondary d-block mt-2">
                            Use uppercase, lowercase, numbers, and symbols for stronger password
                        </small>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-lock me-2"></i>Confirm Password
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Re-enter password"
                            required
                        >
                    </div>
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn-register" id="registerBtn">
                    <i class="fas fa-user-plus me-2"></i>Create Admin Account
                </button>

                <a href="index.php" class="btn-back mt-3">
                    <i class="fas fa-arrow-left"></i>Back to Dashboard
                </a>
            </form>
        </div>

        <!-- Footer -->
        <div class="register-footer">
            <p>Secure Admin Registration • Minimum requirements enforced</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/register.js"></script>
</body>
</html>
