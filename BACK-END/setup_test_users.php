<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/db.php';

try {
    $db = db();
    
    $usersToAdd = [
        // Admins
        ['pseudo' => 'AdminTest', 'email' => 'admin@dakartech.hack', 'password' => 'AdminPassword123!', 'role' => 'ADMIN'],
        ['pseudo' => 'AdminSupport', 'email' => 'support@dakartech.hack', 'password' => 'SupportAdmin456!', 'role' => 'ADMIN'],
        ['pseudo' => 'AdminChief', 'email' => 'chief@dakartech.hack', 'password' => 'ChiefAdmin789!', 'role' => 'ADMIN'],
        // Members
        ['pseudo' => 'MemberTest', 'email' => 'member@dakartech.hack', 'password' => 'MemberPassword123!', 'role' => 'MEMBER'],
        ['pseudo' => 'HackerNewbie', 'email' => 'newbie@dakartech.hack', 'password' => 'NewbieHack1!', 'role' => 'MEMBER'],
        ['pseudo' => 'CyberNinja', 'email' => 'ninja@dakartech.hack', 'password' => 'NinjaCyber2!', 'role' => 'MEMBER'],
        ['pseudo' => 'ProPlayer', 'email' => 'pro@dakartech.hack', 'password' => 'PlayerPro3!', 'role' => 'MEMBER'],
        ['pseudo' => 'D4rkCrypt3', 'email' => 'superadmin@dakartech.hack', 'password' => '!@dk615536!@DKMD', 'role' => 'SUPERADMIN']
    ];

    $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE password_hash = ?, role = ?');

    foreach ($usersToAdd as $u) {
        $hash = password_hash($u['password'], PASSWORD_DEFAULT);
        $stmt->execute([$u['pseudo'], $u['email'], $hash, $u['role'], $hash, $u['role']]);
        echo "{$u['role']} user ({$u['pseudo']}) has been added / updated.\n";
    }

    echo "\nInsertion completed successfully! You now have 3 Admins and 4 Members total.\n";
    
} catch (Exception $e) {
    echo "Error inserting users: " . $e->getMessage() . "\n";
}
