<?php
declare(strict_types=1);

require_once __DIR__ . '/src/db.php';

try {
    $pdo = db();
    
    // Création de la table chat_likes
    $sql = "
    CREATE TABLE IF NOT EXISTS chat_likes (
        user_id BIGINT UNSIGNED NOT NULL,
        message_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, message_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Migration reussie: table chat_likes creee avec succes.\n";
} catch (Exception $e) {
    echo "Erreur lors de la migration: " . $e->getMessage() . "\n";
}
