<?php

// 003_applicants_schema.php
// Create applicants table in the configured payroll_db database.

// Reuse application bootstrap (env, session, db connection)
require __DIR__ . '/../../bootstrap.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
  die('Database connection is not available.');
}

// Define applicants table (single CREATE TABLE with PK and AUTO_INCREMENT)
$createApplicantsSql = <<<SQL
CREATE TABLE IF NOT EXISTS applicants (
  Applicant_ID INT(11) NOT NULL AUTO_INCREMENT,
  First_Name VARCHAR(50) NOT NULL,
  Middle_Name VARCHAR(50) DEFAULT NULL,
  Last_Name VARCHAR(50) NOT NULL,
  Name_Extension VARCHAR(10) DEFAULT NULL,
  Email VARCHAR(100) NOT NULL,
  Phone_Number VARCHAR(20) NOT NULL,
  Position VARCHAR(50) NOT NULL,
  Preferred_Location VARCHAR(50) DEFAULT NULL,
  Resume_Path VARCHAR(255) NOT NULL,
  Additional_Info TEXT DEFAULT NULL,
  Application_Date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Status ENUM('New','Contacted','Interview Scheduled','Hired','Not Qualified') NOT NULL DEFAULT 'New',
  Reviewed TINYINT(1) NOT NULL DEFAULT 0,
  Last_Modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (Applicant_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $conn->query($createApplicantsSql);
} catch (mysqli_sql_exception $e) {
  die('Error creating applicants table: ' . $e->getMessage());
}

echo 'Migration completed. Table applicants is ready.';

