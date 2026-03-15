<?php
/* ============================================
   php/log_activity.php
   UNIQUE BACKEND 2 — Activity Logs
   Inserts a log entry into activity_logs table.
   ============================================ */

require_once __DIR__ . '/config.php';

function logActivity($user_id, $username, $action, $details = '') {
    $conn = getDBConnection();

    // Auto-create table if missing
    $conn->query("CREATE TABLE IF NOT EXISTS activity_logs (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT          NULL,
        username   VARCHAR(50)  NOT NULL DEFAULT '',
        action     VARCHAR(100) NOT NULL,
        details    TEXT         NULL,
        ip_address VARCHAR(45)  NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, username, action, details, ip_address)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $user_id, $username, $action, $details, $ip);
    $stmt->execute();
    $conn->close();
}
?>
