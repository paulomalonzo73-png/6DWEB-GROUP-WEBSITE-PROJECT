<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'log_activity.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields required.']);
        exit;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT id, username, password, is_admin FROM users WHERE username = ? OR email = ?"
    );
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $conn->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (int) $user['is_admin'];
        logActivity($user['id'], $user['username'], 'login', 'User logged in successfully');
        echo json_encode(['success' => true, 'message' => 'Login successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }

} elseif ($action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields required.']);
        exit;
    }
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    $conn  = getDBConnection();
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
        $conn->close();
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt   = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sss", $username, $email, $hashed);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $_SESSION['user_id']  = $new_id;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = 0;
        $conn->close();
        logActivity($new_id, $username, 'register', 'New account created: ' . $username);
        echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
    } else {
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Registration failed. Try again.']);
    }

} elseif ($action === 'logout') {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], $_SESSION['username'], 'logout', 'User signed out');
    }
    session_destroy();
    echo json_encode(['success' => true]);

} elseif ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'logged_in' => true,
            'username'  => $_SESSION['username'],
            'is_admin'  => $_SESSION['is_admin'] ?? 0
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
}
?>
