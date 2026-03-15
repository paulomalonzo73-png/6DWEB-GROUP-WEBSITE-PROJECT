<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/log_activity.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], $_SESSION['username'], 'logout', 'User signed out');
}

session_destroy();
header('Location: ../index.php');
exit;
?>
