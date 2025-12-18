<?php
// login_processing.php

// Load application bootstrap (env, session, db connection)
require __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

// Basic input
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['toast_error'] = 'Please enter both email and password.';
    header('Location: ../login.php');
    exit();
}

// Normalize email
$email = strtolower($email);

// Ensure we have a DB connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    $_SESSION['toast_error'] = 'Database connection is not available.';
    header('Location: ../login.php');
    exit();
}

try {
    // Look up user and role
    $sql = 'SELECT u.user_id, u.password_hash, u.role_id, r.role_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.role_id
            WHERE u.email = ?
            LIMIT 1';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // No such user
        $_SESSION['toast_error'] = 'Invalid email or password.';
        $stmt->close();
        header('Location: ../login.php');
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Only allow hr and accounting roles
    $roleName = strtolower((string) $user['role_name']);
    if ($roleName !== 'hr' && $roleName !== 'accounting') {
        $_SESSION['toast_error'] = 'Only HR and accounting users can sign in to this portal.';
        header('Location: ../login.php');
        exit();
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['toast_error'] = 'Invalid email or password.';
        header('Location: ../login.php');
        exit();
    }

    // Successful login
    $_SESSION['user_id']  = (int) $user['user_id'];
    $_SESSION['role_id']  = (int) $user['role_id'];
    $_SESSION['role']     = $roleName;
    $_SESSION['toast_success'] = 'Login successful.';

    // Simple remember-me (optional): store email in a cookie for convenience only
    if (!empty($_POST['remember'])) {
        setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/');
    } else {
        if (isset($_COOKIE['remember_email'])) {
            setcookie('remember_email', '', time() - 3600, '/');
        }
    }

    // Redirect based on role (hr or accounting only)
    if ($roleName === 'hr') {
        header('Location: /views/hr/dashboard.php');
        exit();
    }

    if ($roleName === 'accounting') {
        header('Location: /views/accounting/dashboard.php');
        exit();
    }

    // Fallback
    header('Location: ../login.php');
    exit();

} catch (Throwable $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['toast_error'] = 'An unexpected error occurred while signing in.';
    header('Location: ../login.php');
    exit();
}
