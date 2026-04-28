<?php
// Direct access to Admin API blocked
header('HTTP/1.0 403 Forbidden');
header('Content-Type: application/json');
die(json_encode(['error' => 'Access Denied - Admin API only']));
?>
