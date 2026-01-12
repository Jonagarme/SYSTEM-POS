<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

try {
    $stmt = $pdo->prepare("SELECT id, cedula_ruc, nombres, apellidos, direccion, telefono FROM clientes WHERE anulado = 0");
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($clientes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>