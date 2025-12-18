<?php
// db_connection.php

// Helper function for environment variables
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Pick DB credentials based on APP_ENV
$appEnv = env('APP_ENV', 'local');

if ($appEnv === 'local') {
    $dbHost = env('DB_HOST_LOCAL', 'localhost');
    $dbUser = env('DB_USER_LOCAL', 'root');
    $dbPass = env('DB_PASS_LOCAL', '');
    $dbName = env('DB_NAME_LOCAL', 'payroll_db');
} else {
    $dbHost = env('DB_HOST_PROD', 'localhost');
    $dbUser = env('DB_USER_PROD', 'root');
    $dbPass = env('DB_PASS_PROD', '');
    $dbName = env('DB_NAME_PROD', 'payroll_db');
}

// Connect to database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    die("Database connection failed.");
}
?>