<?php
require_once 'includes/config.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `renew` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(20) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `renew_date` date NOT NULL,
  `renew_exp_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'renew' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>