<?php
// Database table creation for members and payment receipts
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Members Table
$sql_members = "CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id VARCHAR(8) NOT NULL UNIQUE COMMENT 'Last 8 digits of Aadhar',
    login_id VARCHAR(8) NOT NULL UNIQUE COMMENT 'Login ID (Last 8 digits of Aadhar)',
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    aadhar_number VARCHAR(12) NOT NULL UNIQUE,
    father_husband_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    mobile_number VARCHAR(10) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    office_name VARCHAR(100),
    office_address TEXT,
    state VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    block VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    permanent_address TEXT NOT NULL,
    status INT DEFAULT 0 COMMENT '0=Pending, 1=Approved, 2=Rejected',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(member_id),
    INDEX(login_id),
    INDEX(aadhar_number),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Create Payment Receipts Table
$sql_receipts = "CREATE TABLE IF NOT EXISTS payment_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id VARCHAR(8) NOT NULL,
    aadhar_number VARCHAR(12) NOT NULL,
    receipt_file_name VARCHAR(255) NOT NULL,
    receipt_file_path VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    INDEX(member_id),
    INDEX(aadhar_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Execute queries
if ($conn->query($sql_members) === TRUE) {
    echo "Members table created or already exists.<br>";
} else {
    echo "Error creating members table: " . $conn->error . "<br>";
}

if ($conn->query($sql_receipts) === TRUE) {
    echo "Payment Receipts table created or already exists.<br>";
} else {
    echo "Error creating payment receipts table: " . $conn->error . "<br>";
}

echo "<br><strong>Database tables ready!</strong>";
$conn->close();
?>
