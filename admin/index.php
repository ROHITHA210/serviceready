<?php session_start();

// Check whether the admin is logged in.
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect directly to the Manage Tests page.
header('Location: manage-tests.php');
exit;
