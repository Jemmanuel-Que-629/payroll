<?php

// 004_messages_schema.php
// Create contact_messages table to store contact form submissions.

// Reuse application bootstrap (env, session, db connection)
require __DIR__ . '/../../bootstrap.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
	die('Database connection is not available.');
}

// Define contact_messages table
$createMessagesSql = <<<SQL
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	$conn->query($createMessagesSql);
} catch (mysqli_sql_exception $e) {
	die('Error creating contact_messages table: ' . $e->getMessage());
}

echo 'Migration completed. Table contact_messages is ready.';
