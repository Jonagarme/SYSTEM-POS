<?php
/**
 * Database Connection configuration
 */
$host = 'localhost';
$db = 'logipharmbd';
$user = 'root';
$pass = '0801';
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
