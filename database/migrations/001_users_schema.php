<?php

// Simple one-time migration script to create:
// - database: payroll_db
// - tables: roles, users
// All identifiers are lowercase as requested.

// Load environment variables using phpdotenv (same as bootstrap.php)
require __DIR__ . '/../../vendor/autoload.php';

try {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
	$dotenv->load();
} catch (Exception $e) {
	die('Could not load .env via phpdotenv: ' . $e->getMessage());
}

function env_val($key, $default = null)
{
	return $_ENV[$key] ?? $default;
}

// Detect environment and pick correct credentials (local vs prod)
$appEnv = env_val('APP_ENV', 'local');

if ($appEnv === 'local') {
	$dbHost = env_val('DB_HOST_LOCAL', 'localhost');
	$dbUser = env_val('DB_USER_LOCAL', 'root');
	$dbPass = env_val('DB_PASS_LOCAL', '');
	$dbName = env_val('DB_NAME_LOCAL', 'payroll_db');
} else {
	$dbHost = env_val('DB_HOST_PROD', 'localhost');
	$dbUser = env_val('DB_USER_PROD', 'root');
	$dbPass = env_val('DB_PASS_PROD', '');
	$dbName = env_val('DB_NAME_PROD', 'payroll_db');
}

// Connect without selecting a database first so we can create it if missing
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	$conn = new mysqli($dbHost, $dbUser, $dbPass);
	$conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
	die('Connection failed: ' . $e->getMessage());
}

// 1) Create database payroll_db (or the DB name from .env) if it does not exist
$safeDbName = $conn->real_escape_string($dbName);
$createDbSql = "CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

try {
	$conn->query($createDbSql);
} catch (mysqli_sql_exception $e) {
	die('Error creating database: ' . $e->getMessage());
}

// Select the database
$conn->select_db($dbName);

// 2) Create roles table (only hr and accounting)
$createRolesSql = <<<SQL
CREATE TABLE IF NOT EXISTS roles (
	role_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	role_name VARCHAR(50) NOT NULL UNIQUE,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

// 3) Create users table referencing roles
$createUsersSql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
	user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(255) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	role_id INT UNSIGNED NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles(role_id)
		ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
	$conn->query($createRolesSql);
	$conn->query($createUsersSql);
} catch (mysqli_sql_exception $e) {
	die('Error creating tables: ' . $e->getMessage());
}

// 4) Seed roles table with hr and accounting (lowercase)
$seedSql = "INSERT INTO roles (role_name) VALUES ('hr'), ('accounting') " .
	"ON DUPLICATE KEY UPDATE role_name = VALUES(role_name)";

try {
	$conn->query($seedSql);
} catch (mysqli_sql_exception $e) {
	// If something goes wrong while seeding, fail loudly so you can fix it
	die('Error seeding roles: ' . $e->getMessage());
}

echo 'Migration completed. Database ' . htmlspecialchars($dbName) . ' with tables users and roles is ready.';

