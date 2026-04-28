<?php
// Redirect to login page if not authenticated
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit;
}
else{
// Direct access blocked
header('Location: ../index.php');
die('Access Denied');

}

