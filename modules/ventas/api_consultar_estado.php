<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/logifact_api.php';
session_start();

/**
 * Intenta obtener autorización desde la BD remota (tabla comprobantes).
 * Esto evita depender de la consultaSRI cuando el endpoint está protegido.
 */
function sri_try_remote_comprobantes($numeroFactura, $claveAcceso = null)
{
    static $remotePdo = null;

    $remoteDsn = "mysql:host=sql107.infinityfree.com;dbname=if0_40698217_nexusfact_db;charset=utf8mb4";
    $remoteUser = 'if0_40698217';
    $remotePass = 'jonagarme20';

    try {
        if ($remotePdo === null) {
            $remotePdo = new PDO($remoteDsn, $remoteUser, $remotePass, [
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        // 1) Si ya tenemos claveAcceso, buscar directo
        if (!empty($claveAcceso) && strlen((string) $claveAcceso) >= 40) {
            $stmt = $remotePdo->prepare("SELECT numero_autorizacion, fecha_autorizacion, clave_acceso
                                          FROM comprobantes
                                          WHERE TRIM(clave_acceso) = ?
                                          ORDER BY fecha_autorizacion DESC
                                          LIMIT 1");
            $stmt->execute([trim((string) $claveAcceso)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $row;
            }
        }

        // 2) Buscar por serie + secuencial con tolerancia de formatos
        $parts = explode('-', (string) $numeroFactura);
        if (count($parts) < 3) {
            return null;
        }

        $hasNumericFormat = (bool) preg_match('/^\d{3}$/', $parts[0])
            && (bool) preg_match('/^\d{3}$/', $parts[1])
            && (bool) preg_match('/^\d+$/', $parts[2]);
        if (!$hasNumericFormat) {
            return null;
        }

        $serieHyphen = trim($parts[0] . '-' . $parts[1]);
        $serieSpace = trim($parts[0] . ' ' . $parts[1]);
        $serieConcat = trim($parts[0] . $parts[1]);

        $secuencialFull = trim($parts[2]);
        $secuencial8 = substr($secuencialFull, -8);
        $secuencialShort = ltrim($secuencialFull, '0');
        if ($secuencialShort === '') {
            $secuencialShort = '0';
        }

        $stmt = $remotePdo->prepare("SELECT numero_autorizacion, fecha_autorizacion, clave_acceso
                                      FROM comprobantes
                                      WHERE (TRIM(serie) = ? OR TRIM(serie) = ? OR TRIM(serie) = ?)
                                        AND (TRIM(secuencial) = ? OR TRIM(secuencial) = ? OR TRIM(secuencial) = ?)
                                      ORDER BY fecha_autorizacion DESC
                                      LIMIT 1");
        $stmt->execute([
            $serieHyphen,
            $serieSpace,
            $serieConcat,
            $secuencialFull,
            $secuencial8,
            $secuencialShort
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Exception $e) {
        // Silencioso: si falla remoto, se intenta consultaSRI.
        return null;
    }
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Falta el ID de la factura']);
    exit;
}

try {
    // 1. Obtener datos de la factura
    $stmt = $pdo->prepare("SELECT f.*, e.ruc, e.sri_ambiente as ambiente FROM facturas_venta f JOIN empresas e ON 1=1 WHERE f.id = ?");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$f) {
        throw new Exception("Factura no encontrada");
    }

    // Si ya está autorizada, no consultar más
    if ($f['estadoFactura'] === 'AUTORIZADA') {
        echo json_encode([
            'success' => true,
            'estado' => 'AUTORIZADA',
            'numeroAutorizacion' => $f['numeroAutorizacion']
        ]);
        exit;
    }

    // 2. Generar la clave de acceso si no la tenemos (o usar la que está en numeroAutorizacion si es el caso)
    // El formato de la clave de acceso es: 
    // fecha (8) + tipoDoc (2/01) + RUC (13) + ambiente (1) + serie (6/est+punto) + secuencial (9) + código (8) + tipoEmi (1) + digitoVerificador (1)

    // Lo más seguro es que Logifact ya generó la clave o podemos usar el número de factura para consultar
    // Logifact suele facilitar la consulta por clave de acceso.

    // Vamos a intentar obtener la clave de acceso que guardó Logifact o reconstruirla.
    // Pero si el proceso inicial falló, quizás aún no tenemos la clave guardada localmente.

    // Para simplificar, si numeroAutorizacion tiene 49 dígitos, es la clave de acceso.
    $claveAcceso = $f['numeroAutorizacion'];

    // 2.1 PRIORIDAD: Buscar autorización en la tabla comprobantes (más confiable)
    $remote = sri_try_remote_comprobantes($f['numeroFactura'], $claveAcceso);
    if ($remote) {
        $numAuthRemote = $remote['numero_autorizacion'] ?? null;
        $claveRemote = $remote['clave_acceso'] ?? null;

        // Si ya está autorizado en remoto
        if (!empty($numAuthRemote) && strlen((string) $numAuthRemote) > 10) {
            $set = ["estadoFactura = 'AUTORIZADA'", 'numeroAutorizacion = ?'];
            $params = [$numAuthRemote];
            if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'fechaAutorizacion')) {
                $set[] = 'fechaAutorizacion = NOW()';
            }
            if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'respuesta_sri')) {
                $set[] = 'respuesta_sri = ?';
                $params[] = json_encode(['origen' => 'comprobantes', 'numero_autorizacion' => $numAuthRemote, 'clave_acceso' => $claveRemote ?? null]);
            }
            $params[] = $id;
            $pdo->prepare('UPDATE facturas_venta SET ' . implode(', ', $set) . ' WHERE id = ?')->execute($params);

            echo json_encode([
                'success' => true,
                'estado' => 'AUTORIZADA',
                'numeroAutorizacion' => $numAuthRemote,
                'message' => 'Autorización obtenida (comprobantes)'
            ]);
            exit;
        }

        // Si aún no hay autorización pero sí clave, guardarla para futuras consultas
        if ((empty($claveAcceso) || strlen((string) $claveAcceso) < 40) && !empty($claveRemote) && strlen((string) $claveRemote) >= 40) {
            $claveAcceso = $claveRemote; // Se usa para la consultaSRI de abajo, pero NO se guarda en DB local
        }
    }

    // Si no tenemos clave, no podemos consultar sin recrearla.
    // Como LogifactAPI ya tiene lógica para enviar, asumiremos que ya se intentó enviar y tenemos la clave o al menos el número de factura.

    // Si numeroAutorizacion está vacío, intentamos reconstruir la clave de acceso (esquema SRI)
    if (empty($claveAcceso) || strlen($claveAcceso) < 40) {
        // Reconstrucción básica (muy técnica, pero necesaria si no se guardó)
        $fechaAcceso = date('dmY', strtotime($f['fechaEmision']));
        $ruc = $f['ruc'];
        $ambiente = $f['ambiente'];
        $serie = str_replace('-', '', substr($f['numeroFactura'], 0, 7)); // 001001
        $secuencial = str_pad(substr($f['numeroFactura'], 8), 9, '0', STR_PAD_LEFT);
        $codigoNumerico = "12345678"; // Código por defecto usado en api_guardar_venta si no se generó uno aleatorio
        $tipoEmision = "1";

        $parcialClave = $fechaAcceso . "01" . $ruc . $ambiente . $serie . $secuencial . $codigoNumerico . $tipoEmision;

        // Módulo 11 para el dígito verificador
        function calcularDigitoM11($cadena)
        {
            $reversed = strrev($cadena);
            $sum = 0;
            $factor = 2;
            for ($i = 0; $i < strlen($reversed); $i++) {
                $sum += intval($reversed[$i]) * $factor;
                $factor = ($factor == 7) ? 2 : $factor + 1;
            }
            $digit = 11 - ($sum % 11);
            if ($digit == 11)
                return 0;
            if ($digit == 10)
                return 1;
            return $digit;
        }

        $dv = calcularDigitoM11($parcialClave);
        $claveAcceso = $parcialClave . $dv;
    }

    // 3. Consultar a Logifact
    $res = LogifactAPI::consultaSRI($claveAcceso);

    if (isset($res['estado'])) {
        $estadoSRI = strtoupper($res['estado']);
        if ($estadoSRI === 'AUTORIZADO' || $estadoSRI === 'AUTORIZADA') {
            $numAuth = $res['numeroAutorizacion'] ?? $res['autorizacion'] ?? $claveAcceso;

            $set = ["estadoFactura = 'AUTORIZADA'", 'numeroAutorizacion = ?'];
            $params = [$numAuth];
            if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'fechaAutorizacion')) {
                $set[] = 'fechaAutorizacion = NOW()';
            }
            if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'respuesta_sri')) {
                $set[] = 'respuesta_sri = ?';
                $params[] = json_encode($res);
            }
            if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'claveAcceso')) {
                $clave = $res['claveAcceso'] ?? $claveAcceso;
                if ($clave) {
                    $set[] = 'claveAcceso = ?';
                    $params[] = (string) $clave;
                }
            }
            $params[] = $id;
            $pdo->prepare('UPDATE facturas_venta SET ' . implode(', ', $set) . ' WHERE id = ?')
                ->execute($params);

            echo json_encode([
                'success' => true,
                'estado' => 'AUTORIZADA',
                'numeroAutorizacion' => $numAuth,
                'message' => 'Autorización obtenida con éxito'
            ]);
            exit;
        }
    }

    echo json_encode([
        'success' => true,
        'estado' => ($f['estadoFactura'] ?? 'PENDIENTE'),
        'sri_status' => $res['estado'] ?? 'NO ENCONTRADO',
        'claveAcceso' => $claveAcceso,
        'message' => 'Aún no autorizado o en proceso'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
