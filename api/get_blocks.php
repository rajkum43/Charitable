<?php
// API to get blocks for a specific district from up_block_directory table
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Get district from query parameter
    $district = isset($_GET['district']) ? trim($_GET['district']) : '';
    
    if (empty($district)) {
        throw new Exception("District parameter is required");
    }
    
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Fetch blocks for the selected district
    $district_escaped = $conn->real_escape_string($district);
    $sql = "SELECT DISTINCT block FROM up_block_directory 
            WHERE district = '$district_escaped' 
            ORDER BY block ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $blocks = [];
    while ($row = $result->fetch_assoc()) {
        $blocks[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'district' => $district,
        'blocks' => $blocks
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
