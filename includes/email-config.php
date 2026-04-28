<?php
// PHPMailer Configuration for BRCT Bharat Trust

// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // TLS port
define('SMTP_USER', 'brctbharat@gmail.com'); //brctbharat@gmail.com Your Gmail address
define('SMTP_PASS', 'uugmgkfuhsjsupnm'); // "uugmgkfuhsjsupnm" Gmail App Password (not regular password) - Generate from Google Account
define('FROM_EMAIL', 'brctbharat@gmail.com');//brctbharat@gmail.com
define('FROM_NAME', 'ssv tech mitra');// BRCT BHARAT TRUST
define('ADMIN_EMAIL', 'brctbharat@gmail.com');//brctbharat@gmail.com

// Check if PHPMailer is available
$phpMailerPath = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($phpMailerPath)) {
    // If PHPMailer not installed via composer, you can download the files directly
    define('PHPMAILER_INSTALLED', false);
} else {
    define('PHPMAILER_INSTALLED', true);
}
?>
