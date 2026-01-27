<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

$action = $_GET['action'] ?? '';
$usuario_id = $_SESSION['user_id'] ?? 1;

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT cb.*, cc.nombre as cuenta_contable_nombre, cc.codigo as cuenta_contable_codigo 
                                 FROM contabilidad_cuentabancaria cb
                                 LEFT JOIN contabilidad_cuentacontable cc ON cb.cuenta_contable_id = cc.id
                                 ORDER BY cb.nombre ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                throw new Exception("No se recibieron datos");

            if (isset($data['id']) && !empty($data['id'])) {
                // Update
                $sql = "UPDATE contabilidad_cuentabancaria SET 
                        nombre = ?, banco = ?, numero_cuenta = ?, tipo = ?, 
                        saldo_inicial = ?, fecha_apertura = ?, activa = ?, cuenta_contable_id = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['nombre'],
                    $data['banco'],
                    $data['numero_cuenta'],
                    $data['tipo'],
                    $data['saldo_inicial'],
                    $data['fecha_apertura'],
                    isset($data['activa']) ? (int) $data['activa'] : 1,
                    !empty($data['cuenta_contable_id']) ? $data['cuenta_contable_id'] : null,
                    $data['id']
                ]);
                $message = 'Cuenta bancaria actualizada correctamente';
            } else {
                // Insert
                $sql = "INSERT INTO contabilidad_cuentabancaria 
                        (nombre, banco, numero_cuenta, tipo, saldo_inicial, fecha_apertura, activa, cuenta_contable_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['nombre'],
                    $data['banco'],
                    $data['numero_cuenta'],
                    $data['tipo'],
                    $data['saldo_inicial'],
                    $data['fecha_apertura'],
                    isset($data['activa']) ? (int) $data['activa'] : 1,
                    !empty($data['cuenta_contable_id']) ? $data['cuenta_contable_id'] : null
                ]);
                $message = 'Cuenta bancaria creada correctamente';
            }

            echo json_encode(['success' => true, 'message' => $message]);
            break;

        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id)
                throw new Exception("ID no proporcionado");

            // Check if there are movements associated
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM contabilidad_movimientobancario WHERE cuenta_bancaria_id = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                // Instead of delete, maybe deactivate?
                $stmt = $pdo->prepare("UPDATE contabilidad_cuentabancaria SET activa = 0 WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Cuenta desactivada porque tiene movimientos asociados']);
            } else {
                $stmt = $pdo->prepare("DELETE FROM contabilidad_cuentabancaria WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Cuenta eliminada correctamente']);
            }
            break;

        case 'search_cuenta_contable':
            $q = $_GET['q'] ?? '';
            $stmt = $pdo->prepare("SELECT id, codigo, nombre FROM contabilidad_cuentacontable 
                                   WHERE nombre LIKE ? OR codigo LIKE ? LIMIT 10");
            $stmt->execute(["%$q%", "%$q%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $results]);
            break;

        default:
            throw new Exception("Acción no válida");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
