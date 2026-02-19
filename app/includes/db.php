<?php

$host = 'db';
$db   = 'LandingPageDB';
$AdminUser = 'Administrator';
$AdminPass = 'AdministratorPass';
$ViewerUser = "Viewer";
$ViewerPass = "ViewerPass";

$pdo = new PDO(
    "mysql:host=$host;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
