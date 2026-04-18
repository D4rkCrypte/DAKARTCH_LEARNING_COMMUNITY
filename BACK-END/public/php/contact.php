<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/http.php';
require_once __DIR__ . '/../../src/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    json_response(200, ['success' => true]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(405, ['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

$errors = [];
if ($name === '') { $errors[] = 'Nom requis'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email invalide'; }
if ($subject === '') { $errors[] = 'Sujet requis'; }
if ($message === '') { $errors[] = 'Message requis'; }

if ($errors !== []) {
    json_response(400, ['success' => false, 'errors' => $errors]);
    exit;
}

$stmt = db()->prepare('INSERT INTO contact_messages (full_name, email, subject, message) VALUES (:full_name, :email, :subject, :message)');
$stmt->execute([
    ':full_name' => $name,
    ':email' => $email,
    ':subject' => $subject,
    ':message' => $message,
]);

json_response(200, ['success' => true, 'message' => 'Message enregistré']);
