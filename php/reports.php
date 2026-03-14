<?php
/* ============================================
   php/reports.php
   MEMBER 3 — Report Generation (Aggregates)
   Returns JSON summary stats for the logged-in user
   plus anonymised global totals.
   All queries are SELECT only — read-only endpoint.
   ============================================ */
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn    = getDBConnection();

/* ============================================
   1. Personal totals & calorie averages
   ============================================ */
$stmt = $conn->prepare(
    "SELECT
        COUNT(*)          AS total_plans,
        ROUND(AVG(calories)) AS avg_calories,
        MAX(calories)     AS max_calories,
        MIN(calories)     AS min_calories,
        ROUND(AVG(protein))  AS avg_protein,
        ROUND(AVG(carbs))    AS avg_carbs,
        ROUND(AVG(fats))     AS avg_fats
     FROM generated_plans
     WHERE user_id = ? AND is_deleted = 0"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$personal = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ============================================
   2. Most frequent goal chosen by this user
   ============================================ */
$stmt = $conn->prepare(
    "SELECT goal, COUNT(*) AS cnt
     FROM user_profiles
     WHERE user_id = ?
     GROUP BY goal
     ORDER BY cnt DESC
     LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$top_goal = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ============================================
   3. Plans per week — last 8 weeks
   ============================================ */
$stmt = $conn->prepare(
    "SELECT
        DATE_FORMAT(created_at, '%Y-%u') AS yr_week,
        DATE_FORMAT(MIN(created_at), '%b %d') AS week_label,
        COUNT(*) AS plan_count
     FROM generated_plans
     WHERE user_id = ?
       AND is_deleted = 0
       AND created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
     GROUP BY yr_week
     ORDER BY yr_week ASC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$weekly = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ============================================
   4. Goal distribution across all the user's plans
   ============================================ */
$stmt = $conn->prepare(
    "SELECT goal, COUNT(*) AS cnt
     FROM user_profiles
     WHERE user_id = ?
     GROUP BY goal
     ORDER BY cnt DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goal_dist = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ============================================
   5. Workout type preference (gym vs home)
   ============================================ */
$stmt = $conn->prepare(
    "SELECT workout_type, COUNT(*) AS cnt
     FROM user_profiles
     WHERE user_id = ?
     GROUP BY workout_type
     ORDER BY cnt DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$type_dist = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ============================================
   6. Plans generated this month vs last month
   ============================================ */
$stmt = $conn->prepare(
    "SELECT
        SUM(CASE WHEN MONTH(created_at) = MONTH(NOW())    AND YEAR(created_at) = YEAR(NOW())    THEN 1 ELSE 0 END) AS this_month,
        SUM(CASE WHEN MONTH(created_at) = MONTH(NOW())-1  AND YEAR(created_at) = YEAR(NOW())    THEN 1 ELSE 0 END) AS last_month
     FROM generated_plans
     WHERE user_id = ? AND is_deleted = 0"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$monthly = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ============================================
   7. Global (anonymised) platform stats
   ============================================ */
$global_stmt = $conn->query(
    "SELECT
        COUNT(DISTINCT user_id)   AS total_users,
        COUNT(*)                  AS total_plans,
        ROUND(AVG(calories))      AS global_avg_cal
     FROM generated_plans
     WHERE is_deleted = 0"
);
$global = $global_stmt->fetch_assoc();

$conn->close();

/* ============================================
   Return all data as JSON
   ============================================ */
echo json_encode([
    'success'    => true,
    'personal'   => $personal,
    'top_goal'   => $top_goal,
    'weekly'     => $weekly,
    'goal_dist'  => $goal_dist,
    'type_dist'  => $type_dist,
    'monthly'    => $monthly,
    'global'     => $global,
]);
?>
