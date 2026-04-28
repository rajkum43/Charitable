<?php
// Core Team Members - Create Table
require_once '../includes/config.php';

// SQL to create core_team_members table
$sql = "CREATE TABLE IF NOT EXISTS `core_team_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `mobile_number` VARCHAR(20) NOT NULL,
  `post_name` VARCHAR(100) NOT NULL,
  `photo` VARCHAR(255) NOT NULL,
  `photo_size` INT,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    if ($pdo->exec($sql)) {
        echo json_encode(['success' => true, 'message' => 'Core Team Members table created successfully']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
