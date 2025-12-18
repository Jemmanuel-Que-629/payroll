<?php
// bootstrap.php

// Composer autoload for phpdotenv and other dependencies
require __DIR__ . '/vendor/autoload.php';

// Load .env from the project root
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

// Error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/uploads/error/php-error.log');
error_reporting(E_ALL);

// Start session
session_start();

// Include DB connection from this project
require __DIR__ . '/database/db_connection.php';
?>