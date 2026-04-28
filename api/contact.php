<?php
// Contact Form API - Handle contact form submissions
session_start();
require_once '../includes/config.php';
require_once '../includes/email-config.php';

// Load PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = "नाम आवश्यक है";
    } elseif (strlen($name) < 3) {
        $errors[] = "नाम कम से कम 3 वर्ण होना चाहिए";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "वैध ईमेल आवश्यक है";
    }

    if (empty($phone) || !preg_match('/^\d{10}$/', $phone)) {
        $errors[] = "वैध 10 अंकों का फोन नंबर आवश्यक है";
    }

    if (empty($subject)) {
        $errors[] = "विषय चुनना आवश्यक है";
    }

    if (empty($message)) {
        $errors[] = "संदेश आवश्यक है";
    } elseif (strlen($message) < 10) {
        $errors[] = "संदेश कम से कम 10 वर्ण होना चाहिए";
    }

    // If there are errors, return them
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(", ", $errors)
        ]);
        exit;
    }

    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'डेटाबेस कनेक्शन त्रुटि'
        ]);
        exit;
    }

    // Check if contacts table exists, create if not
    $table_check = $conn->query("SHOW TABLES LIKE 'contact_messages'");
    if ($table_check->num_rows == 0) {
        $create_table_sql = "CREATE TABLE contact_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(10) NOT NULL,
            subject VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
            reply_message TEXT,
            replied_at TIMESTAMP NULL
        )";

        if (!$conn->query($create_table_sql)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'टेबल निर्माण में विफल'
            ]);
            exit;
        }
    }

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

        if ($stmt->execute()) {
            $messageId = $stmt->insert_id;
            $emailsSent = [];
            
            try {
                // Initialize PHPMailer
                $mail = new PHPMailer(true);
                
                // Server settings
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->Port = SMTP_PORT;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->CharSet = 'UTF-8';
                
                // Send email to user
                try {
                    $mail->clearAllRecipients();
                    $mail->setFrom(FROM_EMAIL, FROM_NAME);
                    $mail->addAddress($email, $name);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'आपका संदेश प्राप्त - ' . FROM_NAME;
                    $mail->Body = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <style>
                            body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
                            .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 10px; }
                            .header { color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
                            .content { color: #333; line-height: 1.6; }
                            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #999; font-size: 0.9rem; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>✓ आपका संदेश प्राप्त हुआ</h2>
                            </div>
                            <div class='content'>
                                <p>नमस्ते " . htmlspecialchars($name) . ",</p>
                                <p>आपकी संदेश हमारे पास सुरक्षित रूप से प्राप्त हो गया है। हम आपके संदेश की समीक्षा करेंगे और जल्द ही आपसे संपर्क करेंगे।</p>
                                <p><strong>आपके संदेश का विवरण:</strong></p>
                                <ul>
                                    <li><strong>नाम:</strong> " . htmlspecialchars($name) . "</li>
                                    <li><strong>ईमेल:</strong> " . htmlspecialchars($email) . "</li>
                                    <li><strong>फोन:</strong> " . htmlspecialchars($phone) . "</li>
                                </ul>
                                <p>धन्यवाद!</p>
                            </div>
                            <div class='footer'>
                                <p>" . FROM_NAME . "</p>
                            </div>
                        </div>
                    </body>
                    </html>";
                    $mail->AltBody = "आपका संदेश प्राप्त हुआ है।";
                    
                    if ($mail->send()) {
                        $emailsSent['user'] = '✓ Sent';
                    } else {
                        $emailsSent['user'] = '❌ Failed: ' . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    $emailsSent['user'] = '❌ Error: ' . $e->getMessage();
                }
                
                // Send email to admin
                try {
                    $mail->clearAllRecipients();
                    $mail->setFrom(FROM_EMAIL, FROM_NAME);
                    $mail->addAddress(ADMIN_EMAIL, 'Admin');
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'नया संपर्क संदेश - ' . htmlspecialchars($name);
                    $mail->Body = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .container { max-width: 600px; margin: 0 auto; }
                            .field { margin: 15px 0; padding: 10px; background-color: #f8f9fa; border-left: 3px solid #0d6efd; }
                            .field-label { font-weight: bold; color: #0d6efd; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>🔔 नया संपर्क संदेश</h2>
                            <div class='field'>
                                <span class='field-label'>नाम:</span> " . htmlspecialchars($name) . "
                            </div>
                            <div class='field'>
                                <span class='field-label'>ईमेल:</span> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a>
                            </div>
                            <div class='field'>
                                <span class='field-label'>फोन:</span> " . htmlspecialchars($phone) . "
                            </div>
                            <div class='field'>
                                <span class='field-label'>विषय:</span> " . htmlspecialchars($subject) . "
                            </div>
                            <div class='field'>
                                <span class='field-label'>संदेश:</span><br>
                                " . nl2br(htmlspecialchars($message)) . "
                            </div>
                        </div>
                    </body>
                    </html>";
                    $mail->AltBody = "नया संपर्क संदेश प्राप्त हुआ।";
                    
                    if ($mail->send()) {
                        $emailsSent['admin'] = '✓ Sent';
                    } else {
                        $emailsSent['admin'] = '❌ Failed: ' . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    $emailsSent['admin'] = '❌ Error: ' . $e->getMessage();
                }
                
            } catch (Exception $e) {
                $emailsSent['error'] = $e->getMessage();
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'आपका संदेश सफलतापूर्वक भेज दिया गया है। जल्द ही हम आपसे संपर्क करेंगे।',
                'message_id' => $messageId,
                'emails' => $emailsSent
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'संदेश भेजने में विफल। कृपया पुनः प्रयास करें।'
            ]);
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'डेटाबेस त्रुटि'
        ]);
    }

    $conn->close();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'केवल POST अनुरोध समर्थित हैं'
    ]);
}
?>
