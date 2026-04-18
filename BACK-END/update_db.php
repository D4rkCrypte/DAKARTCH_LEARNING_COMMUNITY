<?php
declare(strict_types=1);
require_once __DIR__ . '/src/db.php';

$pdo = db();

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Add chat_likes table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS chat_likes (
        message_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (message_id, user_id),
        FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Add parent_id to chat_messages
    try {
        $pdo->exec("ALTER TABLE chat_messages ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER id;");
        $pdo->exec("ALTER TABLE chat_messages ADD CONSTRAINT fk_chat_parent FOREIGN KEY (parent_id) REFERENCES chat_messages(id) ON DELETE CASCADE;");
    } catch(Exception $e) { /* Ignore if exists */ }

    // 3. Delete all users except D4rkCrypt3 and Deo
    $pdo->exec("DELETE FROM users WHERE username NOT IN ('D4rkCrypt3', 'Deo');");
    $pdo->exec("DELETE FROM team_members WHERE pseudo NOT IN ('D4rkCrypt3', 'Deo');");
    // This will cascade delete their messages, tokens, solves, replies, etc.

    // 4. Insert new users
    $hash = password_hash('passer123', PASSWORD_DEFAULT);
    $users = [
        // username, email, role, avatar seed, team (if any), is_captain
        ['Dios', 'dios@dakartech.hack', 'MEMBER', 'ctf', 0],
        ['Joel', 'joel@dakartech.hack', 'MENTOR', null, 0],
        ['Prince', 'prince@dakartech.hack', 'MEMBER', 'formation', 0],
        ['Divine', 'divine@dakartech.hack', 'MEMBER', 'ctf', 1],
    ];

    $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, avatar_url) VALUES (?, ?, ?, ?, ?)");
    $stmtTeam = $pdo->prepare("INSERT INTO team_members (team, pseudo, avatar_url, is_captain) VALUES (?, ?, ?, ?)");

    foreach ($users as $u) {
        $username = $u[0];
        $email = $u[1];
        $role = $u[2];
        $avatar = 'https://api.dicebear.com/7.x/lorelei/svg?seed=' . $username;
        $team = $u[3];
        $is_captain = $u[4];

        // Ensure user doesn't already exist
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if (!$check->fetch()) {
            $stmtUser->execute([$username, $email, $hash, $role, $avatar]);
        }

        if ($team !== null) {
            $checkT = $pdo->prepare("SELECT id FROM team_members WHERE pseudo = ?");
            $checkT->execute([$username]);
            if (!$checkT->fetch()) {
                $stmtTeam->execute([$team, $username, $avatar, $is_captain]);
            }
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Success!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
