<?php
/* ============================================
   activity_log.php
   MEMBER 1 — Audit Trail / Activity Log
   Shows the logged-in user's own action history.
   Protected: must be logged in.
   ============================================ */
require_once 'php/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

// --- Pagination ---
$per_page    = 15;
$page        = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

$conn = getDBConnection();

// Total count for pagination
$count_stmt = $conn->prepare(
    "SELECT COUNT(*) as total FROM activity_logs WHERE user_id = ?"
);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_rows  = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));
$count_stmt->close();

// Fetch logs with pagination
$stmt = $conn->prepare(
    "SELECT action, description, ip_address, created_at
     FROM activity_logs
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT ? OFFSET ?"
);
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- Action badge colours ---
$badge_colors = [
    'LOGIN'           => '#00ff88',
    'LOGOUT'          => '#ff4d00',
    'REGISTER'        => '#00aaff',
    'PLAN_GENERATED'  => '#e8ff00',
    'PROFILE_UPDATED' => '#ff00aa',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Activity Log</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/activity_log.css">
</head>
<body>

<!-- ======== NAVBAR ======== -->
<nav>
    <a class="logo" href="app.php"><span>LIMIT</span>LESS</a>
    <div class="nav-actions">
        <span style="font-family:'DM Mono',monospace;font-size:0.8rem;color:var(--accent)">
            <strong><?php echo htmlspecialchars($username); ?></strong>
        </span>
        <a href="app.php" class="btn btn-ghost btn-sm">← Back to App</a>
        <a href="php/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
    </div>
</nav>

<!-- ======== PAGE CONTENT ======== -->
<main class="log-page">

    <div class="log-header">
        <div class="section-eyebrow">// MEMBER 1 FEATURE — AUDIT TRAIL</div>
        <h1 class="log-title">ACTIVITY <span>LOG</span></h1>
        <p class="log-sub">
            A complete server-side record of every action performed on your account.
            Stored in the <code>activity_logs</code> database table.
        </p>
        <div class="log-meta">
            <span class="meta-pill">Total Events: <strong><?php echo $total_rows; ?></strong></span>
            <span class="meta-pill">Page <strong><?php echo $page; ?></strong> of <strong><?php echo $total_pages; ?></strong></span>
        </div>
    </div>

    <?php if (empty($logs)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p>No activity recorded yet. Actions will appear here as you use the app.</p>
        </div>
    <?php else: ?>

    <!-- ======== LOG TABLE ======== -->
    <div class="log-table-wrap">
        <table class="log-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $i => $log):
                    $row_num = $offset + $i + 1;
                    $color   = $badge_colors[$log['action']] ?? '#666';
                    $ts      = date('M d, Y  H:i:s', strtotime($log['created_at']));
                ?>
                <tr>
                    <td class="row-num"><?php echo str_pad($row_num, 2, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <span class="action-badge" style="border-color:<?php echo $color; ?>;color:<?php echo $color; ?>">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </span>
                    </td>
                    <td class="desc-cell"><?php echo htmlspecialchars($log['description'] ?: '—'); ?></td>
                    <td class="ip-cell"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                    <td class="ts-cell"><?php echo $ts; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ======== PAGINATION ======== -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">← Prev</a>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <a href="?page=<?php echo $p; ?>"
               class="page-btn <?php echo $p === $page ? 'active' : ''; ?>">
                <?php echo $p; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</main>

</body>
</html>
