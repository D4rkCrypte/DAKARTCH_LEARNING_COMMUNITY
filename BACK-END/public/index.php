<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
if (!is_string($path)) {
    $path = '/';
}

// Adjust path when running in a subdirectory (e.g. XAMPP htdocs)
$publicPos = strpos(strtolower($path), '/public');
if ($publicPos !== false) {
    $path = substr($path, $publicPos + 7);
}
if ($path === '' || $path === false) {
    $path = '/';
}

if ($method === 'OPTIONS') {
    json_response(200, ['success' => true]);
    exit;
}

if ($method === 'GET' && $path === '/health') {
    json_response(200, ['success' => true, 'data' => ['status' => 'ok']]);
    exit;
}

if ($method === 'POST' && $path === '/api/auth/register') {
    $body = read_json_body();
    $username = trim((string)($body['username'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        json_response(400, ['success' => false, 'error' => 'Champs requis: username, email, password']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(400, ['success' => false, 'error' => 'Email invalide']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $hash,
            ':role' => 'MEMBER',
        ]);

        $userId = (int)db()->lastInsertId();
        $token = create_token_for_user($userId);

        json_response(201, [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'role' => 'MEMBER',
                    'avatar_url' => null,
                ],
            ],
        ]);
        exit;
    } catch (Throwable $e) {
        json_response(409, ['success' => false, 'error' => 'Email ou username déjà utilisé']);
        exit;
    }
}

