<?php
/**
 * Database Connection configuration
 */
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // Configuración Local
    $host = 'localhost';
    $db = 'logipharmbd';
    $user = 'root';
    $pass = '0801';
} else {
    // Configuración InfinityFree
    $host = 'sql113.infinityfree.com';
    $db = 'if0_40888759_logipharmdb';
    $user = 'if0_40888759';
    $pass = '0801jona';
}
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If you haven't created the database yet, this will fail.
    // We'll handle this in the setup script.
    die("Error connecting to database: " . $e->getMessage());
}
