<!-- ============================================
     includes/header.php
     Site <head>, Google Fonts, all CSS links,
     Navbar, and Auth Modals
     ============================================ -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Forge Your Limits</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">

    <!-- CSS Files -->
    <link rel="stylesheet" href="css/variables.css">  <!-- base, resets, shared buttons -->
    <link rel="stylesheet" href="css/navbar.css">     <!-- nav + welcome bar + hero -->
    <link rel="stylesheet" href="css/auth.css">       <!-- login & register modals -->
    <link rel="stylesheet" href="css/form.css">       <!-- multi-step profile form -->
    <link rel="stylesheet" href="css/results.css">    <!-- nutrition + schedule + responsive -->
</head>
<body>

<!-- LOADING OVERLAY -->
<div class="loading-overlay" id="loading">
    <div class="loader-text">GENERATING YOUR PLAN...</div>
    <div class="loader-bar"></div>
</div>

<!-- ======== NAVBAR ======== -->
<nav id="navbar">
    <a class="logo" href="#"><span>LIMIT</span>LESS</a>
    <div class="nav-actions" id="navActions">
        <button class="btn btn-ghost" onclick="openModal('login')">Log In</button>
        <button class="btn btn-primary" onclick="openModal('register')">Start Free</button>
    </div>
</nav>

<!-- ======== LOGIN MODAL ======== -->
<div class="modal-overlay" id="loginModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('login')">×</button>
        <div class="modal-title">WELCOME <span>BACK</span></div>
        <div class="modal-sub">Enter your credentials to continue your journey.</div>
        <div id="loginAlert"></div>
        <div class="form-group">
            <label class="form-label">Username or Email</label>
            <input type="text" class="form-control" id="loginUser" placeholder="your_username" autocomplete="username">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="loginPass" placeholder="••••••••" autocomplete="current-password">
        </div>
        <button class="btn btn-primary" style="width:100%;padding:16px;" onclick="handleLogin()">LOG IN →</button>
        <div class="modal-footer" style="margin-top:20px;">
            No account? <a href="#" onclick="switchModal('login','register')">Sign up here</a>
        </div>
    </div>
</div>

<!-- ======== REGISTER MODAL ======== -->
<div class="modal-overlay" id="registerModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('register')">×</button>
        <div class="modal-title">CREATE <span>ACCOUNT</span></div>
        <div class="modal-sub">Join thousands building their limitless physique.</div>
        <div id="registerAlert"></div>
        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" id="regUser" placeholder="your_username" autocomplete="username">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="regEmail" placeholder="you@email.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="regPass" placeholder="min. 6 characters" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="regConfirm" placeholder="repeat password" autocomplete="new-password">
        </div>
        <button class="btn btn-primary" style="width:100%;padding:16px;" onclick="handleRegister()">CREATE ACCOUNT →</button>
        <div class="modal-footer" style="margin-top:20px;">
            Already have an account? <a href="#" onclick="switchModal('register','login')">Log in</a>
        </div>
    </div>
</div>
