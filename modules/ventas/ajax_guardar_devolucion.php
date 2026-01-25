<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('No se recibieron datos válidos');
    }

    $ventaId = $data['venta_id'];
    $motivo = $data['motivo'];
    $observaciones = $data['observaciones'];
    $items = $data['items'];
    $usuarioId = $_SESSION['user_id'] ?? 1;

    $pdo->beginTransaction();

    // 1. Generate Return Number (e.g., DEV-20250125-001)
    $today = date('Ymd');
    $stmtNum = $pdo->prepare("SELECT COUNT(*) FROM ventas_devolucionventa WHERE numero_devolucion LIKE ?");
    $stmtNum->execute(["DEV-$today-%"]);
    $count = $stmtNum->fetchColumn();
    $nextNum = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    $numeroDevolucion = "DEV-$today-$nextNum";

    // 2. Insert into ventas_devolucionventa
    $totalDevolucion = 0;
    foreach ($items as $item) {
        $totalDevolucion += $item['cantidad'] * $item['precio'];
    }

    $stmtDev = $pdo->prepare("INSERT INTO ventas_devolucionventa 
        (numero_devolucion, fecha, motivo, observaciones, total_devolucion, usuario_id, venta_original_id) 
        VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    $stmtDev->execute([
        $numeroDevolucion,
        $motivo,
        $observaciones,
        $totalDevolucion,
        $usuarioId,
        $ventaId
    ]);

    $devolucionId = $pdo->lastInsertId();

    // 3. Process Items
    $stmtDet = $pdo->prepare("INSERT INTO ventas_detalledevolucion 
        (cantidad_devuelta, detalle_venta_id, devolucion_id) 
        VALUES (?, ?, ?)");

    foreach ($items as $item) {
        // Insert detail
        $stmtDet->execute([
            $item['cantidad'],
            $item['detalle_id'],
            $devolucionId
        ]);

        // Update Stock
        $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?")
            ->execute([$item['cantidad'], $item['producto_id']]);

        // Get new balance
        $stmtStock = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
        $stmtStock->execute([$item['producto_id']]);
        $saldoActual = $stmtStock->fetchColumn();

        // Kardex Move
        $stmtKardex = $pdo->prepare("INSERT INTO kardex_movimientos 
            (idProducto, tipoMovimiento, ingreso, egreso, saldo, detalle, fecha) 
            VALUES (?, 'DEVOLUCION', ?, 0, ?, ?, NOW())");
        $stmtKardex->execute([
            $item['producto_id'],
            $item['cantidad'],
            $saldoActual,
            "Devolución #$numeroDevolucion - " . $item['nombre']
        ]);
    }

    $pdo->commit();

    // Log Audit
    if (file_exists('../../includes/audit.php')) {
        require_once '../../includes/audit.php';
        registrarAuditoria('Ventas', 'DEVOLUCION', 'ventas_devolucionventa', $devolucionId, "Devolución procesada: $numeroDevolucion (Factura Original ID: $ventaId, Total: $totalDevolucion)");
    }

    echo json_encode(['success' => true, 'id' => $devolucionId, 'numero' => $numeroDevolucion]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
