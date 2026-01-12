<?php
require_once '../../includes/db.php';
$stmt = $pdo->query("DESCRIBE productos");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($columns, JSON_PRETTY_PRINT);
?>