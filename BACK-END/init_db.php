<?php
/**
 * ============================================================
 *  DakarTech-Hack — Script d'initialisation complet de la BDD
 * ============================================================
 * 
 * Ce script effectue en UNE SEULE PASSE :
 *   1.  Connexion à MySQL (sans sélectionner de BDD)
 *   2.  Création de la BDD si elle n'existe pas
 *   3.  Création de toutes les tables du schéma
 *   4.  Insertion / mise à jour des utilisateurs par défaut
 *   5.  Insertion / mise à jour des membres d'équipe par défaut
 *
 * USAGE :
 *   php init_db.php
 *
 * SÉCURITÉ :
 *   À SUPPRIMER du serveur de production après utilisation.
 * ============================================================
 */

declare(strict_types=1);

// ----------------------------------------------------------------
// 0. Configuration (copie de config/config.php pour autonomie)
// ----------------------------------------------------------------
$config = [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'dbname'  => 'dakartech_hack',
    'user'    => 'root',
    'pass'    => '',       // ← Modifier si votre MySQL a un mot de passe
    'charset' => 'utf8mb4',
];

echo "\n";
echo "=================================================\n";
echo "  DakarTech-Hack — Initialisation de la base\n";
echo "=================================================\n\n";

// ----------------------------------------------------------------
// 1. Connexion SANS spécifier la BDD (pour pouvoir la créer)
// ----------------------------------------------------------------
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;charset=%s',
        $config['host'],
        $config['port'],
        $config['charset']
    );
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[OK] Connexion à MySQL réussie.\n";
} catch (PDOException $e) {
    die("[ERREUR] Impossible de se connecter à MySQL : " . $e->getMessage() . "\n");
}

