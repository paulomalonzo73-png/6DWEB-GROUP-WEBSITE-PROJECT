<?php
/* ============================================
   admin.php
   MEMBER 4 — Role-Based Access Control
   Admin-only dashboard. Blocked by admin_check.php
   for anyone without is_admin = 1 in session.
   ============================================ */
require_once 'php/config.php';
require_once 'php/admin_check.php';

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<!-- ======== NAVBAR ======== -->
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span class="admin-badge">ADMIN</span>
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--accent)">
            <strong><?php echo htmlspecialchars($username); ?></strong>
        </span>
        <a href="app.php" class="btn btn-ghost btn-sm">← App</a>
        <a href="php/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
    </div>
</nav>

<!-- ======== PAGE ======== -->
<main class="admin-page">

    <div class="admin-header">
        <div class="section-eyebrow">// MEMBER 4 FEATURE — ROLE-BASED ACCESS CONTROL</div>
        <h1 class="admin-title">ADMIN <span>PANEL</span></h1>
        <p class="admin-sub">
            Only accounts with <code>is_admin = 1</code> in the <code>users</code> table
            can access this page. The <code>php/admin_check.php</code> middleware enforces
            this on every request — returning HTTP 403 and redirecting otherwise.
        </p>
    </div>

    <!-- Toast -->
    <div id="adminToast" class="admin-toast" style="display:none;"></div>

    <!-- ======== SUMMARY CARDS ======== -->
    <div class="summary-grid" id="summaryGrid">
        <div class="summary-card loading-card">Loading…</div>
    </div>

    <!-- ======== USERS TABLE ======== -->
    <section class="users-section">
        <div class="users-section-header">
            <h2 class="users-title">ALL <span>USERS</span></h2>
            <button class="btn btn-ghost btn-sm" onclick="loadUsers()">↻ Refresh</button>
        </div>

        <div id="usersTableWrap">
            <div class="admin-loading">Loading users…</div>
        </div>
    </section>

</main>

<script src="js/admin.js"></script>
</body>
</html>
