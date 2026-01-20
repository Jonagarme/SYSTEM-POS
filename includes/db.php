<?php
/**
 * Database Connection configuration
 */
// Detectar si estamos en local (localhost, 127.0.0.1 o IPs locales)
$isLocal = ($_SERVER['HTTP_HOST'] == 'localhost' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ||
    $_SERVER['REMOTE_ADDR'] == '::1' ||
    preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|127\.)/', $_SERVER['REMOTE_ADDR']) ||
    strpos($_SERVER['HTTP_HOST'], '192.168.') !== false);

if ($isLocal) {
    // Configuración Local (Tu PC)
    $host = 'localhost';
    $db = 'SistemaPosDB';
    $user = 'root';
    $pass = '0801';
} else {
    // Configuración InfinityFree (Hosting)
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
    // Set timezone to Ecuador
    date_default_timezone_set('America/Guayaquil');
    $pdo->exec("SET time_zone = '-05:00'");
} catch (\PDOException $e) {
    // If you haven't created the database yet, this will fail.
    // We'll handle this in the setup script.
    die("Error connecting to database: " . $e->getMessage());
}
