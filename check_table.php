<?php
require_once 'includes/config.php';

// Check table structure
$result = $pdo->query("DESCRIBE members");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

echo "Members table columns:\n";
foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
