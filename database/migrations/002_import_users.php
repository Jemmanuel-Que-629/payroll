<?php

// 002_import_users.php
// Seed initial hr and accounting users into the users table.

require __DIR__ . '/../../vendor/autoload.php';

// Load .env using phpdotenv (same pattern as bootstrap.php)
try {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
	$dotenv->load();
} catch (Exception $e) {
	die('Could not load .env via phpdotenv: ' . $e->getMessage());
}

// Connect to the database using existing db_connection.php
require __DIR__ . '/../db_connection.php'; // provides $conn (mysqli)

if (!isset($conn) || !($conn instanceof mysqli)) {
	die('Database connection not available.');
}

// Helper: get role_id by role_name
function get_role_id(mysqli $conn, string $role_name): int
{
	$sql = 'SELECT role_id FROM roles WHERE role_name = ? LIMIT 1';
	$stmt = $conn->prepare($sql);
	$stmt->bind_param('s', $role_name);
	$stmt->execute();
	$stmt->bind_result($role_id);
	if ($stmt->fetch()) {
		$stmt->close();
		return (int)$role_id;
	}
	$stmt->close();
	die('Role not found: ' . htmlspecialchars($role_name));
}

// Fetch role IDs for hr and accounting (lowercase)
$hr_role_id = get_role_id($conn, 'hr');
$accounting_role_id = get_role_id($conn, 'accounting');

// Shared password for initial accounts
$plainPassword = 'Christina_828';
$passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

// Prepare upsert statement for users
$sql = 'INSERT INTO users (email, password_hash, role_id) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role_id = VALUES(role_id)';

$stmt = $conn->prepare($sql);
if (!$stmt) {
	die('Failed to prepare statement: ' . $conn->error);
}

// Insert / update hr user
$email = 'hr@gmail.com';
$role_id = $hr_role_id;
$stmt->bind_param('ssi', $email, $passwordHash, $role_id);
$stmt->execute();

// Insert / update accounting user
$email = 'accounting@gmail.com';
$role_id = $accounting_role_id;
$stmt->bind_param('ssi', $email, $passwordHash, $role_id);
$stmt->execute();

$stmt->close();

echo 'Imported users: hr@gmail.com and accounting@gmail.com (password hashed).';

