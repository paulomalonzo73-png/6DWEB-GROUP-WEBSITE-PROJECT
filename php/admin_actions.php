<?php
/* ============================================
   php/admin_actions.php
   MEMBER 4 — Role-Based Access Control
   Handles all admin CRUD actions:
   - list_users
   - deactivate / reactivate user
   - delete user (hard)
   - promote / demote admin
   ============================================ */
require_once 'config.php';
require_once 'admin_check.php';   // blocks non-admins
header('Content-Type: application/json');

$action  = $_POST['action'] ?? $_GET['action'] ?? '';
$conn    = getDBConnection();
$admin_id = $_SESSION['user_id'];

/* ============================================
   ACTION: list_users — all non-admin users
   with plan count, status, and join date
   ============================================ */
if ($action === 'list_users') {
    $stmt = $conn->prepare(
        "SELECT
            u.id,
            u.username,
            u.email,
            u.is_active,
            u.is_admin,
            u.created_at,
            COUNT(gp.id) AS plan_count
         FROM users u
         LEFT JOIN generated_plans gp
             ON gp.user_id = u.id AND gp.is_deleted = 0
         WHERE u.id != ?
         GROUP BY u.id
         ORDER BY u.created_at DESC"
    );
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

/* ============================================
   ACTION: deactivate — set is_active = 0
   Cannot deactivate an admin account.
   ============================================ */
if ($action === 'deactivate') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    if (!$target_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    // Safety: cannot deactivate another admin
    $stmt = $conn->prepare(
        "UPDATE users SET is_active = 0
         WHERE id = ? AND is_admin = 0 AND id != ?"
    );
    $stmt->bind_param("ii", $target_id, $admin_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'User deactivated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot deactivate this user.']);
    }
    exit;
}

/* ============================================
   ACTION: reactivate — set is_active = 1
   ============================================ */
if ($action === 'reactivate') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    if (!$target_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE users SET is_active = 1
         WHERE id = ? AND id != ?"
    );
    $stmt->bind_param("ii", $target_id, $admin_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'User reactivated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not reactivate user.']);
    }
    exit;
}

/* ============================================
   ACTION: delete_user — hard delete
   Cascades to user_profiles and generated_plans
   (via ON DELETE CASCADE in DB schema).
   Cannot delete self or another admin.
   ============================================ */
if ($action === 'delete_user') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    if (!$target_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM users
         WHERE id = ? AND is_admin = 0 AND id != ?"
    );
    $stmt->bind_param("ii", $target_id, $admin_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'User permanently deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot delete this user.']);
    }
    exit;
}

/* ============================================
   ACTION: promote — grant admin role
   ============================================ */
if ($action === 'promote') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    if (!$target_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE users SET is_admin = 1 WHERE id = ?"
    );
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'User promoted to admin.']);
    exit;
}

/* ============================================
   ACTION: demote — remove admin role
   Cannot demote self.
   ============================================ */
if ($action === 'demote') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    if (!$target_id || $target_id === $admin_id) {
        echo json_encode(['success' => false, 'message' => 'Cannot demote yourself.']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE users SET is_admin = 0 WHERE id = ? AND id != ?"
    );
    $stmt->bind_param("ii", $target_id, $admin_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Admin role removed.']);
    exit;
}

/* ============================================
   ACTION: summary — platform-wide counts
   for the admin dashboard header
   ============================================ */
if ($action === 'summary') {
    $result = $conn->query(
        "SELECT
            (SELECT COUNT(*) FROM users WHERE is_admin = 0)                     AS total_users,
            (SELECT COUNT(*) FROM users WHERE is_active = 0 AND is_admin = 0)   AS inactive_users,
            (SELECT COUNT(*) FROM generated_plans WHERE is_deleted = 0)         AS total_plans,
            (SELECT COUNT(*) FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               AND is_admin = 0)                                                AS new_users_30d"
    );
    $summary = $result->fetch_assoc();
    $conn->close();

    echo json_encode(['success' => true, 'summary' => $summary]);
    exit;
}

$conn->close();
echo json_encode(['success' => false, 'message' => 'Unknown action.']);
?>
