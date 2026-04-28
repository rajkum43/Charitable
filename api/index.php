<?php
// Direct access to API blocked
header('HTTP/1.0 403 Forbidden');
header('Content-Type: application/json');
die(json_encode(['error' => 'Access Denied - API calls only']));
?>
