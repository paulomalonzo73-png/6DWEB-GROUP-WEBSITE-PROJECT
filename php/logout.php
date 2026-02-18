<?php
/* ============================================
   php/logout.php
   Destroys session and redirects to login page
   ============================================ */
session_start();
session_destroy();
header('Location: ../index.php');
exit;
?>
