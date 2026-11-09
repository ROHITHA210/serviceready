<?php session_start();

// Remove all session data.
$_SESSION = [];

// Destroy the current session.
session_destroy();

// Return to the admin login page.
header('Location: login.php');
exit;
