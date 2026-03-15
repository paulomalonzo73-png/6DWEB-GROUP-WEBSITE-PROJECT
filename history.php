<?php
/* ============================================
   history.php
   UNIQUE BACKEND 4 — Plan History
   All users can see their own plan history.
   Soft delete + restore.
   ============================================ */
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

require_once 'php/config.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'] ?? 0;

// Auto-add columns if missing
$_conn = getDBConnection();
$_conn->query("ALTER TABLE generated_plans ADD COLUMN IF NOT EXISTS is_deleted TINYINT(1) DEFAULT 0");
$_conn->query("ALTER TABLE generated_plans ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL");
$_conn->close();

// --- Handle soft delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['plan_id'])) {
    $plan_id = (int) $_POST['plan_id'];
    $conn = getDBConnection();

    if ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare(
            "UPDATE generated_plans SET is_deleted=1, deleted_at=NOW() WHERE id=? AND user_id=?"
        );
        $stmt->bind_param("ii", $plan_id, $user_id);
        $stmt->execute();
        $conn->close();
        header('Location: history.php');
        exit;
    }

    if ($_POST['action'] === 'restore') {
        $stmt = $conn->prepare(
            "UPDATE generated_plans SET is_deleted=0, deleted_at=NULL WHERE id=? AND user_id=?"
        );
        $stmt->bind_param("ii", $plan_id, $user_id);
        $stmt->execute();
        $conn->close();
        header('Location: history.php?view=trash');
        exit;
    }
    $conn->close();
}

$view = (isset($_GET['view']) && $_GET['view'] === 'trash') ? 'trash' : 'active';
$conn = getDBConnection();

