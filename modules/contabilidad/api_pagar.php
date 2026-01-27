<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

$action = $_GET['action'] ?? '';
$usuario_id = $_SESSION['user_id'] ?? 1;

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT c.*, p.razonSocial as proveedor_nombre, p.ruc
                                 FROM contabilidad_cuentaporpagar c
                                 LEFT JOIN proveedores p ON c.proveedor_id = p.id
                                 ORDER BY c.fecha_vencimiento ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular totales
            $totales = [
                'total_por_pagar' => 0,
                'pendiente' => 0,
                'vencido' => 0,
                'pagado_mes' => 0
            ];

            $now = date('Y-m-d');
            $thisMonth = date('Y-m');
            foreach ($data as &$item) {
                $totales['total_por_pagar'] += $item['monto_original'];
                $totales['pendiente'] += $item['monto_pendiente'];

                if ($item['estado'] !== 'PAGADA' && $item['fecha_vencimiento'] < $now) {
                    $totales['vencido'] += $item['monto_pendiente'];
                }

                // Aquí necesitaríamos consultar los pagos del mes para 'pagado_mes'
            }

            // Consultar total pagado este mes
            $stmtM = $pdo->prepare("SELECT SUM(monto) FROM contabilidad_pagocuentaporpagar 
                                   WHERE DATE_FORMAT(fecha_pago, '%Y-%m') = ?");
            $stmtM->execute([$thisMonth]);
            $totales['pagado_mes'] = (float) $stmtM->fetchColumn();

            echo json_encode(['success' => true, 'data' => $data, 'totales' => $totales]);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                throw new Exception("No se recibieron datos");

            $num = 'CP-' . time();
            $emision = $data['fecha'] ?? date('Y-m-d');
            $dias = (int) ($data['dias_credito'] ?? 30);
            $vencimiento = date('Y-m-d', strtotime($emision . " + $dias days"));

            $stmt = $pdo->prepare("INSERT INTO contabilidad_cuentaporpagar 
                (numero, factura_proveedor, fecha_emision, fecha_vencimiento, monto_original, monto_pendiente, estado, categoria_gasto, observaciones, fecha_creacion, proveedor_id, usuario_creacion_id)
                VALUES (?, ?, ?, ?, ?, ?, 'PENDIENTE', ?, ?, NOW(6), ?, ?)");

            $stmt->execute([
                $num,
                $data['factura'],
                $emision,
                $vencimiento,
                $data['total'],
                $data['total'],
                $data['tipo_compra'] ?? 'Mercadería',
                $data['observaciones'] ?? '',
                $data['proveedor_id'],
                $usuario_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Factura registrada correctamente']);
            break;

        case 'save_schedule':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || empty($data['cuenta_id']))
                throw new Exception("Datos incompletos para la programación");

            $pdo->beginTransaction();

            $cuenta_id = $data['cuenta_id'];
            $num_cuotas = (int) $data['num_cuotas'];
            $frecuencia = $data['frecuencia'];
            $fecha_inicio = $data['fecha_inicio'];
            $saldo = (float) $data['saldo'];
            $monto_cuota = round($saldo / $num_cuotas, 2);

            // 1. Eliminar cronograma previo si existe para esta cuenta (opcional, depende de la lógica deseada)
            $stmtDel = $pdo->prepare("DELETE FROM contabilidad_cronogramapago WHERE cuenta_pagar_id = ? AND estado = 'PENDIENTE'");
            $stmtDel->execute([$cuenta_id]);

            // 2. Insertar las cuotas
            $stmtIns = $pdo->prepare("INSERT INTO contabilidad_cronogramapago (cuenta_pagar_id, cuota_numero, fecha_programada, monto, estado) VALUES (?, ?, ?, ?, 'PENDIENTE')");

            $dias_incremento = 30;
            if ($frecuencia === 'semanal')
                $dias_incremento = 7;
            if ($frecuencia === 'quincenal')
                $dias_incremento = 15;

            for ($i = 0; $i < $num_cuotas; $i++) {
                $fecha = date('Y-m-d', strtotime($fecha_inicio . " + " . ($i * $dias_incremento) . " days"));
                // Ajuste para la última cuota por redondeos
                if ($i === $num_cuotas - 1) {
                    $monto_acumulado = $monto_cuota * ($num_cuotas - 1);
                    $monto_cuota = $saldo - $monto_acumulado;
                }
                $stmtIns->execute([$cuenta_id, $i + 1, $fecha, $monto_cuota]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Cronograma de pagos guardado correctamente']);
            break;

        case 'get_schedule':
            $cuenta_id = $_GET['cuenta_id'] ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM contabilidad_cronogramapago WHERE cuenta_pagar_id = ? ORDER BY cuota_numero ASC");
            $stmt->execute([$cuenta_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'pay_quota':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || empty($data['id']))
                throw new Exception("ID de cuota no proporcionado");

            $pdo->beginTransaction();

            // 1. Obtener datos de la cuota y la factura
            $stmtQ = $pdo->prepare("SELECT cq.*, cp.id as cuenta_id FROM contabilidad_cronogramapago cq 
                                   JOIN contabilidad_cuentaporpagar cp ON cq.cuenta_pagar_id = cp.id 
                                   WHERE cq.id = ?");
            $stmtQ->execute([$data['id']]);
            $cuota = $stmtQ->fetch();

            if (!$cuota)
                throw new Exception("Cuota no encontrada");
            if ($cuota['estado'] === 'PAGADO')
                throw new Exception("Esta cuota ya ha sido pagada");

            // 2. Marcar cuota como PAGADO
            $stmtUpdQ = $pdo->prepare("UPDATE contabilidad_cronogramapago SET estado = 'PAGADO' WHERE id = ?");
            $stmtUpdQ->execute([$data['id']]);

            // 3. Registrar el pago en el historial general
            $stmtPay = $pdo->prepare("INSERT INTO contabilidad_pagocuentaporpagar 
                (fecha_pago, monto, forma_pago, referencia, observaciones, fecha_creacion, cuenta_pagar_id, usuario_id)
                VALUES (NOW(), ?, 'PROGRAMADO', ?, ?, NOW(6), ?, ?)");

            $stmtPay->execute([
                $cuota['monto'],
                "Pago cuota #" . $cuota['cuota_numero'],
                "Pago automático desde cronograma",
                $cuota['cuenta_id'],
                $usuario_id
            ]);

            // 4. Actualizar saldo de la factura principal
            $stmtUpdC = $pdo->prepare("UPDATE contabilidad_cuentaporpagar 
                                      SET monto_pendiente = monto_pendiente - ?,
                                          estado = CASE WHEN (monto_pendiente - ?) <= 0 THEN 'PAGADA' ELSE 'PARCIAL' END
                                      WHERE id = ?");
            $stmtUpdC->execute([$cuota['monto'], $cuota['monto'], $cuota['cuenta_id']]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Cuota pagada correctamente']);
            break;

        case 'pay':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                throw new Exception("No se recibieron datos");

            $pdo->beginTransaction();

            // 1. Registrar el pago
            $stmtPay = $pdo->prepare("INSERT INTO contabilidad_pagocuentaporpagar 
                (fecha_pago, monto, forma_pago, referencia, observaciones, fecha_creacion, cuenta_pagar_id, usuario_id)
                VALUES (?, ?, ?, ?, ?, NOW(6), ?, ?)");

            $stmtPay->execute([
                $data['fecha_pago'] ?? date('Y-m-d'),
                $data['monto'],
                $data['metodo_pago'] ?? 'efectivo',
                $data['referencia'] ?? '',
                $data['notas'] ?? '',
                $data['cuenta_id'],
                $usuario_id
            ]);

            // 2. Actualizar saldo de la cuenta
            $stmtUpd = $pdo->prepare("UPDATE contabilidad_cuentaporpagar 
                                      SET monto_pendiente = monto_pendiente - ?,
                                          estado = CASE WHEN (monto_pendiente - ?) <= 0 THEN 'PAGADA' ELSE 'PARCIAL' END
                                      WHERE id = ?");
            $stmtUpd->execute([$data['monto'], $data['monto'], $data['cuenta_id']]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Pago emitido correctamente']);
            break;

        default:
            throw new Exception("Acción no válida");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
