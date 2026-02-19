<?php

function getDb(string $role = 'viewer'): PDO
{
    $host = 'db';
    $db   = 'LandingPageDB';

    $credentials = [
        'admin'  => ['user' => 'Administrator', 'pass' => 'AdministratorPass'],
        'viewer' => ['user' => 'Viewer',        'pass' => 'ViewerPass'],
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