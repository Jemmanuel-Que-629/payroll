<?php

// messages.php
// Handles "Send Us a Message" submissions from the public contact form.

require __DIR__ . '/../bootstrap.php'; // loads .env, starts session, and provides $conn (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php#contact');
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('CONTACT ERROR - Database connection is not available.');
    header('Location: ../index.php?section=contact&status=error#contact');
    exit;
}

// Basic sanitization and validation
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = trim($_POST['phone']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $subject === '' || $body === '') {
    header('Location: ../index.php?section=contact&status=incomplete#contact');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../index.php?section=contact&status=invalid_email#contact');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $stmt = $conn->prepare('INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $name, $email, $phone, $subject, $body);
    $stmt->execute();
    $stmt->close();

    header('Location: ../index.php?section=contact&status=success#contact');
    exit;
} catch (mysqli_sql_exception $e) {
    error_log('CONTACT ERROR - Database error: ' . $e->getMessage());
    header('Location: ../index.php?section=contact&status=error#contact');
    exit;
}
