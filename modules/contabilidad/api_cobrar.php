<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

$action = $_GET['action'] ?? '';
$usuario_id = $_SESSION['user_id'] ?? 1;

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT c.*, cl.nombres, cl.apellidos, cl.cedula_ruc as identificacion,
                                 CONCAT(cl.nombres, ' ', cl.apellidos) as cliente_nombre
                                 FROM contabilidad_cuentaporcobrar c
                                 JOIN clientes cl ON c.cliente_id = cl.id
                                 ORDER BY c.fecha_vencimiento ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular totales
            $totales = [
                'total_cartera' => 0,
                'por_cobrar' => 0,
                'vencido' => 0,
                'cobrado' => 0
            ];

            $now = date('Y-m-d');
            foreach ($data as &$item) {
                $totales['total_cartera'] += $item['monto_original'];
                $totales['por_cobrar'] += $item['monto_pendiente'];
                $totales['cobrado'] += ($item['monto_original'] - $item['monto_pendiente']);

                if ($item['estado'] !== 'PAGADA' && $item['fecha_vencimiento'] < $now) {
                    $totales['vencido'] += $item['monto_pendiente'];
                }
            }

            echo json_encode(['success' => true, 'data' => $data, 'totales' => $totales]);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                throw new Exception("No se recibieron datos");

            $num = $data['factura'] ?? 'CC-' . time();
            $emision = $data['fecha'] ?? date('Y-m-d');
            $dias = (int) ($data['dias_credito'] ?? 30);
            $vencimiento = date('Y-m-d', strtotime($emision . " + $dias days"));

            $stmt = $pdo->prepare("INSERT INTO contabilidad_cuentaporcobrar 
                (numero, fecha_emision, fecha_vencimiento, monto_original, monto_pendiente, estado, observaciones, fecha_creacion, cliente_id, usuario_creacion_id)
                VALUES (?, ?, ?, ?, ?, 'PENDIENTE', ?, NOW(6), ?, ?)");

            $stmt->execute([
                $num,
                $emision,
                $vencimiento,
                $data['total'],
                $data['total'],
                $data['observaciones'] ?? '',
                $data['cliente_id'],
                $usuario_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Cuenta creada correctamente']);
            break;

        case 'pay':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                throw new Exception("No se recibieron datos");

            $pdo->beginTransaction();

            // 1. Registrar el pago
            $stmtPay = $pdo->prepare("INSERT INTO contabilidad_pagocuentaporcobrar 
                (fecha_pago, monto, forma_pago, referencia, observaciones, fecha_creacion, cuenta_cobrar_id, usuario_id)
                VALUES (?, ?, ?, ?, ?, NOW(6), ?, ?)");

            $stmtPay->execute([
                $data['fecha_pago'],
                $data['monto'],
                $data['metodo_pago'],
                $data['referencia'] ?? '',
                $data['notas'] ?? '',
                $data['cuenta_id'],
                $usuario_id
            ]);

            // 2. Actualizar saldo de la cuenta
            $stmtUpd = $pdo->prepare("UPDATE contabilidad_cuentaporcobrar 
                                      SET monto_pendiente = monto_pendiente - ?,
                                          estado = CASE WHEN (monto_pendiente - ?) <= 0 THEN 'PAGADA' ELSE 'PARCIAL' END
                                      WHERE id = ?");
            $stmtUpd->execute([$data['monto'], $data['monto'], $data['cuenta_id']]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
            break;

        default:
            throw new Exception("Acción no válida");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
