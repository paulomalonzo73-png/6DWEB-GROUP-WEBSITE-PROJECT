<?php
/* ============================================
   php/log_activity.php
   MEMBER 1 — Audit Trail / Activity Log
   Reusable function called from auth.php,
   generate_plan.php, and any other action.
   ============================================ */

/**
 * Log a user action into activity_logs table.
 *
 * @param mysqli  $conn        Active DB connection
 * @param int     $user_id     ID of the acting user
 * @param string  $action      Short action code (e.g. 'LOGIN', 'PLAN_GENERATED')
 * @param string  $description Optional detail string
 */
function logActivity($conn, $user_id, $action, $description = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Truncate description to prevent oversized inserts
    $description = substr($description, 0, 500);

    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, action, description, ip_address)
         VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $description, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
?>
