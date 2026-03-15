<?php
/* ============================================
   logs.php
   UNIQUE BACKEND 2 — Activity Logs
   Admin only. Shows all user activity with names.
   ============================================ */
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
if (empty($_SESSION['is_admin']))  { header('Location: app.php?error=unauthorized'); exit; }

require_once 'php/config.php';

$username = $_SESSION['username'];

// Auto-create table if missing
$_conn = getDBConnection();
$_conn->query("ALTER TABLE activity_logs ADD COLUMN IF NOT EXISTS username VARCHAR(50) NOT NULL DEFAULT ''");
$_conn->close();

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$allowed_filters = ['login', 'register', 'plan_generated', 'logout'];

$conn = getDBConnection();

if ($filter && in_array($filter, $allowed_filters)) {
    $stmt = $conn->prepare(
        "SELECT al.username, al.action, al.details, al.ip_address, al.created_at
         FROM activity_logs al
         WHERE al.action = ?
         ORDER BY al.created_at DESC"
    );
    $stmt->bind_param("s", $filter);
} else {
    $stmt = $conn->prepare(
        "SELECT al.username, al.action, al.details, al.ip_address, al.created_at
         FROM activity_logs al
         ORDER BY al.created_at DESC"
    );
}

$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();

$badge_colors = [
    'login'          => '#00ff88',
    'register'       => '#00aaff',
    'plan_generated' => '#e8ff00',
    'logout'         => '#ff4d00',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Activity Logs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        body { padding-top: 80px; }
        .logs-wrapper { max-width: 1000px; margin: 0 auto; padding: 48px 32px; }
        .page-eyebrow { font-family:'DM Mono',monospace; font-size:0.7rem; color:var(--accent); letter-spacing:0.3em; text-transform:uppercase; margin-bottom:8px; }
        .page-title { font-family:'Bebas Neue',sans-serif; font-size:3rem; margin-bottom:32px; }
        .page-title span { color:var(--accent); }

        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px; }
        .filter-btn { background:var(--surface); border:1px solid var(--border); color:var(--muted); padding:8px 18px; font-family:'DM Mono',monospace; font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase; cursor:pointer; text-decoration:none; transition:all 0.2s; }
        .filter-btn:hover, .filter-btn.active { border-color:var(--accent); color:var(--accent); }

        .total-badge { font-family:'DM Mono',monospace; font-size:0.75rem; color:var(--muted); margin-bottom:16px; }
        .total-badge span { color:var(--accent); }

        .log-table { width:100%; border-collapse:collapse; }
        .log-table th { font-family:'DM Mono',monospace; font-size:0.7rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--muted); text-align:left; padding:12px 16px; border-bottom:1px solid var(--border); background:var(--surface); }
        .log-table td { padding:14px 16px; font-size:0.88rem; border-bottom:1px solid rgba(255,255,255,0.04); }
        .log-table tr:hover td { background:var(--surface2); }

        .action-badge { display:inline-block; font-family:'DM Mono',monospace; font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase; padding:3px 10px; border:1px solid; }
        .user-pill { font-family:'DM Mono',monospace; font-size:0.78rem; color:var(--accent); font-weight:600; }
        .empty-state { text-align:center; padding:60px 20px; color:var(--muted); font-family:'DM Mono',monospace; }
    </style>
</head>
<body>
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--danger)">
            <strong><?= htmlspecialchars($username) ?></strong> <span style="font-size:0.65rem">[ADMIN]</span>
        </span>
        <a href="app.php" class="btn btn-ghost btn-sm">← Back</a>
        <a href="php/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
    </div>
</nav>

<div class="logs-wrapper">
    <div class="page-eyebrow">// UNIQUE BACKEND 2</div>
    <h1 class="page-title">ACTIVITY <span>LOGS</span></h1>

    <div class="filter-bar">
        <a href="logs.php"                       class="filter-btn <?= !$filter ? 'active':'' ?>">All</a>
        <a href="logs.php?filter=login"          class="filter-btn <?= $filter==='login'          ? 'active':'' ?>">Login</a>
        <a href="logs.php?filter=register"       class="filter-btn <?= $filter==='register'       ? 'active':'' ?>">Register</a>
        <a href="logs.php?filter=plan_generated" class="filter-btn <?= $filter==='plan_generated' ? 'active':'' ?>">Plan Generated</a>
        <a href="logs.php?filter=logout"         class="filter-btn <?= $filter==='logout'         ? 'active':'' ?>">Logout</a>
    </div>

    <div class="total-badge">Total entries: <span><?= count($logs) ?></span></div>

    <?php if (empty($logs)): ?>
        <div class="empty-state">NO ACTIVITY LOGS FOUND.</div>
    <?php else: ?>
    <table class="log-table">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $i => $log):
                $color = $badge_colors[$log['action']] ?? '#888';
            ?>
            <tr>
                <td style="font-family:'DM Mono',monospace;font-size:0.75rem;color:var(--muted)"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td>
                <td><span class="user-pill"><?= htmlspecialchars($log['username'] ?: '—') ?></span></td>
                <td><span class="action-badge" style="border-color:<?= $color ?>;color:<?= $color ?>"><?= htmlspecialchars($log['action']) ?></span></td>
                <td style="color:var(--muted);font-size:0.82rem"><?= htmlspecialchars($log['details'] ?: '—') ?></td>
                <td style="font-family:'DM Mono',monospace;font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($log['ip_address']) ?></td>
                <td style="font-family:'DM Mono',monospace;font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($log['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
