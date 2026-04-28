<?php
// Mail Helper Class using PHPMailer
require_once __DIR__ . '/email-config.php';

class MailHelper {
    private $mail;
    private $isConfigured = false;
    private $useComposer = false;

    public function __construct() {
        $this->initializeMailer();
    }

    private function initializeMailer() {
        try {
            // Try to load PHPMailer from composer (Recommended)
            if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
                require_once __DIR__ . '/../../vendor/autoload.php';
                $this->useComposer = true;
            } elseif (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
                // Or load from local directory (Manual installation)
                require_once __DIR__ . '/PHPMailer/PHPMailer.php';
                require_once __DIR__ . '/PHPMailer/SMTP.php';
                require_once __DIR__ . '/PHPMailer/Exception.php';
            }

            // Check if PHPMailer class exists
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mailer = 'PHPMailer\PHPMailer\PHPMailer';
                $this->mail = new $mailer(true);
                
                // SMTP Configuration
                $this->mail->isSMTP();
                $this->mail->Host = SMTP_HOST;
                $this->mail->Port = SMTP_PORT;
                $this->mail->SMTPAuth = true;
                $this->mail->SMTPSecure = 'tls';
                $this->mail->Username = SMTP_USER;
                $this->mail->Password = SMTP_PASS;
                $this->mail->CharSet = 'UTF-8';
                
                $this->mail->setFrom(FROM_EMAIL, FROM_NAME);
                $this->isConfigured = true;
            } else {
                // PHPMailer not installed - use native mail() as fallback
                error_log('PHPMailer not found. Using native PHP mail() function as fallback.');
                $this->isConfigured = true; // Mark as configured to use fallback
            }

        } catch (Exception $e) {
            error_log('MailHelper initialization error: ' . $e->getMessage());
            $this->isConfigured = true; // Use fallback anyway
        }
    }

    /**
     * Send confirmation email to user
     */
    public function sendUserConfirmation($userName, $userEmail, $message, $subject_type) {
        if (!$this->isConfigured) {
            return false;
        }

        try {
            // Check if PHPMailer is available
            if ($this->mail && method_exists($this->mail, 'send')) {
                // Use PHPMailer
                $this->mail->clearAllRecipients();
                $this->mail->addAddress($userEmail, $userName);
                
                $this->mail->isHTML(true);
                $this->mail->Subject = 'आपका संदेश प्राप्त - BRCT Bharat Trust';
                $this->mail->Body = $this->getUserConfirmationTemplate($userName, $userEmail, $message, $subject_type);
                $this->mail->AltBody = strip_tags($this->mail->Body);

                return $this->mail->send();
            } else {
                // Fallback to native PHP mail()
                return $this->sendViaNativeMail(
                    $userEmail, 
                    'आपका संदेश प्राप्त - BRCT Bharat Trust',
                    $this->getUserConfirmationTemplate($userName, $userEmail, $message, $subject_type)
                );
            }

        } catch (Exception $e) {
            error_log('User confirmation email error: ' . $e->getMessage());
            // Try native mail as last resort
            return $this->sendViaNativeMail(
                $userEmail,
                'आपका संदेश प्राप्त - BRCT Bharat Trust',
                $this->getUserConfirmationTemplate($userName, $userEmail, $message, $subject_type)
            );
        }
    }

    /**
     * Send notification email to admin
     */
    public function sendAdminNotification($name, $email, $phone, $subject, $message) {
        if (!$this->isConfigured) {
            return false;
        }

        try {
            // Check if PHPMailer is available
            if ($this->mail && method_exists($this->mail, 'send')) {
                // Use PHPMailer
                $this->mail->clearAllRecipients();
                $this->mail->addAddress(ADMIN_EMAIL, 'Admin');
                
                $this->mail->isHTML(true);
                $this->mail->Subject = 'नया संपर्क संदेश - ' . $name;
                $this->mail->Body = $this->getAdminNotificationTemplate($name, $email, $phone, $subject, $message);
                $this->mail->AltBody = strip_tags($this->mail->Body);

                return $this->mail->send();
            } else {
                // Fallback to native PHP mail()
                return $this->sendViaNativeMail(
                    ADMIN_EMAIL,
                    'नया संपर्क संदेश - ' . $name,
                    $this->getAdminNotificationTemplate($name, $email, $phone, $subject, $message)
                );
            }

        } catch (Exception $e) {
            error_log('Admin notification email error: ' . $e->getMessage());
            // Try native mail as last resort
            return $this->sendViaNativeMail(
                ADMIN_EMAIL,
                'नया संपर्क संदेश - ' . $name,
                $this->getAdminNotificationTemplate($name, $email, $phone, $subject, $message)
            );
        }
    }

    /**
     * Send email using native PHP mail() function
     * This is a fallback when PHPMailer is not available
     */
    private function sendViaNativeMail($to, $subject, $body) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . FROM_EMAIL . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        return @mail($to, $subject, $body, $headers);
    }

    /**
     * Get HTML template for user confirmation email
     */
    private function getUserConfirmationTemplate($name, $email, $message, $subject_type) {
        $subjectLabels = [
            'membership' => 'सदस्यता संबंधी',
            'benefits' => 'सुविधाओं के बारे में',
            'complaint' => 'शिकायत',
            'suggestion' => 'सुझाव',
            'donation' => 'दान संबंधी',
            'other' => 'अन्य'
        ];

        $subject_display = $subjectLabels[$subject_type] ?? 'अन्य';

        return "
        <!DOCTYPE html>
        <html dir='ltr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f5f5f5; 
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background-color: white; 
                    padding: 20px; 
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header { 
                    color: #0d6efd; 
                    border-bottom: 3px solid #0d6efd; 
                    padding-bottom: 15px; 
                    margin-bottom: 20px; 
                }
                .header h2 {
                    margin: 0;
                    font-size: 24px;
                }
                .content { 
                    color: #333; 
                    line-height: 1.6;
                    font-size: 14px;
                }
                .info-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #0d6efd;
                    padding: 15px;
                    margin: 15px 0;
                    border-radius: 5px;
                }
                .info-box strong {
                    color: #0d6efd;
                }
                .footer { 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 1px solid #e9ecef; 
                    color: #999; 
                    font-size: 12px;
                    text-align: center;
                }
                .footer p {
                    margin: 5px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✓ आपका संदेश प्राप्त हुआ</h2>
                </div>
                <div class='content'>
                    <p>नमस्ते <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    
                    <p>आपकी संदेश हमारे पास सुरक्षित रूप से प्राप्त हो गया है। हम आपके संदेश की समीक्षा करेंगे और आपकी समस्या के समाधान के लिए जल्द ही आपसे संपर्क करेंगे।</p>
                    
                    <div class='info-box'>
                        <p><strong>📋 आपके संदेश का विवरण:</strong></p>
                        <p><strong>विषय:</strong> " . htmlspecialchars($subject_display) . "<br>
                        <strong>ईमेल:</strong> " . htmlspecialchars($email) . "</p>
                    </div>
                    
                    <p><strong>धन्यवाद आपकी भरोसेमंदी के लिए!</strong></p>
                    <p style='color: #666; font-size: 13px; margin-top: 20px;'>
                        यदि आपको किसी भी प्रकार की समस्या आती है, तो कृपया हमें <strong>" . htmlspecialchars(ADMIN_EMAIL) . "</strong> पर ईमेल भेजें।
                    </p>
                </div>
                <div class='footer'>
                    <p><strong>BRCT Bharat Trust</strong></p>
                    <p>समाज सेवा केंद्र, राज नगर, कानपुर</p>
                    <p style='margin-top: 15px; color: #bbb;'>यह एक स्वचालित संदेश है, कृपया इसका उत्तर न दें।</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get HTML template for admin notification email
     */
    private function getAdminNotificationTemplate($name, $email, $phone, $subject, $message) {
        $subjectLabels = [
            'membership' => 'सदस्यता संबंधी',
            'benefits' => 'सुविधाओं के बारे में',
            'complaint' => 'शिकायत',
            'suggestion' => 'सुझाव',
            'donation' => 'दान संबंधी',
            'other' => 'अन्य'
        ];

        $subject_display = $subjectLabels[$subject] ?? 'अन्य';

        return "
        <!DOCTYPE html>
        <html dir='ltr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f5f5f5; 
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background-color: white; 
                    padding: 20px;
                    border-radius: 10px;
                }
                .header { 
                    background-color: #0d6efd; 
                    color: white; 
                    padding: 15px; 
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                .header h2 {
                    margin: 0;
                    font-size: 20px;
                }
                .content { 
                    color: #333; 
                    line-height: 1.6;
                }
                .field {
                    margin: 15px 0;
                    padding: 10px;
                    background-color: #f8f9fa;
                    border-left: 3px solid #0d6efd;
                }
                .field-label {
                    font-weight: bold;
                    color: #0d6efd;
                }
                .message-box {
                    background-color: #fffbea;
                    border: 1px solid #ffeaa7;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 15px 0;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔔 नया संपर्क संदेश प्राप्त</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='field-label'>👤 नाम:</span> " . htmlspecialchars($name) . "
                    </div>
                    <div class='field'>
                        <span class='field-label'>📧 ईमेल:</span> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a>
                    </div>
                    <div class='field'>
                        <span class='field-label'>📱 फोन:</span> " . htmlspecialchars($phone) . "
                    </div>
                    <div class='field'>
                        <span class='field-label'>📌 विषय:</span> " . htmlspecialchars($subject_display) . "
                    </div>
                    <div class='field'>
                        <span class='field-label'>📝 संदेश:</span>
                    </div>
                    <div class='message-box'>
" . htmlspecialchars($message) . "
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>
