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

$config = require __DIR__ . '/../../config/config.php';
$adminPassword = (string)$config['security']['admin_password'];

$pass = (string)($_POST['password'] ?? '');
$team = trim((string)($_POST['team'] ?? ''));
$pseudo = trim((string)($_POST['pseudo'] ?? ''));
$avatar = trim((string)($_POST['avatar'] ?? ''));
$isCaptain = (string)($_POST['isCaptain'] ?? '') === 'true' ? 1 : 0;

$role = trim((string)($_POST['role'] ?? ''));
$specialties = trim((string)($_POST['specialties'] ?? ''));
$bio = trim((string)($_POST['bio'] ?? ''));

if ($pass !== $adminPassword) {
    json_response(401, ['success' => false, 'error' => 'Mot de passe admin incorrect']);
    exit;
}

if ($team !== 'formation' && $team !== 'ctf') {
    json_response(400, ['success' => false, 'error' => 'Team invalide']);
    exit;
}

if ($pseudo === '') {
    json_response(400, ['success' => false, 'error' => 'Pseudo requis']);
    exit;
}

$avatarUrl = $avatar !== '' ? $avatar : ('https://api.dicebear.com/7.x/identicon/svg?seed=' . rawurlencode($pseudo));

$stmt = db()->prepare('INSERT INTO team_members (team, pseudo, avatar_url, is_captain, role, specialties, bio) VALUES (:team, :pseudo, :avatar_url, :is_captain, :role, :specialties, :bio)');
$stmt->execute([
    ':team' => $team,
    ':pseudo' => $pseudo,
    ':avatar_url' => $avatarUrl,
    ':is_captain' => $isCaptain,
    ':role' => $role,
    ':specialties' => $specialties,
    ':bio' => $bio,
]);

json_response(200, ['success' => true]);
