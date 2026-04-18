<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function create_token_for_user(int $userId): string
{
    $config = require __DIR__ . '/../config/config.php';
    $ttl = (int)$config['security']['token_ttl_seconds'];

    $token = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable())->add(new DateInterval('PT' . $ttl . 'S'));

    $stmt = db()->prepare('INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)');
    $stmt->execute([
        ':user_id' => $userId,
        ':token' => $token,
        ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
    ]);

    return $token;
}

function user_from_token(?string $token): ?array
{
    if ($token === null || $token === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT u.id, u.username, u.email, u.role
         FROM user_tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.token = :token AND (t.expires_at IS NULL OR t.expires_at > NOW())
         LIMIT 1'
    );
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}
