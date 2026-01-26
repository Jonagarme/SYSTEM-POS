<?php
/**
 * Cron Script: Sincronización Automática con el SRI (Logifact)
 * Versión mejorada para Local y Producción
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/logifact_api.php';

$is_json = (isset($_GET['format']) && $_GET['format'] === 'json');
$results = [
    'checked' => 0,
    'updated' => 0,
    'pending' => 0,
    'errors' => 0,
    'details' => []
];

if (!$is_json) {
    echo "--- Iniciando Sincronización SRI (" . date('Y-m-d H:i:s') . ") ---\n";
}

try {
    // 0. Obtener datos de la empresa para reconstrucción de claves si es necesario
    $stmtE = $pdo->query("SELECT ruc, sri_ambiente as sri_ambiente FROM empresas LIMIT 1");
    $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);

    // 1. Intentar conexión remota ONCE (solo si es posible)
    $remotePdo = null;
    $remoteDsn = "mysql:host=sql107.infinityfree.com;dbname=if0_40698217_nexusfact_db;charset=utf8mb4";
    $remoteUser = 'if0_40698217';
    $remotePass = 'jonagarme20';

    try {
        $remotePdo = new PDO($remoteDsn, $remoteUser, $remotePass, [
            PDO::ATTR_TIMEOUT => 2, // Timeout corto para no colgar local
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (Exception $e) {
        if (!$is_json)
            echo "Nota: Base remota no accesible (esperado en local). Usando Fallback API HTTP.\n";
    }

    // 2. Obtener facturas pendientes (últimos 30 días, máximo 10)
    $stmt = $pdo->query("SELECT f.* 
                         FROM facturas_venta f 
                         WHERE f.estadoFactura != 'AUTORIZADA' 
                         AND f.creadoDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         AND f.anulado = 0
                         ORDER BY f.creadoDate DESC
                         LIMIT 10");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($facturas as $f) {
        $results['checked']++;
        $authorized_now = false;
        $found_data = null;

        // --- PASO 1: Intentar por DB Remota (si disponible) ---
        if ($remotePdo) {
            try {
                $numFact = $f['numeroFactura'];
                $parts = explode('-', $numFact);
                if (count($parts) >= 3) {
                    $serie = $parts[0] . '-' . $parts[1];
                    $sec = $parts[2];

                    $stmtR = $remotePdo->prepare("SELECT numero_autorizacion, fecha_autorizacion, clave_acceso 
                                                  FROM comprobantes 
                                                  WHERE (serie = ? OR serie = REPLACE(?,'-',' ')) 
                                                  AND (secuencial = ? OR secuencial = ?) 
                                                  AND numero_autorizacion IS NOT NULL LIMIT 1");
                    $stmtR->execute([$serie, $serie, $sec, ltrim($sec, '0')]);
                    $found_data = $stmtR->fetch(PDO::FETCH_ASSOC);
                }
            } catch (Exception $e_rem) {
                // Silencioso, pasará al PASO 2
            }
        }

        // --- PASO 2: Intentar por API Logifact (Fallback Local/Prod) ---
        if (!$found_data) {
            $claveConsulta = $f['numeroAutorizacion']; // Podría tener la clave de 49 dígitos guardada ahí

            // Si no tiene clave, reconstruirla
            if (empty($claveConsulta) || strlen($claveConsulta) < 40) {
                $fecha = date('dmY', strtotime($f['fechaEmision']));
                $ruc = $empresa['ruc'] ?? '0915912604001';
                $amb = $empresa['sri_ambiente'] ?? 1;
                $serie = str_replace('-', '', substr($f['numeroFactura'], 0, 7));
                $secuencial = str_pad(substr($f['numeroFactura'], 8), 9, '0', STR_PAD_LEFT);
                $parcial = $fecha . "01" . $ruc . $amb . $serie . $secuencial . "12345678" . "1";

                // Módulo 11
                $reversed = strrev($parcial);
                $sum = 0;
                $factor = 2;
                for ($i = 0; $i < strlen($reversed); $i++) {
                    $sum += intval($reversed[$i]) * $factor;
                    $factor = ($factor == 7) ? 2 : $factor + 1;
                }
                $digit = 11 - ($sum % 11);
                $dv = ($digit >= 10) ? ($digit == 11 ? 0 : 1) : $digit;
                $claveConsulta = $parcial . $dv;
            }

            $resAPI = LogifactAPI::consultaSRI($claveConsulta);
            $estadoSRI = strtoupper($resAPI['estado'] ?? '');

            if ($estadoSRI === 'AUTORIZADO' || $estadoSRI === 'AUTORIZADA') {
                $found_data = [
                    'numero_autorizacion' => $resAPI['numeroAutorizacion'] ?? $resAPI['autorizacion'] ?? $claveConsulta,
                    'fecha_autorizacion' => $resAPI['fechaAutorizacion'] ?? date('Y-m-d H:i:s')
                ];
            }
        }

        // --- ACTUALIZAR SI SE ENCONTRÓ AUTORIZACIÓN ---
        if ($found_data) {
            $set = ["estadoFactura = 'AUTORIZADA'", "numeroAutorizacion = ?", "fechaAutorizacion = ?"];
            $updParams = [$found_data['numero_autorizacion'], $found_data['fecha_autorizacion']];

            if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'claveAcceso')) {
                $clave = $found_data['clave_acceso'] ?? $claveConsulta;
                if ($clave) {
                    $set[] = "claveAcceso = ?";
                    $updParams[] = (string) $clave;
                }
            }

            $updParams[] = $f['id'];
            $upd = $pdo->prepare("UPDATE facturas_venta SET " . implode(', ', $set) . " WHERE id = ?");
            $upd->execute($updParams);
            $results['updated']++;

            if (!$is_json)
                echo "✓ Factura " . $f['numeroFactura'] . " AUTORIZADA ahora.\n";
        } else {
            $results['pending']++;
            if (!$is_json)
                echo "- Factura " . $f['numeroFactura'] . " sigue PENDIENTE.\n";
        }
    }

    if (!$is_json)
        echo "--- Sincronización Finalizada ---\n";

} catch (Exception $e) {
    if ($is_json) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
    echo "ERROR: " . $e->getMessage() . "\n";
}

if ($is_json) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'results' => $results]);
}
