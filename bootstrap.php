<?php
// bootstrap.php

require __DIR__ . '/../vendor/autoload.php'; // Composer autoload for phpdotenv

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../uploads/error/php-error.log');
error_reporting(E_ALL);

// Start session
session_start();

// Include DB connection
require __DIR__ . '/../database/db_connection.php';
?>