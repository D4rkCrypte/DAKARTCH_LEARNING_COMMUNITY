<?php
declare(strict_types=1);

require_once __DIR__ . '/src/db.php';

try {
    $pdo = db();
    
    // 1. DDL : Ajouter la colonne file_url si elle n'existe pas
    try {
        $pdo->exec("ALTER TABLE ctf_challenges ADD COLUMN file_url VARCHAR(500) NULL AFTER description;");
        echo "Colonne 'file_url' ajoutee avec succes.\n";
    } catch (Exception $e) {
        // Ignorer si la colonne existe déjà (erreur SQLSTATE 42S21)
        if ($e->getCode() !== '42S21') {
            echo "Avertissement (file_url) : " . $e->getMessage() . "\n";
        }
    }

    // 2. Clear old ctfs to ensure we insert fresh demo data
    $pdo->exec("DELETE FROM ctf_challenges");

    // 3. Examples of CTFs
    $ctfs = [
        ['Le Secret de César', 'Un classique de la Rome Antique. Déchiffrez ce message : "IYM{HWFET_JXY_QF}" (ROT5).', 'crypto', 100, 'DTK{CEZAR_EST_LA}', null],
        ['L\'Énigme Base64', 'Un message encodé a été intercepté : "RFRLe0JBU0U2NF9JU19FQVNZfQ==". Retrouvez le flag.', 'crypto', 150, 'DTK{BASE64_IS_EASY}', null],
        ['RSA Master', 'La sécurité du web repose sur RSA. Trouvez le flag caché derrière cette logique de factorisation.', 'crypto', 250, 'DTK{RSA_MASTER_2024}', null]
    ];

    $stmt = $pdo->prepare('INSERT INTO ctf_challenges (title, description, category, points, flag, file_url) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($ctfs as $ctf) {
        $stmt->execute($ctf);
    }
    
    echo "3 CTFs exemples ajoutes avec succes.\n";
} catch (Exception $e) {
    echo "Erreur fatale: " . $e->getMessage() . "\n";
}
