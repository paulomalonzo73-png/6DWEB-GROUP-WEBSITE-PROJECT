<?php
/* ============================================
   reports.php
   MEMBER 3 — Report Generation (Summary Stats)
   Runs GROUP BY queries to show system-wide
   and user-level statistics from the database.
   ============================================ */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if (empty($_SESSION['is_admin'])) {
    header('Location: app.php?error=unauthorized');
    exit;
}

require_once 'php/config.php';

$username = $_SESSION['username'];
$conn = getDBConnection();

// --- 1. Goal distribution (all users) ---
$goal_result = $conn->query(
    "SELECT goal, COUNT(*) AS total
     FROM user_profiles
     GROUP BY goal
     ORDER BY total DESC"
);
$goal_data = $goal_result->fetch_all(MYSQLI_ASSOC);

// --- 2. Workout type distribution ---
$workout_result = $conn->query(
    "SELECT workout_type, COUNT(*) AS total
     FROM user_profiles
     GROUP BY workout_type
     ORDER BY total DESC"
);
$workout_data = $workout_result->fetch_all(MYSQLI_ASSOC);

// --- 3. Experience level distribution ---
$exp_result = $conn->query(
    "SELECT experience, COUNT(*) AS total
     FROM user_profiles
     GROUP BY experience
     ORDER BY total DESC"
);
$exp_data = $exp_result->fetch_all(MYSQLI_ASSOC);

// --- 4. Average nutrition across all plans ---
$avg_result = $conn->query(
    "SELECT
        ROUND(AVG(calories)) AS avg_calories,
        ROUND(AVG(protein))  AS avg_protein,
        ROUND(AVG(carbs))    AS avg_carbs,
        ROUND(AVG(fats))     AS avg_fats,
        COUNT(*)             AS total_plans
     FROM generated_plans"
);
$avg_data = $avg_result->fetch_assoc();

// --- 5. Most active users (by plan count) ---
$top_result = $conn->query(
    "SELECT u.username, COUNT(gp.id) AS plan_count
     FROM users u
     LEFT JOIN generated_plans gp ON gp.user_id = u.id AND gp.is_deleted = 0
     GROUP BY u.id, u.username
     ORDER BY plan_count DESC
     LIMIT 5"
);
$top_users = $top_result->fetch_all(MYSQLI_ASSOC);

// --- 6. Total registered users ---
$user_count_result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_users = $user_count_result->fetch_assoc()['total'];

$conn->close();

