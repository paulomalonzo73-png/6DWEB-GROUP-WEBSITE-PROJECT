<?php
/* ============================================
   php/admin_check.php
   MEMBER 4 — Role-Based Access Control
   Include at the TOP of every admin-only page.
   Blocks non-admins and unauthenticated users.
   ============================================ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Not logged in → send to login page
    header('Location: ../index.php');
    exit;
}

if (empty($_SESSION['is_admin'])) {
    // Logged in but not admin → 403 + redirect to app
    http_response_code(403);
    header('Location: ../app.php?error=access_denied');
    exit;
}
?>
