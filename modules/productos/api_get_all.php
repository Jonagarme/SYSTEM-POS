<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

try {
    $stmt = $pdo->prepare("SELECT id, codigoPrincipal, nombre, precioVenta, stock FROM productos WHERE anulado = 0");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($productos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>