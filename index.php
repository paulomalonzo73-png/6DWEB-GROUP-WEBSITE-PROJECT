<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: app.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Forge Your Limits</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/landing.css">
</head>
<body>

<nav id="navbar">
    <a class="logo" href="#"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <button class="btn btn-ghost" onclick="openModal('login')">Log In</button>
        <button class="btn btn-primary" onclick="openModal('register')">Start Free</button>
    </div>
</nav>

<section id="hero">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>
    <div class="hero-eyebrow">// TRANSFORM YOUR BODY. REDEFINE YOUR LIMITS.</div>
    <h1 class="hero-title">
        LIMIT<span class="accent">LESS</span><br>
        <span class="stroke">FITNESS</span>
    </h1>
    <p class="hero-sub">
        AI-powered workout and nutrition plans tailored precisely to your body,
        your goals, and your lifestyle. No guessing. Just results.
    </p>
    <div class="hero-cta">
        <button class="btn btn-primary btn-xl" onclick="openModal('register')">Get Your Plan Free</button>
        <button class="btn btn-ghost btn-xl" onclick="openModal('login')">Sign In</button>
    </div>
    <div class="hero-stats">
        <div class="stat-item"><div class="num">4+</div><div class="label">Goal Types</div></div>
        <div class="stat-item"><div class="num">7-Day</div><div class="label">Full Schedule</div></div>
        <div class="stat-item"><div class="num">100%</div><div class="label">Personalized</div></div>
        <div class="stat-item"><div class="num">Gym+Home</div><div class="label">Both Supported</div></div>
    </div>
</section>

<!-- LOGIN MODAL -->
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
        <button class="btn-auth" onclick="handleLogin()">LOG IN →</button>
        <div class="modal-footer">
            No account? <a href="#" onclick="switchModal('login','register')">Sign up here</a>
        </div>
    </div>
</div>

<!-- REGISTER MODAL -->
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
        <button class="btn-auth" onclick="handleRegister()">CREATE ACCOUNT →</button>
        <div class="modal-footer">
            Already have an account? <a href="#" onclick="switchModal('register','login')">Log in</a>
        </div>
    </div>
</div>

<script src="js/login.js"></script>
</body>
</html>
