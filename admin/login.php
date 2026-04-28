<?php
// Check if already logged in
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BRCT Bharat Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    
    <!-- Dynamic Base URL Configuration (MUST be before other scripts) -->
    <script src="../assets/js/config.js"></script>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <h1><i class="fas fa-lock-open"></i></h1>
            <h1>Admin Login</h1>
            <p>BRCT Bharat Trust - Management Portal</p>
        </div>

        <!-- Body -->
        <div class="login-body">
            <!-- Alert Messages -->
            <div id="alert-container"></div>

            <!-- Demo Credentials Info -->
            <div class="login-info">
                <strong>Demo Credentials:</strong>
                Username: <code>admin</code><br>
                Password: <code>password123</code>
            </div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)">
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
                            placeholder="Enter your username"
                            required
                            autocomplete="username"
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
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="remember-me">
                    <input type="checkbox" id="rememberMe" name="rememberMe">
                    <label for="rememberMe">Remember me for 30 days</label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>Secured Admin Portal • Session Timeout: 30 minutes</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
