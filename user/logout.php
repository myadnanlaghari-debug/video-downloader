<?php
/**
 * User Logout Handler
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

startSecureSession();

// Destroy session
session_unset();
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: ../login.php?logout=success');
exit;
?>
