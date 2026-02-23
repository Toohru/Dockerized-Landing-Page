<?php

require_once __DIR__ . '/../../config.php';

function getDb(string $role = 'viewer'): PDO
{
    $host = DB_HOST;
    $db   = DB_NAME;

    $credentials = [
        'admin'  => ['user' => DB_ADMIN_USER,  'pass' => DB_ADMIN_PASS],
        'viewer' => ['user' => DB_VIEWER_USER, 'pass' => DB_VIEWER_PASS],
    ];

    $cred = $credentials[$role] ?? $credentials['viewer'];

    return new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $cred['user'],
        $cred['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}
