<?php
// API to get all districts from up_block_directory table
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Fetch all unique districts
    $sql = "SELECT DISTINCT district FROM up_block_directory ORDER BY district ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $districts = [];
    while ($row = $result->fetch_assoc()) {
        $districts[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'districts' => $districts
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
