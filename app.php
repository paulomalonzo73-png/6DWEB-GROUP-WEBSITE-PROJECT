<?php
/* ============================================
   app.php — LIMITLESS Main App
   Only accessible after login.
   Contains: navbar, profile form, results.
   ============================================ */
session_start();

// If NOT logged in, send back to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Build Your Plan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/results.css">
</head>
<body>

<!-- ======== LOADING OVERLAY ======== -->
<div class="loading-overlay" id="loading">
    <div class="loader-text">GENERATING YOUR PLAN...</div>
    <div class="loader-bar"></div>
</div>

<!-- ======== NAVBAR ======== -->
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--accent)">
           <strong> <?php echo htmlspecialchars($username); ?> </strong>
        </span>
        <a href="php/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
    </div>
</nav>

<!-- ======== WELCOME BAR ======== -->
<div class="welcome-bar" style="margin-top:64px;">
    <span>Welcome back, <strong><?php echo htmlspecialchars($username); ?></strong> — ready to crush it?</span>
</div>

<!-- ======== PROFILE FORM ======== -->
<?php include 'includes/form.php'; ?>

<!-- ======== RESULTS ======== -->
<?php include 'includes/results.php'; ?>


<!-- JavaScript -->
<script src="js/form.js"></script>
<script src="js/plan.js"></script>

</body>
</html>
