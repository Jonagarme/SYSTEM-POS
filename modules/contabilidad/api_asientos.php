<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

$action = $_GET['action'] ?? '';
$usuario_id = $_SESSION['user_id'] ?? 1;

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT * FROM contabilidad_asientocontable ORDER BY fecha DESC, numero DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                throw new Exception("No se recibieron datos");

            $pdo->beginTransaction();

            $total_debe = 0;
            $total_haber = 0;
            foreach ($data['detalles'] as $d) {
                $total_debe += (float) $d['debe'];
                $total_haber += (float) $d['haber'];
            }

            if (abs($total_debe - $total_haber) > 0.001) {
                // throw new Exception("El asiento no está cuadrado (Debe: $total_debe, Haber: $total_haber)");
                $cuadrado = 0;
            } else {
                $cuadrado = 1;
            }

            $id = $data['id'] ?? null;
            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE contabilidad_asientocontable SET fecha = ?, tipo = ?, concepto = ?, referencia = ?, total_debe = ?, total_haber = ?, cuadrado = ? WHERE id = ?");
                $stmt->execute([
                    $data['fecha'],
                    $data['tipo'],
                    $data['concepto'],
                    $data['referencia'],
                    $total_debe,
                    $total_haber,
                    $cuadrado,
                    $id
                ]);

                // Clear old details
                $pdo->prepare("DELETE FROM contabilidad_movimientocontable WHERE asiento_id = ?")->execute([$id]);
            } else {
                // Insert
                $numero = 'AS-' . time();
                $stmt = $pdo->prepare("INSERT INTO contabilidad_asientocontable (numero, fecha, tipo, concepto, referencia, total_debe, total_haber, cuadrado, fecha_creacion, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(6), ?)");
                $stmt->execute([
                    $numero,
                    $data['fecha'],
                    $data['tipo'],
                    $data['concepto'],
                    $data['referencia'],
                    $total_debe,
                    $total_haber,
                    $cuadrado,
                    $usuario_id
                ]);
                $id = $pdo->lastInsertId();
            }

            // Insert details
            $stmtDet = $pdo->prepare("INSERT INTO contabilidad_movimientocontable (debe, haber, concepto, asiento_id, cuenta_id) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['detalles'] as $d) {
                $stmtDet->execute([
                    (float) $d['debe'],
                    (float) $d['haber'],
                    $d['concepto'] ?? $data['concepto'],
                    $id,
                    $d['cuenta_id']
                ]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Asiento guardado correctamente', 'id' => $id]);
            break;

        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id)
                throw new Exception("ID no proporcionado");

            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM contabilidad_movimientocontable WHERE asiento_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM contabilidad_asientocontable WHERE id = ?")->execute([$id]);
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Asiento eliminado correctamente']);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id)
                throw new Exception("ID no proporcionado");

            $stmt = $pdo->prepare("SELECT * FROM contabilidad_asientocontable WHERE id = ?");
            $stmt->execute([$id]);
            $asiento = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmtDet = $pdo->prepare("SELECT d.*, c.codigo, c.nombre as cuenta_nombre 
                                      FROM contabilidad_movimientocontable d 
                                      JOIN contabilidad_cuentacontable c ON d.cuenta_id = c.id 
                                      WHERE d.asiento_id = ?");
            $stmtDet->execute([$id]);
            $asiento['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $asiento]);
            break;

        case 'search_accounts':
            $q = $_GET['q'] ?? '';
            $stmt = $pdo->prepare("SELECT id, codigo, nombre FROM contabilidad_cuentacontable 
                                   WHERE codigo LIKE ? OR nombre LIKE ? LIMIT 15");
            $stmt->execute(["%$q%", "%$q%"]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        default:
            throw new Exception("Acción no válida");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
