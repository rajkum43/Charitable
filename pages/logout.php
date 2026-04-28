<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookies if set
setcookie('member_remember', '', time() - 3600, '/');
setcookie('member_id', '', time() - 3600, '/');

// Redirect to login page with success message
header('Location: login.php?message=' . urlencode('आप सफलतापूर्वक लॉगआउट हो गए हैं') . '&type=success');
exit;
?>
