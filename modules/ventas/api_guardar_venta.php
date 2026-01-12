<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/logifact_api.php';
session_start();

// Obtener datos del POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos válidos']);
    exit;
}

try {
    $pdo->beginTransaction();

    $venta = $data['data'];
    $usuario_id = $_SESSION['user_id'] ?? 1; // ID por defecto si no hay sesión

    // 1. Insertar en facturas_venta
    // Nota: Usamos los nombres de columna de tu tabla real detectados en el backup
    $stmt = $pdo->prepare("INSERT INTO facturas_venta 
        (idCliente, idUsuario, numeroFactura, fechaEmision, subtotal, descuento, iva, total, estado, creadoPor, creadoDate) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'PAGADA', ?, NOW())");

    // Generar un número de factura aleatorio para el ejemplo
    $numFactura = "FAC-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -6));

    $stmt->execute([
        $data['cliente_id'] ?? 275, // Consumidor final por defecto
        $usuario_id,
        $numFactura,
        $venta['totalSinImpuestos'],
        $venta['totalDescuento'],
        $venta['impuestos'][0]['valor'],
        $venta['importeTotal'],
        $usuario_id
    ]);

    $idVenta = $pdo->lastInsertId();

    // 2. Insertar detalles
    $stmtDet = $pdo->prepare("INSERT INTO facturas_venta_detalle 
        (idFacturaVenta, idProducto, cantidad, precioUnitario, descuentoValor, ivaValor, total, productoNombre) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($venta['detalles'] as $det) {
        // Buscar el ID real del producto por su código principal
        $stmtP = $pdo->prepare("SELECT id FROM productos WHERE codigoPrincipal = ? LIMIT 1");
        $stmtP->execute([$det['codigoPrincipal']]);
        $prod = $stmtP->fetch();
        $idProducto = $prod ? $prod['id'] : 0;

        $stmtDet->execute([
            $idVenta,
            $idProducto,
            $det['cantidad'],
            $det['precioUnitario'],
            $det['descuento'],
            $det['impuestos'][0]['valor'],
            $det['precioTotalSinImpuesto'] + $det['impuestos'][0]['valor'],
            $det['description']
        ]);

        // 3. Descontar Stock
        if ($idProducto > 0) {
            $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?")
                ->execute([$det['cantidad'], $idProducto]);
        }
    }

    $pdo->commit();

    // 4. Forward to Logifact API
    $token = LogifactAPI::login();
    $external_res = null;
    if ($token) {
        $external_res = LogifactAPI::sendInvoice($venta, $token);
    }

    echo json_encode([
        'success' => true,
        'id' => $idVenta,
        'numero' => $numFactura,
        'external' => $external_res
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
