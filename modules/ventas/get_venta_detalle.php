<?php
ob_start();
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../includes/db.php';

try {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        throw new Exception('ID de venta no proporcionado');
    }

    // Fetch main invoice info
    $stmt = $pdo->prepare("
        SELECT f.*, 
               CONCAT(c.nombres, ' ', c.apellidos) as cliente_nombre,
               c.cedula_ruc as cliente_ruc,
               c.direccion as cliente_direccion,
               c.celular as cliente_telefono,
               u.nombreUsuario as vendedor_nombre
        FROM facturas_venta f
        LEFT JOIN clientes c ON f.idCliente = c.id
        LEFT JOIN usuarios u ON f.idUsuario = u.id
        WHERE f.id = ?
    ");
    $stmt->execute([$id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception('Venta no encontrada en la base de datos');
    }

    // Fetch details
    $stmtDet = $pdo->prepare("SELECT * FROM facturas_venta_detalle WHERE idFacturaVenta = ?");
    $stmtDet->execute([$id]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode([
        'success' => true,
        'venta' => $venta,
        'detalles' => $detalles
    ], JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