// --- Helper: label maps ---
$goal_labels = [
    'bulking'         => '💪 Bulking',
    'cutting'         => '🔪 Cutting',
    'endurance'       => '🏃 Endurance',
    'general_fitness' => '⚖️ General Fitness',
];
$exp_labels = [
    'beginner'     => '🌱 Beginner',
    'intermediate' => '⚡ Intermediate',
    'expert'       => '🔥 Expert',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIMITLESS — Reports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        body { padding-top: 80px; }

        .reports-wrapper {
            max-width: 1080px;
            margin: 0 auto;
            padding: 48px 32px;
        }

        .page-eyebrow {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
            color: var(--accent);
            letter-spacing: 0.3em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .page-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3rem;
            margin-bottom: 40px;
        }
        .page-title span { color: var(--accent); }

        /* Summary cards row */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2px;
            margin-bottom: 48px;
        }

        .summary-card {
            background: var(--surface);
            padding: 28px 20px;
            text-align: center;
            position: relative;
        }
        .summary-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
        }
        .summary-card:nth-child(1)::after { background: var(--accent); }
        .summary-card:nth-child(2)::after { background: #ff4d00; }
        .summary-card:nth-child(3)::after { background: #00aaff; }
        .summary-card:nth-child(4)::after { background: #ff00aa; }

        .summary-val {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.8rem;
            color: var(--accent);
            line-height: 1;
        }
        .summary-card:nth-child(2) .summary-val { color: #ff4d00; }
        .summary-card:nth-child(3) .summary-val { color: #00aaff; }
        .summary-card:nth-child(4) .summary-val { color: #ff00aa; }

        .summary-lbl {
            font-family: 'DM Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            margin-top: 6px;
        }

        /* Section heading */
        .section-heading {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem;
            letter-spacing: 0.1em;
            margin-bottom: 16px;
            margin-top: 40px;
        }
        .section-heading span { color: var(--accent); }

        /* Distribution bars */
        .dist-item {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 14px;
        }

        .dist-label {
            width: 160px;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .dist-bar-wrap {
            flex: 1;
            background: var(--surface);
            height: 28px;
            position: relative;
            overflow: hidden;
        }

        .dist-bar {
            height: 100%;
            background: var(--accent);
            transition: width 0.6s ease;
        }

        .dist-count {
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            color: var(--muted);
            width: 60px;
            text-align: right;
            flex-shrink: 0;
        }

        /* Avg nutrition row */
        .avg-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2px;
            margin-bottom: 40px;
        }

        /* Top users table */
        .top-table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-table th {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            text-align: left;
            padding: 10px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }
        .top-table td {
            padding: 12px 16px;
            font-size: 0.88rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .top-table tr:hover td { background: var(--surface2); }

        @media (max-width: 768px) {
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .avg-grid     { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

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

<div class="reports-wrapper">

    <div class="page-eyebrow">// MEMBER 3 FEATURE</div>
    <h1 class="page-title">SYSTEM <span>REPORTS</span></h1>

    <!-- Summary cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-val"><?= $total_users ?></div>
            <div class="summary-lbl">Total Users</div>
        </div>
        <div class="summary-card">
            <div class="summary-val"><?= $avg_data['total_plans'] ?? 0 ?></div>
            <div class="summary-lbl">Plans Generated</div>
        </div>
        <div class="summary-card">
            <div class="summary-val"><?= $avg_data['avg_calories'] ?? '—' ?></div>
            <div class="summary-lbl">Avg Calories (kcal)</div>
        </div>
        <div class="summary-card">
            <div class="summary-val"><?= $avg_data['avg_protein'] ?? '—' ?>g</div>
            <div class="summary-lbl">Avg Protein</div>
        </div>
    </div>

    <!-- Goal distribution -->
    <h2 class="section-heading">GOAL <span>DISTRIBUTION</span></h2>
    <?php
    $max_goal = max(array_column($goal_data, 'total') ?: [1]);
    foreach ($goal_data as $row):
        $pct = round(($row['total'] / $max_goal) * 100);
        $label = $goal_labels[$row['goal']] ?? $row['goal'];
    ?>
    <div class="dist-item">
        <div class="dist-label"><?= htmlspecialchars($label) ?></div>
        <div class="dist-bar-wrap">
            <div class="dist-bar" style="width:<?= $pct ?>%"></div>
        </div>
        <div class="dist-count"><?= $row['total'] ?> users</div>
    </div>
    <?php endforeach; ?>

    <!-- Workout type distribution -->
    <h2 class="section-heading">WORKOUT <span>TYPE</span></h2>
    <?php
    $max_wo = max(array_column($workout_data, 'total') ?: [1]);
    foreach ($workout_data as $row):
        $pct = round(($row['total'] / $max_wo) * 100);
        $icon = $row['workout_type'] === 'gym' ? '🏋️ Gym' : '🏠 Home';
    ?>
    <div class="dist-item">
        <div class="dist-label"><?= $icon ?></div>
        <div class="dist-bar-wrap">
            <div class="dist-bar" style="width:<?= $pct ?>%;background:#00aaff"></div>
        </div>
        <div class="dist-count"><?= $row['total'] ?> users</div>
    </div>
    <?php endforeach; ?>

    <!-- Experience distribution -->
    <h2 class="section-heading">EXPERIENCE <span>LEVEL</span></h2>
    <?php
    $max_exp = max(array_column($exp_data, 'total') ?: [1]);
    foreach ($exp_data as $row):
        $pct = round(($row['total'] / $max_exp) * 100);
        $label = $exp_labels[$row['experience']] ?? $row['experience'];
    ?>
    <div class="dist-item">
        <div class="dist-label"><?= htmlspecialchars($label) ?></div>
        <div class="dist-bar-wrap">
            <div class="dist-bar" style="width:<?= $pct ?>%;background:#ff4d00"></div>
        </div>
        <div class="dist-count"><?= $row['total'] ?> users</div>
    </div>
    <?php endforeach; ?>

    <!-- Average nutrition -->
    <h2 class="section-heading">AVERAGE <span>NUTRITION</span> TARGETS</h2>
    <div class="avg-grid">
        <?php
        $avg_items = [
            ['val' => $avg_data['avg_calories'] ?? '—', 'unit' => 'kcal', 'lbl' => 'Avg Calories', 'color' => 'var(--accent)'],
            ['val' => ($avg_data['avg_protein']  ?? '—') . 'g', 'unit' => '',     'lbl' => 'Avg Protein',  'color' => '#ff4d00'],
            ['val' => ($avg_data['avg_carbs']    ?? '—') . 'g', 'unit' => '',     'lbl' => 'Avg Carbs',    'color' => '#00aaff'],
            ['val' => ($avg_data['avg_fats']     ?? '—') . 'g', 'unit' => '',     'lbl' => 'Avg Fats',     'color' => '#ff00aa'],
        ];
        foreach ($avg_items as $item):
        ?>
        <div class="summary-card">
            <div class="summary-val" style="color:<?= $item['color'] ?>"><?= $item['val'] ?></div>
            <div class="summary-lbl"><?= $item['lbl'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Top users -->
    <h2 class="section-heading">TOP <span>ACTIVE USERS</span></h2>
    <table class="top-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Username</th>
                <th>Plans Generated</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_users as $i => $u): ?>
            <tr>
                <td style="font-family:'DM Mono',monospace;color:var(--accent)">#<?= $i + 1 ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td style="font-family:'DM Mono',monospace"><?= $u['plan_count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
</body>
</html>