if ($view === 'trash') {
    $stmt = $conn->prepare(
        "SELECT id, calories, protein, carbs, fats, plan_data, created_at, deleted_at
         FROM generated_plans WHERE user_id=? AND is_deleted=1 ORDER BY deleted_at DESC"
    );
} else {
    $stmt = $conn->prepare(
        "SELECT id, calories, protein, carbs, fats, plan_data, created_at
         FROM generated_plans WHERE user_id=? AND is_deleted=0 ORDER BY created_at DESC"
    );
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Plan History</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        body { padding-top: 80px; }
        .wrapper { max-width: 960px; margin: 0 auto; padding: 48px 32px; }
        .page-eyebrow { font-family:'DM Mono',monospace; font-size:0.7rem; color:var(--accent); letter-spacing:0.3em; text-transform:uppercase; margin-bottom:8px; }
        .page-title { font-family:'Bebas Neue',sans-serif; font-size:3rem; margin-bottom:32px; }
        .page-title span { color:var(--accent); }

        .view-tabs { display:flex; border-bottom:1px solid var(--border); margin-bottom:32px; }
        .view-tab { text-decoration:none; color:var(--muted); font-family:'DM Mono',monospace; font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase; padding:10px 24px 10px 0; margin-right:24px; position:relative; transition:color 0.2s; }
        .view-tab::after { content:''; position:absolute; bottom:-1px; left:0; right:0; height:2px; background:var(--accent); transform:scaleX(0); transition:transform 0.2s; }
        .view-tab.active { color:var(--text); }
        .view-tab.active::after { transform:scaleX(1); }

        .plan-card { background:var(--surface); border:1px solid var(--border); padding:24px 28px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; gap:20px; }
        .plan-meta { font-family:'DM Mono',monospace; font-size:0.75rem; color:var(--muted); margin-bottom:10px; }
        .plan-stats { display:flex; gap:24px; flex-wrap:wrap; }
        .plan-stat .val { font-family:'Bebas Neue',sans-serif; font-size:1.6rem; line-height:1; color:var(--accent); }
        .plan-stat:nth-child(2) .val { color:#ff4d00; }
        .plan-stat:nth-child(3) .val { color:#00aaff; }
        .plan-stat:nth-child(4) .val { color:#ff00aa; }
        .plan-stat .lbl { font-family:'DM Mono',monospace; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); margin-top:2px; }

        .goal-tag { display:inline-block; font-family:'DM Mono',monospace; font-size:0.65rem; letter-spacing:0.1em; text-transform:uppercase; padding:2px 8px; border:1px solid var(--accent); color:var(--accent); margin-bottom:8px; }

        .plan-actions { display:flex; gap:8px; flex-shrink:0; }
        .btn-del { background:transparent; border:1px solid var(--danger); color:var(--danger); padding:8px 16px; font-family:'DM Mono',monospace; font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase; cursor:pointer; transition:all 0.2s; }
        .btn-del:hover { background:var(--danger); color:#000; }
        .btn-restore { background:transparent; border:1px solid var(--success); color:var(--success); padding:8px 16px; font-family:'DM Mono',monospace; font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase; cursor:pointer; transition:all 0.2s; }
        .btn-restore:hover { background:var(--success); color:#000; }
        .trash-label { font-family:'DM Mono',monospace; font-size:0.7rem; color:var(--danger); margin-top:6px; }
        .empty-state { text-align:center; padding:60px 20px; color:var(--muted); font-family:'DM Mono',monospace; }
    </style>
</head>
<body>
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:<?= $is_admin ? 'var(--danger)' : 'var(--accent)' ?>">
            <strong><?= htmlspecialchars($username) ?></strong>
        </span>
        <a href="app.php" class="btn btn-ghost btn-sm">← Back</a>
        <a href="php/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
    </div>
</nav>

<div class="wrapper">
    <div class="page-eyebrow">// UNIQUE BACKEND 4</div>
    <h1 class="page-title">PLAN <span>HISTORY</span></h1>

    <div class="view-tabs">
        <a href="history.php"            class="view-tab <?= $view==='active'?'active':'' ?>">Active Plans</a>
        <a href="history.php?view=trash" class="view-tab <?= $view==='trash' ?'active':'' ?>">🗑 Trash</a>
    </div>

    <?php if (empty($plans)): ?>
        <div class="empty-state"><?= $view==='trash' ? 'TRASH IS EMPTY.' : 'NO PLANS YET. GO GENERATE ONE!' ?></div>
    <?php else: ?>
        <?php foreach ($plans as $plan):
            $pd = json_decode($plan['plan_data'], true);
            $goal = $pd['goal'] ?? '—';
            $exp  = $pd['experience'] ?? '—';
            $type = $pd['workout_type'] ?? '—';
        ?>
        <div class="plan-card">
            <div>
                <div class="plan-meta">Plan #<?= $plan['id'] ?> &nbsp;·&nbsp; <?= htmlspecialchars($plan['created_at']) ?></div>
                <div class="goal-tag"><?= htmlspecialchars($goal) ?> · <?= htmlspecialchars($exp) ?> · <?= htmlspecialchars($type) ?></div>
                <div class="plan-stats">
                    <div class="plan-stat"><div class="val"><?= $plan['calories'] ?></div><div class="lbl">kcal</div></div>
                    <div class="plan-stat"><div class="val"><?= $plan['protein'] ?>g</div><div class="lbl">Protein</div></div>
                    <div class="plan-stat"><div class="val"><?= $plan['carbs'] ?>g</div><div class="lbl">Carbs</div></div>
                    <div class="plan-stat"><div class="val"><?= $plan['fats'] ?>g</div><div class="lbl">Fats</div></div>
                </div>
                <?php if ($view==='trash' && !empty($plan['deleted_at'])): ?>
                    <div class="trash-label">Deleted: <?= htmlspecialchars($plan['deleted_at']) ?></div>
                <?php endif; ?>
            </div>
            <div class="plan-actions">
                <?php if ($view==='active'): ?>
                    <form method="POST">
                        <input type="hidden" name="action"  value="delete">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <button type="submit" class="btn-del" onclick="return confirm('Move to trash?')">Delete</button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action"  value="restore">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <button type="submit" class="btn-restore">Restore</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