// ----------------------------------------------------------------
// 2. Création de la BDD
// ----------------------------------------------------------------
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$config['dbname']}`");
echo "[OK] Base de données '{$config['dbname']}' prête.\n";

// ----------------------------------------------------------------
// 3. Création des tables (idempotent — IF NOT EXISTS)
// ----------------------------------------------------------------
echo "\n[INFO] Création des tables...\n";

$tables = [
    'users' => "
        CREATE TABLE IF NOT EXISTS users (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            username     VARCHAR(50)     NOT NULL,
            email        VARCHAR(190)    NOT NULL,
            password_hash VARCHAR(255)   NOT NULL,
            role         ENUM('MEMBER','ADMIN','MENTOR','SUPERADMIN') NOT NULL DEFAULT 'MEMBER',
            avatar_url   VARCHAR(500)    NULL,
            created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_users_email    (email),
            UNIQUE KEY uq_users_username (username),
            KEY idx_users_role           (role)
        ) ENGINE=InnoDB
    ",

    'user_tokens' => "
        CREATE TABLE IF NOT EXISTS user_tokens (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id    BIGINT UNSIGNED NOT NULL,
            token      CHAR(64)        NOT NULL,
            expires_at DATETIME        NULL,
            created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_user_tokens_token     (token),
            KEY idx_user_tokens_user_id          (user_id),
            CONSTRAINT fk_user_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ",

    'contact_messages' => "
        CREATE TABLE IF NOT EXISTS contact_messages (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            full_name  VARCHAR(120)    NOT NULL,
            email      VARCHAR(190)    NOT NULL,
            subject    VARCHAR(50)     NOT NULL,
            message    TEXT            NOT NULL,
            created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_contact_messages_email (email)
        ) ENGINE=InnoDB
    ",

    'team_members' => "
        CREATE TABLE IF NOT EXISTS team_members (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            team       ENUM('formation','ctf') NOT NULL,
            pseudo     VARCHAR(80)     NOT NULL,
            avatar_url VARCHAR(500)    NULL,
            is_captain TINYINT(1)      NOT NULL DEFAULT 0,
            created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_team_members_team (team)
        ) ENGINE=InnoDB
    ",

    'forum_topics' => "
        CREATE TABLE IF NOT EXISTS forum_topics (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            author_id    BIGINT UNSIGNED NOT NULL,
            title        VARCHAR(180)    NOT NULL,
            content      TEXT            NOT NULL,
            category     VARCHAR(50)     NOT NULL,
            author_name  VARCHAR(120)    NOT NULL,
            author_email VARCHAR(190)    NOT NULL,
            created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_forum_topics_category (category),
            KEY idx_forum_topics_author   (author_id),
            CONSTRAINT fk_forum_topics_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ",

    'forum_replies' => "
        CREATE TABLE IF NOT EXISTS forum_replies (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            topic_id     BIGINT UNSIGNED NOT NULL,
            author_id    BIGINT UNSIGNED NOT NULL,
            content      TEXT            NOT NULL,
            author_name  VARCHAR(120)    NOT NULL,
            author_email VARCHAR(190)    NOT NULL,
            created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_forum_replies_topic  (topic_id),
            KEY idx_forum_replies_author (author_id),
            CONSTRAINT fk_forum_replies_topic  FOREIGN KEY (topic_id)  REFERENCES forum_topics(id) ON DELETE CASCADE,
            CONSTRAINT fk_forum_replies_author FOREIGN KEY (author_id) REFERENCES users(id)       ON DELETE CASCADE
        ) ENGINE=InnoDB
    ",

    'news_articles' => "
        CREATE TABLE IF NOT EXISTS news_articles (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title        VARCHAR(255)    NOT NULL,
            content      TEXT            NOT NULL,
            image_url    VARCHAR(500)    NULL,
            author       VARCHAR(100)    NOT NULL DEFAULT 'Admin',
            published_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB
    ",

    'ctf_challenges' => "
        CREATE TABLE IF NOT EXISTS ctf_challenges (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title       VARCHAR(255)    NOT NULL,
            description TEXT            NOT NULL,
            category    VARCHAR(50)     NOT NULL,
            points      INT             NOT NULL DEFAULT 0,
            flag        VARCHAR(255)    NOT NULL,
            created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_ctf_challenges_category (category)
        ) ENGINE=InnoDB
    ",

    'ctf_solves' => "
        CREATE TABLE IF NOT EXISTS ctf_solves (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id      BIGINT UNSIGNED NOT NULL,
            challenge_id BIGINT UNSIGNED NOT NULL,
            solved_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_ctf_solves (user_id, challenge_id),
            CONSTRAINT fk_ctf_solves_user      FOREIGN KEY (user_id)      REFERENCES users(id)          ON DELETE CASCADE,
            CONSTRAINT fk_ctf_solves_challenge FOREIGN KEY (challenge_id) REFERENCES ctf_challenges(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ",
];

foreach ($tables as $name => $sql) {
    $pdo->exec($sql);
    echo "  [OK] Table '$name'\n";
}

// ----------------------------------------------------------------
// 4. Données par défaut — Utilisateurs
// ----------------------------------------------------------------
echo "\n[INFO] Insertion des utilisateurs par défaut...\n";

$users = [
    // ----- SUPERADMIN -----
    [
        'username'   => 'D4rkCrypt3',
        'email'      => 'superadmin@dakartech.hack',
        'password'   => '!@dk615536!@DKMD',
        'role'       => 'SUPERADMIN',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=D4rkCrypt3',
    ],

    // ----- ADMINS -----
    [
        'username'   => 'AdminTest',
        'email'      => 'admin@dakartech.hack',
        'password'   => 'AdminPassword123!',
        'role'       => 'ADMIN',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=AdminTest',
    ],
    [
        'username'   => 'AdminSupport',
        'email'      => 'support@dakartech.hack',
        'password'   => 'SupportAdmin456!',
        'role'       => 'ADMIN',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=AdminSupport',
    ],
    [
        'username'   => 'AdminChief',
        'email'      => 'chief@dakartech.hack',
        'password'   => 'ChiefAdmin789!',
        'role'       => 'ADMIN',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=AdminChief',
    ],

    // ----- MENTORS -----
    [
        'username'   => 'MentorSenior',
        'email'      => 'mentor.senior@dakartech.hack',
        'password'   => 'MentorSenior2024!',
        'role'       => 'MENTOR',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=MentorSenior',
    ],
    [
        'username'   => 'MentorJunior',
        'email'      => 'mentor.junior@dakartech.hack',
        'password'   => 'MentorJunior2024!',
        'role'       => 'MENTOR',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=MentorJunior',
    ],

    // ----- MEMBRES -----
    [
        'username'   => 'MemberTest',
        'email'      => 'member@dakartech.hack',
        'password'   => 'MemberPassword123!',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=MemberTest',
    ],
    [
        'username'   => 'HackerNewbie',
        'email'      => 'newbie@dakartech.hack',
        'password'   => 'NewbieHack1!',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=HackerNewbie',
    ],
    [
        'username'   => 'CyberNinja',
        'email'      => 'ninja@dakartech.hack',
        'password'   => 'NinjaCyber2!',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=CyberNinja',
    ],
    [
        'username'   => 'ProPlayer',
        'email'      => 'pro@dakartech.hack',
        'password'   => 'PlayerPro3!',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=ProPlayer',
    ],
    [
        'username'   => 'RedTeamer',
        'email'      => 'redteam@dakartech.hack',
        'password'   => 'RedTeam2024!',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=RedTeamer',
    ],
    [
        'username'   => 'BlueDefender',
        'email'      => 'bluedefender@dakartech.hack',
        'password'   => 'BlueDefend2024!',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=BlueDefender',
    ],
    [
        'username'   => 'Deo',
        'email'      => 'deo@dakartech.hack',
        'password'   => 'passer123',
        'role'       => 'MEMBER',
        'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=Deo',
    ],
];

$userStmt = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash, role, avatar_url)
     VALUES (:username, :email, :password_hash, :role, :avatar_url)
     ON DUPLICATE KEY UPDATE
         password_hash = VALUES(password_hash),
         role          = VALUES(role),
         avatar_url    = VALUES(avatar_url)'
);

foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    $userStmt->execute([
        ':username'      => $u['username'],
        ':email'         => $u['email'],
        ':password_hash' => $hash,
        ':role'          => $u['role'],
        ':avatar_url'    => $u['avatar_url'],
    ]);
    $action = $userStmt->rowCount() === 1 ? 'Ajouté  ' : 'Mis à jour';
    echo "  [{$action}] [{$u['role']}] {$u['username']} ({$u['email']})\n";
}

// ----------------------------------------------------------------
// 5. Données par défaut — Membres d'équipe
// ----------------------------------------------------------------
echo "\n[INFO] Insertion des membres d'équipes par défaut...\n";

$teamMembers = [
    // Équipe CTF
    ['team' => 'ctf', 'pseudo' => 'D4rkCrypt3',   'is_captain' => 1, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=D4rkCrypt3'],
    ['team' => 'ctf', 'pseudo' => 'CyberNinja',   'is_captain' => 0, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=CyberNinja'],
    ['team' => 'ctf', 'pseudo' => 'RedTeamer',    'is_captain' => 0, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=RedTeamer'],
    ['team' => 'ctf', 'pseudo' => 'ProPlayer',    'is_captain' => 0, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=ProPlayer'],

    // Équipe Formation
    ['team' => 'formation', 'pseudo' => 'MentorSenior', 'is_captain' => 1, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=MentorSenior'],
    ['team' => 'formation', 'pseudo' => 'MentorJunior', 'is_captain' => 0, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=MentorJunior'],
    ['team' => 'formation', 'pseudo' => 'HackerNewbie', 'is_captain' => 0, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=HackerNewbie'],
    ['team' => 'formation', 'pseudo' => 'BlueDefender', 'is_captain' => 0, 'avatar_url' => 'https://api.dicebear.com/7.x/lorelei/svg?seed=BlueDefender'],
];

// Pour les membres d'équipe, on vérifie par (team, pseudo) pour éviter les doublons
$checkTeamStmt  = $pdo->prepare('SELECT id FROM team_members WHERE team = ? AND pseudo = ? LIMIT 1');
$insertTeamStmt = $pdo->prepare('INSERT INTO team_members (team, pseudo, avatar_url, is_captain) VALUES (?, ?, ?, ?)');
$updateTeamStmt = $pdo->prepare('UPDATE team_members SET avatar_url = ?, is_captain = ? WHERE id = ?');

foreach ($teamMembers as $m) {
    $checkTeamStmt->execute([$m['team'], $m['pseudo']]);
    $existing = $checkTeamStmt->fetch();
    if ($existing) {
        $updateTeamStmt->execute([$m['avatar_url'], $m['is_captain'], $existing['id']]);
        echo "  [Mis à jour] [{$m['team']}] {$m['pseudo']}\n";
    } else {
        $insertTeamStmt->execute([$m['team'], $m['pseudo'], $m['avatar_url'], $m['is_captain']]);
        echo "  [Ajouté   ] [{$m['team']}] {$m['pseudo']}\n";
    }
}

// ----------------------------------------------------------------
// 6. Rapport final
// ----------------------------------------------------------------
echo "\n=================================================\n";
echo "  Initialisation terminée avec succès !\n";
echo "=================================================\n";
echo "\nComptes disponibles :\n";
echo "------------------------------------------------------\n";
printf("  %-15s | %-10s | %s\n", 'USERNAME', 'ROLE', 'MOT DE PASSE');
echo "------------------------------------------------------\n";
foreach ($users as $u) {
    printf("  %-15s | %-10s | %s\n", $u['username'], $u['role'], $u['password']);
}
echo "------------------------------------------------------\n";
echo "\nLancez le serveur PHP : php -S localhost:8000 -t BACK-END/public\n";
echo "Puis visitez       : http://localhost/ATTENTION04/FRONT-END/forum_login.html\n\n";
