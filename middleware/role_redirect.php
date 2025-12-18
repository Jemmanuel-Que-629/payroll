<?php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /login.php');
    exit;
}

$role = $_SESSION['role'];
$page = basename($_SERVER['PHP_SELF']);

// 2. Define allowed pages per role
$rolePermissions = [
    'hr' => [
        'dashboard.php',
        'view_payroll.php',
        'create_user.php',
        'archive_user.php'
    ],
    'accounting' => [
        'dashboard.php',
        'view_payroll.php'
    ]
];

// 3. Allow only HR or Accounting
if (!in_array($role, ['hr','accounting'])) {
    die("Access denied. Unknown role.");
}

// 4. Check if the current page is allowed for this role
if (!in_array($page, $rolePermissions[$role])) {
    die("Access denied. You do not have permission to access this page.");
}

// 5. Optional: redirect to dashboard if needed
if ($page === 'login.php') {
    if ($role === 'hr') {
        header('Location: /views/hr/dashboard.php');
    } else {
        header('Location: /views/accounting/dashboard.php');
    }
    exit;
}

// 6. Optional: Add 2FA check here if enabled
// if ($_SESSION['2fa_enabled'] && !on_2fa_page()) { redirect_to_2fa(); }

?>
