<?php
/**
 * Migration Script: Update Schema and link Forum to Users
 * Run this via command line: php migrate.php
 */

require_once __DIR__ . '/src/db.php';

try {
    $db = db();
    echo "Starting migration...\n";

    // 1. Add avatar_url and idx_users_role to users
    echo "Updating users table...\n";
    $db->exec("ALTER TABLE users MODIFY role ENUM('MEMBER','ADMIN','MENTOR','SUPERADMIN') NOT NULL DEFAULT 'MEMBER'");
    
    // Check if column exists first (basic idempotency)
    $columns = $db->query("SHOW COLUMNS FROM users LIKE 'avatar_url'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(500) NULL AFTER role");
    }
    
    // Add index if not exists
    try {
        $db->exec("CREATE INDEX idx_users_role ON users(role)");
    } catch (PDOException $e) {
        // Assume index already exists
    }

    // 2. Update forum_topics
    echo "Updating forum_topics table...\n";
    $columns = $db->query("SHOW COLUMNS FROM forum_topics LIKE 'author_id'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE forum_topics ADD COLUMN author_id BIGINT UNSIGNED NULL AFTER id");
    }

    // 3. Update forum_replies
    echo "Updating forum_replies table...\n";
    $columns = $db->query("SHOW COLUMNS FROM forum_replies LIKE 'author_id'")->fetchAll();
    if (empty($columns)) {
        $db->exec("ALTER TABLE forum_replies ADD COLUMN author_id BIGINT UNSIGNED NULL AFTER topic_id");
    }

    // 4. Link existing posts to users based on author_email
    echo "Linking posts to users...\n";
    
    // Topics
    $stmt = $db->query("SELECT id, author_email FROM forum_topics WHERE author_id IS NULL");
    while ($row = $stmt->fetch()) {
        $userStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $userStmt->execute([$row['author_email']]);
        $user = $userStmt->fetch();
        if ($user) {
            $update = $db->prepare("UPDATE forum_topics SET author_id = ? WHERE id = ?");
            $update->execute([$user['id'], $row['id']]);
        }
    }

    // Replies
    $stmt = $db->query("SELECT id, author_email FROM forum_replies WHERE author_id IS NULL");
    while ($row = $stmt->fetch()) {
        $userStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $userStmt->execute([$row['author_email']]);
        $user = $userStmt->fetch();
        if ($user) {
            $update = $db->prepare("UPDATE forum_replies SET author_id = ? WHERE id = ?");
            $update->execute([$user['id'], $row['id']]);
        }
    }

    // 5. Cleanup: Set remaining null author_id to a system admin or delete (we'll set NOT NULL later)
    // For safety in this script, we'll just report
    $nullTopics = $db->query("SELECT COUNT(*) FROM forum_topics WHERE author_id IS NULL")->fetchColumn();
    $nullReplies = $db->query("SELECT COUNT(*) FROM forum_replies WHERE author_id IS NULL")->fetchColumn();
    
    echo "Migration report:\n";
    echo "- Topics without author: $nullTopics\n";
    echo "- Replies without author: $nullReplies\n";

    if ($nullTopics == 0 && $nullReplies == 0) {
        echo "Finalizing constraints...\n";
        $db->exec("ALTER TABLE forum_topics MODIFY author_id BIGINT UNSIGNED NOT NULL");
        $db->exec("ALTER TABLE forum_replies MODIFY author_id BIGINT UNSIGNED NOT NULL");
        
        // Add Foreign Keys if they don't exist
        try {
            $db->exec("ALTER TABLE forum_topics ADD CONSTRAINT fk_forum_topics_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE");
            $db->exec("ALTER TABLE forum_replies ADD CONSTRAINT fk_forum_replies_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE");
        } catch (PDOException $e) {
            // Constraints might already exist
        }
    } else {
        echo "WARNING: Constraints not applied because some posts could not be linked to a user. Please fix manually.\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
