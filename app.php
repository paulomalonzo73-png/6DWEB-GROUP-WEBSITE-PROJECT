<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'] ?? 0;
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
    <style>
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 7px 14px;
            cursor: pointer;
            font-family: 'DM Mono', monospace;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            transition: border-color 0.2s;
        }
        .profile-btn:hover { border-color: var(--accent); color: var(--accent); }

        .profile-avatar {
            width: 26px;
            height: 26px;
            background: var(--accent);
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        /* Admin avatar is red so it's visually distinct */
        .profile-avatar.admin-avatar { background: var(--danger); color: #fff; }

        .drawer-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 900;
            backdrop-filter: blur(4px);
        }
        .drawer-overlay.active { display: block; }

        .drawer {
            position: fixed;
            top: 0; right: -360px;
            width: 340px;
            height: 100vh;
            background: var(--surface);
            border-left: 1px solid var(--border);
            z-index: 901;
            transition: right 0.35s cubic-bezier(0.4,0,0.2,1);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .drawer.active { right: 0; }

        .drawer-header {
            padding: 28px 28px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .drawer-avatar {
            width: 48px; height: 48px;
            background: var(--accent);
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .drawer-avatar.admin-avatar { background: var(--danger); color: #fff; }

        .drawer-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            letter-spacing: 0.05em;
            line-height: 1;
        }
        .drawer-name span { color: var(--accent); }
        .drawer-name span.admin-name { color: var(--danger); }

        .drawer-sub {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
            color: var(--muted);
            letter-spacing: 0.1em;
            margin-top: 4px;
        }
        .admin-role-badge {
            display: inline-block;
            background: rgba(255,59,59,0.15);
            color: var(--danger);
            border: 1px solid var(--danger);
            font-family: 'DM Mono', monospace;
            font-size: 0.6rem;
            letter-spacing: 0.15em;
            padding: 2px 8px;
            margin-top: 5px;
            text-transform: uppercase;
        }

        .drawer-close {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 1.4rem;
            cursor: pointer;
            transition: color 0.2s;
            flex-shrink: 0;
        }
        .drawer-close:hover { color: var(--text); }

        .drawer-section-label {
            font-family: 'DM Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 20px 28px 8px;
        }

        .drawer-menu { padding: 0 16px; }

        .drawer-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 12px;
            text-decoration: none;
            color: var(--text);
            border: 1px solid transparent;
            margin-bottom: 4px;
            transition: background 0.15s, border-color 0.15s;
        }
        .drawer-item:hover { background: var(--surface2); border-color: var(--border); }
        .drawer-item:hover .drawer-item-icon { color: var(--accent); }

        /* Admin items glow red on hover */
        .drawer-item.admin-item:hover { border-color: rgba(255,59,59,0.4); }
        .drawer-item.admin-item:hover .drawer-item-icon { color: var(--danger); }

        .drawer-item-icon {
            font-size: 1.2rem;
            width: 28px;
            text-align: center;
            flex-shrink: 0;
            transition: color 0.15s;
        }

        .drawer-item-title {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            line-height: 1.2;
        }
        .drawer-item-desc {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 2px;
            font-family: 'DM Mono', monospace;
        }

        .drawer-item-badge {
            margin-left: auto;
            font-family: 'DM Mono', monospace;
            font-size: 0.6rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 3px 8px;
            border: 1px solid var(--accent);
            color: var(--accent);
            flex-shrink: 0;
        }
        .drawer-item-badge.admin-badge {
            border-color: var(--danger);
            color: var(--danger);
        }

        /* Admin-only section header */
        .admin-section-label {
            font-family: 'DM Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--danger);
            padding: 20px 28px 8px;
            opacity: 0.8;
        }

        .drawer-divider {
            height: 1px;
            background: var(--border);
            margin: 12px 28px;
        }

        .drawer-footer {
            margin-top: auto;
            padding: 16px 28px 28px;
            border-top: 1px solid var(--border);
        }

        .btn-signout {
            width: 100%;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 12px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        .btn-signout:hover { border-color: var(--danger); color: var(--danger); }

        @media (max-width: 480px) {
            .drawer { width: 100%; right: -100%; }
        }
    </style>
</head>
<body>

<div class="loading-overlay" id="loading">
    <div class="loader-text">GENERATING YOUR PLAN...</div>
    <div class="loader-bar"></div>
</div>

<!-- Drawer overlay -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- ======== SLIDE-IN DRAWER ======== -->
<div class="drawer" id="drawer">

    <!-- Header -->
    <div class="drawer-header">
        <div class="drawer-avatar <?= $is_admin ? 'admin-avatar' : '' ?>">
            <?= strtoupper(substr($username, 0, 1)) ?>
        </div>
        <div>
            <div class="drawer-name">
                <span class="<?= $is_admin ? 'admin-name' : '' ?>">
                    <?= htmlspecialchars(strtoupper($username)) ?>
                </span>
            </div>
            <div class="drawer-sub">
                <?php if ($is_admin): ?>
                    <div class="admin-role-badge">⚙ Administrator</div>
                <?php else: ?>
                    // LIMITLESS MEMBER
                <?php endif; ?>
            </div>
        </div>
        <button class="drawer-close" onclick="closeDrawer()">×</button>
    </div>

    <!-- ======== REGULAR USER SECTION ======== -->
    <div class="drawer-section-label">// MENU</div>
    <div class="drawer-menu">
        <a href="app.php" class="drawer-item">
            <div class="drawer-item-icon">⚡</div>
            <div>
                <div class="drawer-item-title">Generate Plan</div>
                <div class="drawer-item-desc">Build your workout & nutrition plan</div>
            </div>
        </a>
        <a href="history.php" class="drawer-item">
            <div class="drawer-item-icon">📋</div>
            <div>
                <div class="drawer-item-title">Plan History</div>
                <div class="drawer-item-desc">View, delete & restore past plans</div>
            </div>
        </a>
        <a href="profile_edit.php" class="drawer-item">
            <div class="drawer-item-icon">✏️</div>
            <div>
                <div class="drawer-item-title">Edit Profile</div>
                <div class="drawer-item-desc">Update your body stats & goals</div>
            </div>
        </a>
    </div>

    <?php if ($is_admin): ?>
    <!-- ======== ADMIN-ONLY SECTION ======== -->
    <div class="drawer-divider"></div>
    <div class="admin-section-label">⚙ ADMIN FEATURES</div>
    <div class="drawer-menu">

        <a href="reports.php" class="drawer-item admin-item">
            <div class="drawer-item-icon">📊</div>
            <div>
                <div class="drawer-item-title">System Reports</div>
                <div class="drawer-item-desc">Stats, goals & user analytics</div>
            </div>
            <div class="drawer-item-badge admin-badge">M3</div>
        </a>

        <a href="logs.php" class="drawer-item admin-item">
            <div class="drawer-item-icon">🕓</div>
            <div>
                <div class="drawer-item-title">Activity Logs</div>
                <div class="drawer-item-desc">Login, logout & plan activity</div>
            </div>
            <div class="drawer-item-badge admin-badge">M2</div>
        </a>

    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="drawer-footer">
        <a href="php/logout.php" class="btn-signout">Sign Out</a>
    </div>

</div>

<!-- ======== NAVBAR ======== -->
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:<?= $is_admin ? 'var(--danger)' : 'var(--accent)' ?>">
            <strong><?= htmlspecialchars($username) ?></strong>
            <?php if ($is_admin): ?>
                <span style="font-size:0.65rem;opacity:0.7"> [ADMIN]</span>
            <?php endif; ?>
        </span>
        <button class="profile-btn" onclick="openDrawer()">
            <div class="profile-avatar <?= $is_admin ? 'admin-avatar' : '' ?>">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            Menu ▾
        </button>
    </div>
</nav>

<!-- Welcome bar -->
<div class="welcome-bar" style="margin-top:64px;">
    <span>Welcome back, <strong><?= htmlspecialchars($username) ?></strong>
    <?= $is_admin ? ' — <span style="color:var(--danger)">Admin Access</span>' : ' — ready to crush it?' ?>
    </span>
</div>

<?php include 'includes/form.php'; ?>
<?php include 'includes/results.php'; ?>

<script src="js/form.js"></script>
<script src="js/plan.js"></script>
<script>
    function openDrawer() {
        document.getElementById('drawer').classList.add('active');
        document.getElementById('drawerOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        document.getElementById('drawer').classList.remove('active');
        document.getElementById('drawerOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeDrawer();
    });

    // Show unauthorized toast if redirected from admin page
    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
    window.addEventListener('load', () => {
        const t = document.createElement('div');
        t.className = 'toast';
        t.style.borderLeftColor = 'var(--danger)';
        t.textContent = '⛔ Access denied — Admin only area.';
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    });
    <?php endif; ?>
</script>
</body>
</html>
