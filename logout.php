<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();

require_once __DIR__ . '/app/config/url.php';
header('Location: ' . app_url('/login.php'));

exit;
?>
