<?php
$c = new mysqli('localhost', 'root', '', 'dakartech_hack');
if ($c->connect_error) die('Connection failed');

$updates = [
    ['D4rkCrypt3', 'Lead CTF Warrior', 'Pwn, Reverse', 'Expert en exploitation binaire et reverse engineering.'],
    ['Dios', 'Web Security Specialist', 'Web, OSINT', 'Passionné par la sécurité des applications web.'],
    ['Prince', 'Instructeur Senior', 'Pentest, Réseau', 'Formateur chevronné spécialisé en réseaux.'],
    ['Divine', 'Crypto & Forensics', 'Cryptographie, Forensics', 'Analyse forensique et cryptographie avancée.']
];

foreach ($updates as $u) {
    $stmt = $c->prepare("UPDATE team_members SET role = ?, specialties = ?, bio = ? WHERE pseudo = ?");
    $stmt->bind_param("ssss", $u[1], $u[2], $u[3], $u[0]);
    $stmt->execute();
}

$c->close();
echo "Data updated successfully";
