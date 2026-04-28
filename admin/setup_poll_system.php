<?php
// Poll System Database Setup Script
// Run this file once to create all necessary tables

require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$messages = [];
$errors = [];

// Create poll_applications table
$sql1 = "CREATE TABLE IF NOT EXISTS `poll_applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` VARCHAR(50) NOT NULL,
  `type` ENUM('vivah', 'death') NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `application_details` LONGTEXT,
  `approved_date` DATE NULL,
  `poll_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`),
  UNIQUE KEY `unique_application` (`member_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql1) === TRUE) {
    $messages[] = "✓ Table 'poll_applications' बनाया गया";
} else {
    $errors[] = "✗ poll_applications: " . $conn->error;
}

// Create polls table
$sql2 = "CREATE TABLE IF NOT EXISTS `polls` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `poll_name` VARCHAR(100) NOT NULL,
  `poll_code` VARCHAR(20) UNIQUE NOT NULL,
  `application_id` INT NOT NULL,
  `beneficiary_id` VARCHAR(50) NOT NULL,
  `beneficiary_name` VARCHAR(100) NOT NULL,
  `poll_type` ENUM('vivah', 'death') NOT NULL,
  `total_members` INT NOT NULL,
  `donation_amount` INT DEFAULT 50,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `status` ENUM('Active', 'Closed', 'Completed') DEFAULT 'Active',
  `total_collected` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `poll_applications`(`id`),
  FOREIGN KEY (`beneficiary_id`) REFERENCES `members`(`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql2) === TRUE) {
    $messages[] = "✓ Table 'polls' बनाया गया";
} else {
    $errors[] = "✗ polls: " . $conn->error;
}

// Create poll_members table
$sql3 = "CREATE TABLE IF NOT EXISTS `poll_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `poll_id` INT NOT NULL,
  `member_id` VARCHAR(50) NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
  `payment_date` DATETIME NULL,
  `utr_number` VARCHAR(50) NULL,
  `transaction_id` VARCHAR(100) NULL,
  `screenshot_path` VARCHAR(255) NULL,
  `paid_amount` INT DEFAULT 0,
  `payment_method` ENUM('UPI', 'Bank Transfer', 'Cheque') NULL,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`),
  UNIQUE KEY `unique_poll_member` (`poll_id`, `member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql3) === TRUE) {
    $messages[] = "✓ Table 'poll_members' बनाया गया";
} else {
    $errors[] = "✗ poll_members: " . $conn->error;
}

// Create poll_payments table
$sql4 = "CREATE TABLE IF NOT EXISTS `poll_payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `poll_member_id` INT NOT NULL,
  `poll_id` INT NOT NULL,
  `member_id` VARCHAR(50) NOT NULL,
  `amount` INT NOT NULL,
  `payment_date` DATETIME,
  `payment_method` ENUM('UPI', 'Bank Transfer', 'Cheque', 'Cash') NULL,
  `utr_number` VARCHAR(50),
  `transaction_id` VARCHAR(100),
  `screenshot_path` VARCHAR(255),
  `status` ENUM('Pending', 'Verified', 'Failed') DEFAULT 'Pending',
  `verified_by_admin` INT NULL,
  `verified_date` DATETIME NULL,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`poll_member_id`) REFERENCES `poll_members`(`id`),
  FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql4) === TRUE) {
    $messages[] = "✓ Table 'poll_payments' बनाया गया";
} else {
    $errors[] = "✗ poll_payments: " . $conn->error;
}

// Create indexes
$conn->query("CREATE INDEX idx_poll_status ON polls(status)");
$conn->query("CREATE INDEX idx_poll_member_status ON poll_members(payment_status)");
$conn->query("CREATE INDEX idx_poll_payment_status ON poll_payments(status)");
$conn->query("CREATE INDEX idx_member_poll ON poll_members(member_id, poll_id)");

$messages[] = "✓ Indexes बनाए गए";

// Create upload directories
$upload_dir = '../uploads/poll_payments/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    $messages[] = "✓ Upload directory बनाई गई";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-database me-2"></i>Poll System Database Setup</h3>
                    </div>
                    <div class="card-body">
                        <h5>डेटाबेस सेटअप परिणाम</h5>
                        
                        <?php if (!empty($messages)): ?>
                            <div class="alert alert-success" role="alert">
                                <h6>सफल ऑपरेशन:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($messages as $msg): ?>
                                        <li><?php echo $msg; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <h6>त्रुटियां:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $err): ?>
                                        <li><?php echo $err; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <h6>अगले कदम:</h6>
                        <ul>
                            <li>Dashboard प्रशासन पैनल (<strong>/admin/</strong>) में जाएं</li>
                            <li>सदस्य आवेदन को देखने और अनुमोदित करने के लिए <strong>poll-applications.php</strong> जाएं</li>
                            <li>सदस्यों को सहयोग पृष्ठ देखें: <strong>/member/sahyog.php</strong></li>
                            <li>पारदर्शिता डैशबोर्ड देखें: <strong>/pages/poll-transparency.php</strong></li>
                        </ul>

                        <div class="mt-4">
                            <a href="../admin/admin-poll-applications.php" class="btn btn-primary me-2">
                                <i class="fas fa-arrow-right me-2"></i>प्रशासन पैनल
                            </a>
                            <a href="../member/sahyog.php" class="btn btn-success me-2">
                                <i class="fas fa-arrow-right me-2"></i>सहयोग पृष्ठ
                            </a>
                            <a href="../pages/poll-transparency.php" class="btn btn-info">
                                <i class="fas fa-arrow-right me-2"></i>पारदर्शिता डैशबोर्ड
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
