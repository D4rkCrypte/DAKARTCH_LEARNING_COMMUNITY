<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/db.php';

try {
    $db = db();
    $stmt = $db->query('SELECT id, username, email, role, created_at FROM users ORDER BY id ASC');
    $users = $stmt->fetchAll();
    
    echo "<h1>Liste des utilisateurs inscrits</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Rôle</th><th>Date d'inscription</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars((string)$user['id']) . "</td>";
        echo "<td><b>" . htmlspecialchars((string)$user['username']) . "</b></td>";
        echo "<td>" . htmlspecialchars((string)$user['email']) . "</td>";
        echo "<td><span style='color:" . ($user['role'] === 'ADMIN' ? 'red' : 'green') . ";'>" . htmlspecialchars((string)$user['role']) . "</span></td>";
        echo "<td>" . htmlspecialchars((string)$user['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (empty($users)) {
        echo "<p>Aucun utilisateur n'a été trouvé dans la base de données. Le script setup_test_users.php n'a peut-être pas fonctionné ou vous regardez la mauvaise base de données.</p>";
    }
    
} catch (Exception $e) {
    echo "<h1>Erreur Base de données</h1>";
    echo "<p>Impossible de se connecter ou de lire la table `users`.</p>";
    echo "<pre>Détails de l'erreur : " . htmlspecialchars($e->getMessage()) . "</pre>";
}
