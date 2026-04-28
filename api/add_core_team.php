<?php
// API - Add Core Team Member
header('Content-Type: application/json');

try {
    require_once '../includes/config.php';
    
    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Validate input
    $full_name = trim($_POST['full_name'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $post_name = trim($_POST['post_name'] ?? '');
    
    if (empty($full_name) || empty($mobile_number) || empty($post_name)) {
        throw new Exception('All fields are required');
    }
    
    // Validate mobile number (10-15 digits)
    if (!preg_match('/^\d{10,15}$/', str_replace([' ', '-', '+'], '', $mobile_number))) {
        throw new Exception('Invalid mobile number');
    }
    
    // Handle image upload
    if (!isset($_FILES['photo'])) {
        throw new Exception('Photo is required');
    }
    
    $photo = $_FILES['photo'];
    
    // Validate file type
    $allowed_types = ['image/png'];
    $file_type = mime_content_type($photo['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Only PNG files are allowed');
    }
    
    // Validate file size (50KB = 51200 bytes)
    if ($photo['size'] > 51200) {
        throw new Exception('File size must be less than 50KB');
    }
    
    // Create uploads directory if not exists
    $upload_dir = '../uploads/core-team/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }
    
    // Generate unique filename with timestamp (safe filename)
    $timestamp = time();
    // Remove special characters and limit to safe filename
    $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $full_name));
    // Limit to first 30 characters to avoid excessively long filenames
    $safe_name = substr($safe_name, 0, 30);
    // If empty after sanitization, use timestamp only
    if (empty($safe_name)) {
        $safe_name = 'member';
    }
    $filename = $safe_name . '_' . $timestamp . '.png';
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($photo['tmp_name'], $file_path)) {
        throw new Exception('Failed to upload image');
    }
    
    // Insert into database
    $query = "INSERT INTO core_team_members (full_name, mobile_number, post_name, photo, photo_size, status) 
              VALUES (:full_name, :mobile_number, :post_name, :photo, :photo_size, 'active')";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':full_name' => $full_name,
        ':mobile_number' => $mobile_number,
        ':post_name' => $post_name,
        ':photo' => $filename,
        ':photo_size' => $photo['size']
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Team member added successfully',
            'data' => [
                'id' => $pdo->lastInsertId(),
                'full_name' => $full_name,
                'mobile_number' => $mobile_number,
                'post_name' => $post_name,
                'photo' => $filename
            ]
        ]);
    } else {
        throw new Exception('Failed to save to database');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
