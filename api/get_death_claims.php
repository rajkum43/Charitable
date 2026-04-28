<?php
/**
 * Get Death Claims - API Endpoint
 * Fetches death claims with filtering and search
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../includes/config.php';

try {
    // Fix: Migrate status field from TINYINT to VARCHAR and set defaults
    try {
        // Get current column type
        $stmt = $pdo->query("DESCRIBE `death_claims`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $statusColumn = null;
        $claimIdColumn = null;
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'status') {
                $statusColumn = $col;
            }
            if ($col['Field'] === 'claim_id') {
                $claimIdColumn = $col;
            }
        }
        
        // If claim_id column doesn't exist, add it
        if (!$claimIdColumn) {
            $pdo->exec("ALTER TABLE `death_claims` ADD COLUMN `claim_id` VARCHAR(50) UNIQUE DEFAULT NULL");
            // Generate claim_id for existing records
            $pdo->exec("UPDATE `death_claims` SET `claim_id` = CONCAT('BRCT-D', DATE_FORMAT(created_at, '%Y%m%d'), LPAD(id, 4, '0')) WHERE `claim_id` IS NULL");
        }
        
        // If status is not VARCHAR, convert it
        if ($statusColumn && strpos($statusColumn['Type'], 'varchar') === false) {
            $pdo->exec("ALTER TABLE `death_claims` MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'Pending'");
            // Update existing NULL and 0 values to 'Pending'
            $pdo->exec("UPDATE `death_claims` SET `status` = 'Pending' WHERE `status` IS NULL OR `status` = '' OR `status` = '0'");
        }
        
        // Populate age from age_at_death if age is NULL
        $pdo->exec("UPDATE `death_claims` SET `age` = `age_at_death` WHERE `age` IS NULL AND `age_at_death` IS NOT NULL");
        
        // Populate relation_type from nominee_relation if relation_type is NULL
        $pdo->exec("UPDATE `death_claims` SET `relation_type` = `nominee_relation` WHERE `relation_type` IS NULL AND `nominee_relation` IS NOT NULL");
    } catch (Exception $migrationError) {
        // Continue anyway - migration might not be needed
    }
    
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Build query
    $query = "SELECT * FROM death_claims WHERE 1=1";
    $params = [];
    
    // Filter by status
    if (!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    // Search filter
    if (!empty($search)) {
        $query .= " AND (claim_id LIKE ? OR member_id LIKE ? OR full_name LIKE ? OR nominee_name LIKE ?)";
        $search_term = "%{$search}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Order by latest first
    $query .= " ORDER BY created_at DESC LIMIT 500";
    
    // Prepare and execute statement
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'claims' => $claims,
        'count' => count($claims)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching death claims',
        'error' => $e->getMessage()
    ]);
}
?>
