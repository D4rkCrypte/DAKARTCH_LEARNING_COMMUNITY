<?php
require_once __DIR__ . '/../src/db.php';
$stmt = db()->query("SHOW CREATE TABLE users");
$row = $stmt->fetch();
echo $row['Create Table'] . "\n";
