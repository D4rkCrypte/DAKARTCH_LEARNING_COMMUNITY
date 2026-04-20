<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => getenv('MYSQLHOST') ?: '127.0.0.1',
        'port' => getenv('MYSQLPORT') ?: 3306,
        'name' => getenv('MYSQLDATABASE') ?: 'dakartech_hack',
        'user' => getenv('MYSQLUSER') ?: 'root',
        'pass' => getenv('MYSQLPASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'token_ttl_seconds' => 60 * 60 * 24 * 30,
        'admin_password' => 'admin123',
    ],
];
