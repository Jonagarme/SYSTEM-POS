<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

try {
    $numero = $_GET['numero'] ?? '';

    if (empty($numero)) {
        throw new Exception('Número de factura no proporcionado');
    }

    // Fetch main invoice info
    $stmt = $pdo->prepare("
        SELECT f.*, 
               CONCAT(c.nombres, ' ', c.apellidos) as cliente_nombre,
               c.cedula_ruc as cliente_ruc,
               c.direccion as cliente_direccion,
               c.celular as cliente_telefono
        FROM facturas_venta f
        LEFT JOIN clientes c ON f.idCliente = c.id
        WHERE f.numeroFactura = ?
    ");
    $stmt->execute([$numero]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception('Factura no encontrada en la base de datos');
    }

    // Fetch details
    $stmtDet = $pdo->prepare("
        SELECT vd.*, p.codigoPrincipal as codigo
        FROM facturas_venta_detalle vd
        LEFT JOIN productos p ON vd.idProducto = p.id
        WHERE vd.idFacturaVenta = ?
    ");
    $stmtDet->execute([$venta['id']]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'venta' => $venta,
        'detalles' => $detalles
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
