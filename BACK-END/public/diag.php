<?php
$c = new mysqli('localhost', 'root', '', 'dakartech_hack');
if ($c->connect_error) die('Connection failed');
$r = $c->query('SELECT pseudo, team, role, specialties, bio FROM team_members');
while ($row = $r->fetch_assoc()) {
    echo "Pseudo: " . $row['pseudo'] . " | Team: " . $row['team'] . " | Role: " . $row['role'] . " | Specs: " . $row['specialties'] . "\n";
}
$c->close();
