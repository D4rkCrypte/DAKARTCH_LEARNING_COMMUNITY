<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'dakartech_hack',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'token_ttl_seconds' => 60 * 60 * 24 * 30,
        'admin_password' => 'admin123',
    ],
];
