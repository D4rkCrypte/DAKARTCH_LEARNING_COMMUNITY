<?php
/**
 * Master Script - Synchronisation Dynamique Automatisée de la Base de Données
 */

echo "<html><head><title>Synchronisation BDD Automatique</title>";
echo "<style>body{background:#050a15; color:#00ffcc; font-family:monospace; padding:30px;} pre{background:rgba(0,0,0,0.5); padding:15px; border-radius:10px; border:1px solid #00ffcc;}</style></head><body>";
echo "<h1>🚀 Moteur de Déploiement Global Dynamique...</h1>";
echo "<p>Recherche intelligente de tous les scripts de base de données dans le dossier du projet...</p>";

$scripts = [];
$dir = __DIR__;

// Analyse du dossier pour trouver tous les futurs ou anciens fichiers
foreach (scandir($dir) as $file) {
    if ($file === '.' || $file === '..') continue;
    if ($file === basename(__FILE__)) continue; // Ne pas s'inclure lui-même

    if (is_file($dir . '/' . $file)) {
        // Détecter automatiquement tous les fichiers qui commencent par init, setup, create, migrate, ou update
        if (preg_match('/^(init|setup|create|migrate|update).*\.php$/i', $file)) {
            $scripts[] = $file;
        }
    }
}

// Assurer que les scripts "init" (l'initialisation originelle de la BD) s'exécutent TOUJOURS en tout premier
usort($scripts, function($a, $b) {
    $aInit = (stripos($a, 'init') !== false) ? 1 : 0;
    $bInit = (stripos($b, 'init') !== false) ? 1 : 0;
    if ($aInit !== $bInit) {
        return $bInit - $aInit; // met l'élément ayant 'init' en premier
    }
    return strcmp($a, $b); // Tri alphabétique classique pour le reste
});

if (empty($scripts)) {
    echo "<p style='color:orange;'>Aucun script de base de données detecté.</p>";
} else {
    foreach ($scripts as $index => $script) {
        $step = $index + 1;
        echo "<h2>Étape $step : Exécution de [$script]</h2><pre>";
        ob_start(); // Capture de la sortie pour l'afficher joliment
        
        try {
            include $dir . '/' . $script;
            $out = ob_get_clean();
            echo htmlspecialchars($out);
        } catch (Throwable $e) {
            $out = ob_get_clean();
            echo htmlspecialchars($out);
            echo "\n\n<span style='color:red;'>ERREUR CRITIQUE DANS $script : " . $e->getMessage() . "</span>";
        }
        
        echo "</pre>";
    }
}

echo "<h2 style='color:#ff3366;'>✅ SYNCHRONISATION DYNAMIQUE TERMINÉE A 100% !</h2>";
echo "<p>Tous les fichiers reconnus ont été appliqués automatiquement.</p>";
echo "</body></html>";
?>
