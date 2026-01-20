<?php
require_once '../../includes/db.php';
header('Content-Type: application/json');

$seccion_id = $_GET['id'] ?? null;

if (!$seccion_id) {
    echo json_encode(['error' => 'ID de sección no proporcionado']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.nombre, 
            p.codigoPrincipal as codigo, 
            p.stock,
            pp.nombre as percha_nombre,
            pu.fila,
            pu.columna
        FROM productos_ubicacionproducto pu
        JOIN productos p ON pu.producto_id = p.id
        JOIN productos_percha pp ON pu.percha_id = pp.id
        WHERE pp.seccion_id = ? AND p.anulado = 0
        ORDER BY pp.nombre, pu.fila, pu.columna
    ");
    $stmt->execute([$seccion_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
