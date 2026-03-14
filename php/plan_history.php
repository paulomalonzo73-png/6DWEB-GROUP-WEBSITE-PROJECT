<?php
/* ============================================
   php/plan_history.php
   MEMBER 2 — Plan History with Soft Delete + Restore
   Handles: list, soft-delete, restore, view single plan
   All actions require session authentication.
   ============================================ */
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

$conn = getDBConnection();

/* ============================================
   ACTION: list — get all active (non-deleted) plans
   ============================================ */
if ($action === 'list') {
    $stmt = $conn->prepare(
        "SELECT id, calories, protein, carbs, fats, created_at
         FROM generated_plans
         WHERE user_id = ? AND is_deleted = 0
         ORDER BY created_at DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'plans' => $plans]);
    exit;
}

/* ============================================
   ACTION: view — load full plan_data JSON for one plan
   ============================================ */
if ($action === 'view') {
    $plan_id = (int)($_GET['id'] ?? 0);
    if (!$plan_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid plan ID.']);
        exit;
    }

    // user_id check prevents accessing another user's plan
    $stmt = $conn->prepare(
        "SELECT id, calories, protein, carbs, fats, plan_data, created_at
         FROM generated_plans
         WHERE id = ? AND user_id = ? AND is_deleted = 0"
    );
    $stmt->bind_param("ii", $plan_id, $user_id);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Plan not found.']);
        exit;
    }

    $plan['plan_data'] = json_decode($plan['plan_data'], true);
    echo json_encode(['success' => true, 'plan' => $plan]);
    exit;
}

/* ============================================
   ACTION: delete — soft delete a plan
   Sets is_deleted = 1, records deleted_at timestamp
   ============================================ */
if ($action === 'delete') {
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    if (!$plan_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid plan ID.']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE generated_plans
         SET is_deleted = 1, deleted_at = NOW()
         WHERE id = ? AND user_id = ? AND is_deleted = 0"
    );
    $stmt->bind_param("ii", $plan_id, $user_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'Plan moved to trash.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan not found or already deleted.']);
    }
    exit;
}

/* ============================================
   ACTION: restore — un-delete a plan
   Sets is_deleted = 0, clears deleted_at
   ============================================ */
if ($action === 'restore') {
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    if (!$plan_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid plan ID.']);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE generated_plans
         SET is_deleted = 0, deleted_at = NULL
         WHERE id = ? AND user_id = ? AND is_deleted = 1"
    );
    $stmt->bind_param("ii", $plan_id, $user_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'Plan restored successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan not found in trash.']);
    }
    exit;
}

/* ============================================
   ACTION: trash — list all soft-deleted plans
   ============================================ */
if ($action === 'trash') {
    $stmt = $conn->prepare(
        "SELECT id, calories, protein, carbs, fats, created_at, deleted_at
         FROM generated_plans
         WHERE user_id = ? AND is_deleted = 1
         ORDER BY deleted_at DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'plans' => $plans]);
    exit;
}

/* ============================================
   ACTION: purge — permanently delete one plan from trash
   ============================================ */
if ($action === 'purge') {
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    if (!$plan_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid plan ID.']);
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM generated_plans
         WHERE id = ? AND user_id = ? AND is_deleted = 1"
    );
    $stmt->bind_param("ii", $plan_id, $user_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $conn->close();

    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'Plan permanently deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan not found in trash.']);
    }
    exit;
}

$conn->close();
echo json_encode(['success' => false, 'message' => 'Unknown action.']);
?>
