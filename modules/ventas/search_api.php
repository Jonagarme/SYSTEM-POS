<?php
require_once '../../includes/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$query = $_GET['q'] ?? '';

try {
    if ($action === 'search_products') {
        $stmt = $pdo->prepare("
            SELECT p.*, l.nombre as lab 
            FROM productos p 
            LEFT JOIN laboratorios l ON p.idLaboratorio = l.id 
            WHERE p.anulado = 0 
            AND (p.nombre LIKE ? OR p.codigoPrincipal LIKE ?)
            LIMIT 20
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        echo json_encode($stmt->fetchAll());
    } elseif ($action === 'search_clients') {
        $stmt = $pdo->prepare("
            SELECT id, nombres, apellidos, cedula_ruc, direccion 
            FROM clientes 
            WHERE (nombres LIKE ? OR apellidos LIKE ? OR cedula_ruc LIKE ?)
            LIMIT 10
        ");
        $stmt->execute(["%$query%", "%$query%", "%$query%"]);
        echo json_encode($stmt->fetchAll());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