if ($method === 'POST' && $path === '/api/auth/login') {
    $body = read_json_body();
    $username = trim((string)($body['username'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($username === '' || $password === '') {
        json_response(400, ['success' => false, 'error' => 'Champs requis: username (pseudo), password']);
        exit;
    }

    $stmt = db()->prepare('SELECT id, username, email, password_hash, role, avatar_url FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!is_array($user) || !password_verify($password, (string)$user['password_hash'])) {
        json_response(401, ['success' => false, 'error' => 'Identifiants invalides']);
        exit;
    }

    $token = create_token_for_user((int)$user['id']);

    json_response(200, [
        'success' => true,
        'data' => [
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'username' => (string)$user['username'],
                'email' => (string)$user['email'],
                'role' => (string)$user['role'],
                'avatar_url' => $user['avatar_url'],
            ],
        ],
    ]);
    exit;
}

if ($method === 'GET' && $path === '/api/auth/profile') {
    $token = bearer_token();
    $user = user_from_token($token);

    if ($user === null) {
        json_response(401, ['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    json_response(200, ['success' => true, 'data' => ['user' => $user]]);
    exit;
}

if ($method === 'GET' && $path === '/api/admin/users') {
    $token = bearer_token();
    $me = user_from_token($token);

    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Non authentifié']);
        exit;
    }
    if ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN') {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $stmt = db()->query('SELECT id, username, email, role, avatar_url, created_at FROM users ORDER BY id DESC');
    $rows = $stmt->fetchAll();

    json_response(200, ['success' => true, 'data' => ['users' => $rows]]);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/users/add') {
    $token = bearer_token();
    $me = user_from_token($token);

    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $username = trim((string)($body['username'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $password = (string)($body['password'] ?? '');
    $role = (string)($body['role'] ?? 'MEMBER');
    $avatarUrl = trim((string)($body['avatarUrl'] ?? ''));

    // Validation des rôles
    $allowedRolesForAdmin = ['MEMBER', 'MENTOR'];
    $allowedRolesForSuperAdmin = ['MEMBER', 'MENTOR', 'ADMIN', 'SUPERADMIN'];

    if ((string)$me['role'] === 'ADMIN' && !in_array($role, $allowedRolesForAdmin)) {
        json_response(403, ['success' => false, 'error' => 'Un Admin ne peut ajouter que des Membres ou des Mentors']);
        exit;
    }

    if ($avatarUrl === '' && ($role === 'MENTOR' || $role === 'ADMIN' || $role === 'SUPERADMIN')) {
        $avatarUrl = 'https://api.dicebear.com/7.x/identicon/svg?seed=' . rawurlencode($username);
    }


    if ($username === '' || $email === '' || $password === '') {
        json_response(400, ['success' => false, 'error' => 'Champs requis: username, email, password']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, role, avatar_url) VALUES (:username, :email, :password_hash, :role, :avatar_url)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $hash,
            ':role' => $role,
            ':avatar_url' => $avatarUrl,
        ]);


        json_response(201, ['success' => true, 'message' => 'Utilisateur ajouté avec succès']);
        exit;
    } catch (Throwable $e) {
        json_response(409, ['success' => false, 'error' => 'Erreur: pseudo ou email déjà utilisé']);
        exit;
    }
}

if ($method === 'POST' && $path === '/api/admin/users/delete') {
    $token = bearer_token();
    $me = user_from_token($token);

    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $userId = (int)($body['id'] ?? 0);

    // Vérifier les privilèges de suppression
    $stmtUser = db()->prepare('SELECT role FROM users WHERE id = :id');
    $stmtUser->execute([':id' => $userId]);
    $targetUser = $stmtUser->fetch();

    if (!$targetUser) {
        json_response(404, ['success' => false, 'error' => 'Utilisateur introuvable']);
        exit;
    }

    if ((string)$me['role'] === 'ADMIN') {
        if (in_array((string)$targetUser['role'], ['ADMIN', 'SUPERADMIN'])) {
            json_response(403, ['success' => false, 'error' => 'Un Admin ne peut pas supprimer un autre Admin ou un Superadmin']);
            exit;
        }
    }

    if ($userId === (int)$me['id']) {
        json_response(400, ['success' => false, 'error' => 'Vous ne pouvez pas vous supprimer vous-même']);
        exit;
    }

    $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);

    json_response(200, ['success' => true, 'message' => 'Utilisateur supprimé']);
    exit;
}

if ($method === 'GET' && $path === '/api/admin/team') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $stmt = db()->query('SELECT * FROM team_members ORDER BY id DESC');
    json_response(200, ['success' => true, 'data' => ['members' => $stmt->fetchAll()]]);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/team/add') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $team = trim((string)($body['team'] ?? ''));
    $pseudo = trim((string)($body['pseudo'] ?? ''));
    $isCaptain = (int)($body['isCaptain'] ?? 0);
    $avatarUrl = trim((string)($body['avatarUrl'] ?? ''));
    $role = trim((string)($body['role'] ?? ''));
    $specialties = trim((string)($body['specialties'] ?? ''));
    $bio = trim((string)($body['bio'] ?? ''));

    if ($team === '' || $pseudo === '') {
        json_response(400, ['success' => false, 'error' => 'Champs team et pseudo requis']); exit;
    }

    $stmt = db()->prepare('INSERT INTO team_members (team, pseudo, is_captain, avatar_url, role, specialties, bio) VALUES (:team, :pseudo, :is_captain, :avatar_url, :role, :specialties, :bio)');
    $stmt->execute([
        ':team' => $team,
        ':pseudo' => $pseudo,
        ':is_captain' => $isCaptain,
        ':avatar_url' => $avatarUrl,
        ':role' => $role,
        ':specialties' => $specialties,
        ':bio' => $bio
    ]);
    json_response(201, ['success' => true, 'message' => 'Membre ajouté à l\'équipe']);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/team/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $team = trim((string)($body['team'] ?? ''));
    $pseudo = trim((string)($body['pseudo'] ?? ''));
    $isCaptain = (int)($body['isCaptain'] ?? 0);
    $avatarUrl = trim((string)($body['avatarUrl'] ?? ''));
    $role = trim((string)($body['role'] ?? ''));
    $specialties = trim((string)($body['specialties'] ?? ''));
    $bio = trim((string)($body['bio'] ?? ''));

    if ($id <= 0 || $team === '' || $pseudo === '') {
        json_response(400, ['success' => false, 'error' => 'ID, team et pseudo requis']); exit;
    }

    $stmt = db()->prepare('UPDATE team_members SET team = :team, pseudo = :pseudo, is_captain = :is_captain, avatar_url = :avatar_url, role = :role, specialties = :specialties, bio = :bio WHERE id = :id');
    $stmt->execute([
        ':team' => $team,
        ':pseudo' => $pseudo,
        ':is_captain' => $isCaptain,
        ':avatar_url' => $avatarUrl,
        ':role' => $role,
        ':specialties' => $specialties,
        ':bio' => $bio,
        ':id' => $id
    ]);
    json_response(200, ['success' => true, 'message' => 'Membre mis à jour']);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/team/delete') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $stmt = db()->prepare('DELETE FROM team_members WHERE id = :id');
    $stmt->execute([':id' => $id]);
    json_response(200, ['success' => true, 'message' => 'Membre retiré de l\'équipe']);
    exit;
}

if ($method === 'POST' && $path === '/api/forum/topics') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Authentification requise pour publier']);
        exit;
    }

    $body = read_json_body();

    $title = trim((string)($body['title'] ?? ''));
    $content = trim((string)($body['content'] ?? ''));
    $category = trim((string)($body['category'] ?? 'general'));
    
    // Use authenticated user info instead of trusted client-provided info
    $authorName = (string)$me['username'];
    $authorEmail = (string)$me['email'];

    if ($title === '' || $content === '') {
        json_response(400, ['success' => false, 'error' => 'Champs requis: title, content']);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO forum_topics (author_id, title, content, category, author_name, author_email) VALUES (:author_id, :title, :content, :category, :author_name, :author_email)');
    $stmt->execute([
        ':author_id' => $me['id'],
        ':title' => $title,
        ':content' => $content,
        ':category' => $category,
        ':author_name' => $authorName,
        ':author_email' => $authorEmail,
    ]);

    json_response(201, ['success' => true, 'data' => ['id' => (int)db()->lastInsertId()]]);
    exit;
}

if ($method === 'GET' && $path === '/api/forum/topics') {
    $stmt = db()->query('
        SELECT t.id, t.title, t.category, t.created_at, u.username as author_name, u.avatar_url 
        FROM forum_topics t
        JOIN users u ON u.id = t.author_id
        ORDER BY t.id DESC
    ');
    $rows = $stmt->fetchAll();
    json_response(200, ['success' => true, 'data' => ['topics' => $rows]]);
    exit;
}

if ($method === 'GET' && preg_match('#^/api/forum/topics/(\d+)$#', $path, $matches)) {
    $topicId = (int)$matches[1];
    
    $stmt = db()->prepare('
        SELECT t.id, t.title, t.content, t.category, t.created_at, u.username as author_name, u.avatar_url 
        FROM forum_topics t
        JOIN users u ON u.id = t.author_id
        WHERE t.id = :id
    ');
    $stmt->execute([':id' => $topicId]);
    $topic = $stmt->fetch();
    
    if (!$topic) {
        json_response(404, ['success' => false, 'error' => 'Topic introuvable']);
        exit;
    }

    $stmtReplies = db()->prepare('
        SELECT r.id, r.content, r.created_at, u.username as author_name, u.avatar_url 
        FROM forum_replies r
        JOIN users u ON u.id = r.author_id
        WHERE r.topic_id = :topic_id 
        ORDER BY r.id ASC
    ');
    $stmtReplies->execute([':topic_id' => $topicId]);
    $replies = $stmtReplies->fetchAll();

    json_response(200, [
        'success' => true,
        'data' => [
            'topic' => $topic,
            'replies' => $replies
        ]
    ]);
    exit;
}

if ($method === 'POST' && preg_match('#^/api/forum/topics/(\d+)/replies$#', $path, $matches)) {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Authentification requise pour répondre']);
        exit;
    }

    $topicId = (int)$matches[1];
    $body = read_json_body();
    
    $content = trim((string)($body['content'] ?? ''));
    $authorName = (string)$me['username'];
    $authorEmail = (string)$me['email'];

    if ($content === '') {
        json_response(400, ['success' => false, 'error' => 'Champs requis: content']);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO forum_replies (topic_id, author_id, content, author_name, author_email) VALUES (:topic_id, :author_id, :content, :author_name, :author_email)');
    $stmt->execute([
        ':topic_id' => $topicId,
        ':author_id' => $me['id'],
        ':content' => $content,
        ':author_name' => $authorName,
        ':author_email' => $authorEmail,
    ]);

    json_response(201, ['success' => true, 'data' => ['id' => (int)db()->lastInsertId()]]);
    exit;
}

if ($method === 'POST' && $path === '/api/contact') {
    $body = read_json_body();
    
    $fullName = trim((string)($body['fullName'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $subject = trim((string)($body['subject'] ?? ''));
    $message = trim((string)($body['message'] ?? ''));

    if ($fullName === '' || $email === '' || $subject === '' || $message === '') {
        json_response(400, ['success' => false, 'error' => 'Tous les champs sont requis']);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO contact_messages (full_name, email, subject, message) VALUES (:full_name, :email, :subject, :message)');
    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message,
    ]);

    json_response(201, ['success' => true, 'message' => 'Message envoyé avec succès.']);
    exit;
}

if ($method === 'GET' && $path === '/api/news') {
    $stmt = db()->query('SELECT * FROM news_articles ORDER BY published_at DESC');
    $rows = $stmt->fetchAll();
    json_response(200, ['success' => true, 'data' => ['articles' => $rows]]);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/news/add') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $title = trim((string)($body['title'] ?? ''));
    $content = trim((string)($body['content'] ?? ''));
    $imageUrl = trim((string)($body['imageUrl'] ?? ''));
    $author = trim((string)($body['author'] ?? $me['username']));

    if ($title === '' || $content === '') {
        json_response(400, ['success' => false, 'error' => 'Titre et contenu requis']);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO news_articles (title, content, image_url, author) VALUES (:title, :content, :image_url, :author)');
    $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':image_url' => $imageUrl,
        ':author' => $author
    ]);

    json_response(201, ['success' => true, 'message' => 'Article publié !']);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/news/delete') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    if ($id <= 0) {
        json_response(400, ['success' => false, 'error' => 'ID invalide']);
        exit;
    }

    $stmt = db()->prepare('DELETE FROM news_articles WHERE id = ?');
    $stmt->execute([$id]);

    json_response(200, ['success' => true, 'message' => 'Article supprimé']);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/news/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN', 'MENTOR'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $title = trim((string)($body['title'] ?? ''));
    $content = trim((string)($body['content'] ?? ''));
    $imageUrl = trim((string)($body['imageUrl'] ?? ''));

    if ($id <= 0 || $title === '' || $content === '') {
        json_response(400, ['success' => false, 'error' => 'Données invalides']);
        exit;
    }

    $stmt = db()->prepare('UPDATE news_articles SET title = ?, content = ?, image_url = ? WHERE id = ?');
    $stmt->execute([$title, $content, $imageUrl, $id]);

    json_response(200, ['success' => true, 'message' => 'Article mis à jour']);
    exit;
}

if ($method === 'GET' && $path === '/api/ctf') {
    $token = bearer_token();
    $me = user_from_token($token);

    $stmt = db()->query('SELECT id, title, description, category, points, file_url, created_at FROM ctf_challenges ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();

    $myRank = null;
    if ($me !== null) {
        $stmtSolved = db()->prepare('SELECT challenge_id FROM ctf_solves WHERE user_id = ?');
        $stmtSolved->execute([$me['id']]);
        $solvedIds = $stmtSolved->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as &$row) {
            $row['is_solved'] = in_array((string)$row['id'], $solvedIds, true);
        }

        // Calculer le rang
        $rankSql = '
            SELECT rank FROM (
                SELECT 
                    u.id, 
                    RANK() OVER (ORDER BY COALESCE(SUM(c.points), 0) DESC, COUNT(s.challenge_id) DESC) as rank
                FROM users u
                LEFT JOIN ctf_solves s ON u.id = s.user_id
                LEFT JOIN ctf_challenges c ON s.challenge_id = c.id
                GROUP BY u.id
            ) ranked WHERE id = ?
        ';
        $stmtRank = db()->prepare($rankSql);
        $stmtRank->execute([$me['id']]);
        $rankResult = $stmtRank->fetch();
        $myRank = $rankResult ? (int)$rankResult['rank'] : null;
    }

    json_response(200, ['success' => true, 'data' => [
        'challenges' => $rows,
        'my_rank' => $myRank
    ]]);
    exit;
}

if ($method === 'GET' && $path === '/api/ctf/leaderboard') {
    $sql = '
        SELECT 
            u.id, 
            u.username, 
            u.avatar_url, 
            u.role,
            COALESCE(SUM(c.points), 0) as total_points,
            COUNT(s.challenge_id) as solves_count
        FROM users u
        LEFT JOIN ctf_solves s ON u.id = s.user_id
        LEFT JOIN ctf_challenges c ON s.challenge_id = c.id
        GROUP BY u.id, u.username, u.avatar_url, u.role
        ORDER BY total_points DESC, solves_count DESC
        LIMIT 50
    ';
    $stmt = db()->query($sql);
    $rows = $stmt->fetchAll();
    json_response(200, ['success' => true, 'data' => ['leaderboard' => $rows]]);
    exit;
}

if ($method === 'GET' && $path === '/api/admin/users/stats') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN'])) {
        json_response(403, ['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    $userId = $_GET['id'] ?? null;
    if (!$userId) {
        json_response(400, ['success' => false, 'message' => 'ID manquant']);
        exit;
    }

    // Récupérer les points et comptes par catégorie
    $stmt = db()->prepare('
        SELECT c.category, COUNT(*) as count, SUM(c.points) as points
        FROM ctf_solves s
        JOIN ctf_challenges c ON s.challenge_id = c.id
        WHERE s.user_id = ?
        GROUP BY c.category
    ');
    $stmt->execute([$userId]);
    $stats = $stmt->fetchAll();

    json_response(200, ['success' => true, 'data' => ['stats' => $stats]]);
    exit;
}

if ($method === 'POST' && $path === '/api/ctf/submit') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Veuillez vous connecter']);
        exit;
    }

    $body = read_json_body();
    $challengeId = (int)($body['challenge_id'] ?? 0);
    $flag = trim((string)($body['flag'] ?? ''));

    if ($challengeId <= 0 || $flag === '') {
        json_response(400, ['success' => false, 'error' => 'Flag manquant']);
        exit;
    }

    $stmtCheck = db()->prepare('SELECT 1 FROM ctf_solves WHERE user_id = ? AND challenge_id = ?');
    $stmtCheck->execute([$me['id'], $challengeId]);
    if ($stmtCheck->fetch()) {
        json_response(400, ['success' => false, 'error' => 'Vous avez déjà résolu ce challenge !']);
        exit;
    }

    $stmt = db()->prepare('SELECT id, flag FROM ctf_challenges WHERE id = ?');
    $stmt->execute([$challengeId]);
    $challenge = $stmt->fetch();

    if (!$challenge) {
        json_response(404, ['success' => false, 'error' => 'Challenge introuvable']);
        exit;
    }

    // Comparaison stricte
    if ($challenge['flag'] === $flag) {
        $stmtIns = db()->prepare('INSERT IGNORE INTO ctf_solves (user_id, challenge_id) VALUES (?, ?)');
        $stmtIns->execute([$me['id'], $challengeId]);
        json_response(200, ['success' => true, 'message' => 'Félicitations ! Flag correct.']);
    } else {
        json_response(400, ['success' => false, 'error' => 'Flag incorrect. Essayez encore.']);
    }
    exit;
}

if ($method === 'POST' && $path === '/api/admin/ctf/add') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN', 'MENTOR'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $title = trim((string)($body['title'] ?? ''));
    $description = trim((string)($body['description'] ?? ''));
    $category = trim((string)($body['category'] ?? ''));
    $points = (int)($body['points'] ?? 0);
    $flag = trim((string)($body['flag'] ?? ''));
    $fileUrl = trim((string)($body['fileUrl'] ?? ''));

    if ($title === '' || $description === '' || $category === '' || $flag === '') {
        json_response(400, ['success' => false, 'error' => 'Tous les champs obligatoires (sauf fichier) sont requis']);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO ctf_challenges (title, description, category, points, flag, file_url) VALUES (:title, :description, :category, :points, :flag, :file_url)');
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':category' => $category,
        ':points' => $points,
        ':flag' => $flag,
        ':file_url' => $fileUrl ? $fileUrl : null
    ]);

    json_response(201, ['success' => true, 'message' => 'Challenge CTF ajouté !']);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/ctf/delete') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN', 'MENTOR'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);

    if ($id <= 0) {
        json_response(400, ['success' => false, 'error' => 'ID invalide']);
        exit;
    }

    $stmt = db()->prepare('DELETE FROM ctf_challenges WHERE id = ?');
    $stmt->execute([$id]);

    json_response(200, ['success' => true, 'message' => 'Challenge supprimé']);
    exit;
}

if ($method === 'POST' && $path === '/api/admin/ctf/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN', 'MENTOR'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']);
        exit;
    }

    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $title = trim((string)($body['title'] ?? ''));
    $description = trim((string)($body['description'] ?? ''));
    $category = trim((string)($body['category'] ?? ''));
    $points = (int)($body['points'] ?? 0);
    $flag = trim((string)($body['flag'] ?? ''));
    $fileUrl = trim((string)($body['fileUrl'] ?? ''));

    if ($id <= 0 || $title === '' || $description === '' || $category === '' || $flag === '') {
        json_response(400, ['success' => false, 'error' => 'Données invalides ou incomplètes']);
        exit;
    }

    $stmt = db()->prepare('UPDATE ctf_challenges SET title = ?, description = ?, category = ?, points = ?, flag = ?, file_url = ? WHERE id = ?');
    $stmt->execute([$title, $description, $category, $points, $flag, $fileUrl ? $fileUrl : null, $id]);

    json_response(200, ['success' => true, 'message' => 'Challenge CTF mis à jour !']);
    exit;
}

if ($method === 'GET' && $path === '/api/team/ctf') {
    $stmt = db()->prepare('SELECT id, pseudo, avatar_url, is_captain, role, specialties, bio FROM team_members WHERE team = "ctf" ORDER BY is_captain DESC, id ASC');
    $stmt->execute();
    json_response(200, ['success' => true, 'data' => ['members' => $stmt->fetchAll()]]);
    exit;
}

if ($method === 'GET' && $path === '/api/team/formation') {
    $stmt = db()->prepare('SELECT id, pseudo, avatar_url, is_captain, role, specialties, bio FROM team_members WHERE team = "formation" ORDER BY is_captain DESC, id ASC');
    $stmt->execute();
    json_response(200, ['success' => true, 'data' => ['members' => $stmt->fetchAll()]]);
    exit;
}

if ($method === 'GET' && $path === '/api/mentors') {
    $stmt = db()->prepare('SELECT id, username, email, role, avatar_url FROM users WHERE role = "MENTOR" ORDER BY id ASC');
    $stmt->execute();
    json_response(200, ['success' => true, 'data' => ['mentors' => $stmt->fetchAll()]]);
    exit;
}

if ($method === 'GET' && $path === '/api/forum/members') {
    $stmt = db()->prepare('SELECT id, username, role, avatar_url FROM users WHERE role = "MEMBER" ORDER BY id DESC');
    $stmt->execute();
    json_response(200, ['success' => true, 'data' => ['members' => $stmt->fetchAll()]]);
    exit;
}


if ($method === 'POST' && $path === '/api/team/join') {
    $body = read_json_body();
    
    $team = trim((string)($body['team'] ?? ''));
    $pseudo = trim((string)($body['pseudo'] ?? ''));

    if (!in_array($team, ['formation', 'ctf'], true) || $pseudo === '') {
        json_response(400, ['success' => false, 'error' => 'Champs team (formation/ctf) et pseudo requis']);
        exit;
    }

    $stmt = db()->prepare('INSERT INTO team_members (team, pseudo) VALUES (:team, :pseudo)');
    $stmt->execute([
        ':team' => $team,
        ':pseudo' => $pseudo,
    ]);

    json_response(201, ['success' => true, 'message' => 'Candidature enregistrée avec succès.']);
    exit;
}

// --- UPDATE ENDPOINTS ---

if ($method === 'POST' && $path === '/api/admin/users/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || ((string)$me['role'] !== 'ADMIN' && (string)$me['role'] !== 'SUPERADMIN')) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $username = trim((string)($body['username'] ?? ''));
    $email = trim((string)($body['email'] ?? ''));
    $role = (string)($body['role'] ?? '');
    $avatarUrl = trim((string)($body['avatarUrl'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($id <= 0 || $username === '' || $email === '') {
        json_response(400, ['success' => false, 'error' => 'Champs requis manquants']); exit;
    }

    try {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET username = :u, email = :e, role = :r, avatar_url = :a, password_hash = :p WHERE id = :id');
            $stmt->execute([':u'=>$username, ':e'=>$email, ':r'=>$role, ':a'=>$avatarUrl, ':p'=>$hash, ':id'=>$id]);
        } else {
            $stmt = db()->prepare('UPDATE users SET username = :u, email = :e, role = :r, avatar_url = :a WHERE id = :id');
            $stmt->execute([':u'=>$username, ':e'=>$email, ':r'=>$role, ':a'=>$avatarUrl, ':id'=>$id]);
        }
        json_response(200, ['success' => true, 'message' => 'Mis à jour']); exit;
    } catch (Throwable $e) { json_response(409, ['success' => false, 'error' => 'Conflit de données']); exit; }
}

if ($method === 'POST' && $path === '/api/admin/team/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $pseudo = trim((string)($body['pseudo'] ?? ''));
    $team = (string)($body['team'] ?? '');
    $isCaptain = (int)($body['isCaptain'] ?? 0);
    $avatarUrl = trim((string)($body['avatarUrl'] ?? ''));

    $stmt = db()->prepare('UPDATE team_members SET pseudo = :p, team = :t, is_captain = :c, avatar_url = :a WHERE id = :id');
    $stmt->execute([':p'=>$pseudo, ':t'=>$team, ':c'=>$isCaptain, ':a'=>$avatarUrl, ':id'=>$id]);
    json_response(200, ['success' => true]); exit;
}

if ($method === 'POST' && $path === '/api/admin/news/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $title = trim((string)($body['title'] ?? ''));
    $content = trim((string)($body['content'] ?? ''));
    $imageUrl = trim((string)($body['imageUrl'] ?? ''));

    $stmt = db()->prepare('UPDATE news_articles SET title = :t, content = :c, image_url = :i WHERE id = :id');
    $stmt->execute([':t'=>$title, ':c'=>$content, ':i'=>$imageUrl, ':id'=>$id]);
    json_response(200, ['success' => true]); exit;
}

if ($method === 'POST' && $path === '/api/admin/ctf/update') {
    $token = bearer_token();
    $me = user_from_token($token);
    if ($me === null || !in_array($me['role'], ['ADMIN', 'SUPERADMIN', 'MENTOR'])) {
        json_response(403, ['success' => false, 'error' => 'Accès refusé']); exit;
    }
    $body = read_json_body();
    $id = (int)($body['id'] ?? 0);
    $title = trim((string)($body['title'] ?? ''));
    $desc = trim((string)($body['description'] ?? ''));
    $cat = trim((string)($body['category'] ?? ''));
    $points = (int)($body['points'] ?? 0);
    $flag = trim((string)($body['flag'] ?? ''));
    $fileUrl = trim((string)($body['fileUrl'] ?? ''));

    $stmt = db()->prepare('UPDATE ctf_challenges SET title = :t, description = :d, category = :c, points = :p, flag = :f, file_url = :url WHERE id = :id');
    $stmt->execute([':t'=>$title, ':d'=>$desc, ':c'=>$cat, ':p'=>$points, ':f'=>$flag, ':url'=>$fileUrl ? $fileUrl : null, ':id'=>$id]);
    json_response(200, ['success' => true]); exit;
}

if ($method === 'GET' && $path === '/api/setup/chat') {
    try {
        $sql = "
        CREATE TABLE IF NOT EXISTS chat_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            parent_id BIGINT UNSIGNED DEFAULT NULL,
            likes_count INT UNSIGNED DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES chat_messages(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS chat_likes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            message_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_chat_like (user_id, message_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        db()->exec($sql);
        json_response(200, ['success' => true, 'message' => 'Tables chat initialisées']);
        exit;
    } catch (Throwable $e) {
        json_response(500, ['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

if ($method === 'POST' && $path === '/api/chat/send') {
    $token = bearer_token();
    $me = user_from_token($token);
    
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    $body = read_json_body();
    $message = trim((string)($body['message'] ?? ''));
    $parentId = isset($body['parent_id']) ? (int)$body['parent_id'] : null;

    if ($message === '') {
        json_response(400, ['success' => false, 'error' => 'Message vide']);
        exit;
    }

    try {
        $stmt = db()->prepare('INSERT INTO chat_messages (user_id, message, parent_id) VALUES (:uid, :msg, :pid)');
        $stmt->execute([
            ':uid' => $me['id'],
            ':msg' => $message,
            ':pid' => $parentId > 0 ? $parentId : null
        ]);
        json_response(201, ['success' => true, 'message' => 'Message envoyé']);
        exit;
    } catch (Throwable $e) {
        json_response(500, ['success' => false, 'error' => 'Erreur serveur']);
        exit;
    }
}

if ($method === 'POST' && $path === '/api/chat/like') {
    $token = bearer_token();
    $me = user_from_token($token);
    
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    $body = read_json_body();
    $msgId = isset($body['message_id']) ? (int)$body['message_id'] : 0;

    if ($msgId <= 0) {
        json_response(400, ['success' => false, 'error' => 'ID message invalide']);
        exit;
    }

    try {
        // Obtenir le user ID
        $uid = $me['id'];
        
        // Vérifier si le like existe déjà
        $stmt = db()->prepare('SELECT 1 FROM chat_likes WHERE user_id = ? AND message_id = ?');
        $stmt->execute([$uid, $msgId]);
        $alreadyLiked = $stmt->fetch();
        
        if ($alreadyLiked) {
            // L\'utilisateur a déjà like, donc retirer le like (Toggle Off)
            $delStmt = db()->prepare('DELETE FROM chat_likes WHERE user_id = ? AND message_id = ?');
            $delStmt->execute([$uid, $msgId]);
            
            // Décrémenter le compteur
            $updStmt = db()->prepare('UPDATE chat_messages SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?');
            $updStmt->execute([$msgId]);
            $isLiked = false;
        } else {
            // L\'utilisateur n\'a pas like, donc ajouter le like (Toggle On)
            $insStmt = db()->prepare('INSERT INTO chat_likes (user_id, message_id) VALUES (?, ?)');
            $insStmt->execute([$uid, $msgId]);
            
            // Incrémenter le compteur
            $updStmt = db()->prepare('UPDATE chat_messages SET likes_count = likes_count + 1 WHERE id = ?');
            $updStmt->execute([$msgId]);
            $isLiked = true;
        }
        
        $stmt2 = db()->prepare('SELECT likes_count FROM chat_messages WHERE id = ?');
        $stmt2->execute([$msgId]);
        $newCount = $stmt2->fetchColumn();

        json_response(200, [
            'success' => true, 
            'message' => $isLiked ? 'Like ajouté' : 'Like retiré', 
            'data' => ['liked' => $isLiked, 'likes_count' => (int)$newCount]
        ]);
        exit;
    } catch (Throwable $e) {
        json_response(500, ['success' => false, 'error' => 'Erreur serveur']);
        exit;
    }
}

if ($method === 'POST' && $path === '/api/chat/delete') {
    $token = bearer_token();
    $me = user_from_token($token);
    
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    $body = read_json_body();
    $msgId = isset($body['message_id']) ? (int)$body['message_id'] : 0;

    if ($msgId <= 0) {
        json_response(400, ['success' => false, 'error' => 'ID message invalide']);
        exit;
    }

    try {
        // Enforce ownership: only the author or a SUPERADMIN can delete a message
        $stmt = db()->prepare('SELECT user_id FROM chat_messages WHERE id = ?');
        $stmt->execute([$msgId]);
        $msg = $stmt->fetch();

        if (!$msg) {
            json_response(404, ['success' => false, 'error' => 'Message introuvable']);
            exit;
        }

        if ((int)$msg['user_id'] !== (int)$me['id'] && (string)$me['role'] !== 'SUPERADMIN') {
            json_response(403, ['success' => false, 'error' => 'Non autorisé à supprimer ce message']);
            exit;
        }

        $delStmt = db()->prepare('DELETE FROM chat_messages WHERE id = ?');
        $delStmt->execute([$msgId]);

        json_response(200, ['success' => true, 'message' => 'Message supprimé']);
        exit;
    } catch (Throwable $e) {
        json_response(500, ['success' => false, 'error' => 'Erreur serveur lors de la suppression']);
        exit;
    }
}

if ($method === 'GET' && $path === '/api/chat/messages') {
    $token = bearer_token();
    $me = user_from_token($token);
    
    if ($me === null) {
        json_response(401, ['success' => false, 'error' => 'Non authentifié']);
        exit;
    }

    try {
        $uid = (int)$me['id'];
        $stmt = db()->prepare("
            SELECT 
                c.id, c.message, c.created_at, c.parent_id, c.likes_count, c.user_id,
                u.username, u.role, u.avatar_url,
                (SELECT COUNT(*) FROM chat_likes l WHERE l.message_id = c.id AND l.user_id = ?) as has_liked,
                p.message as parent_message,
                pu.username as parent_username
            FROM chat_messages c 
            JOIN users u ON c.user_id = u.id 
            LEFT JOIN chat_messages p ON c.parent_id = p.id
            LEFT JOIN users pu ON p.user_id = pu.id
            ORDER BY c.created_at DESC 
            LIMIT 100
        ");
        $stmt->execute([$uid]);
        $messages = $stmt->fetchAll();
        // Return messages in chronological order (oldest first for display)
        $messages = array_reverse($messages);

        json_response(200, ['success' => true, 'data' => ['messages' => $messages]]);
        exit;
    } catch (Throwable $e) {
        json_response(500, ['success' => false, 'error' => 'Erreur de récupération']);
        exit;
    }
}
json_response(404, ['success' => false, 'error' => 'Route introuvable']);
