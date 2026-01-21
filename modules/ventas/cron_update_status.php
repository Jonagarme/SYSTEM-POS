<?php
/**
 * Cron Script: Sincronización Automática con el SRI (Logifact)
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
    // 1. Obtener facturas no autorizadas de los últimos 3 días
    // Nota: Usamos ambiente de la empresa para reconstruir clave si falta
    $stmtE = $pdo->query("SELECT ruc, sri_ambiente as sri_ambiente FROM empresas LIMIT 1");
    $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);

    // Conexión remota (tabla comprobantes en la API) - reutilizable
    $remotePdo = null;
    $remoteDsn = "mysql:host=sql107.infinityfree.com;dbname=if0_40698217_nexusfact_db;charset=utf8mb4";
    $remoteUser = 'if0_40698217';
    $remotePass = 'jonagarme20';

    $stmt = $pdo->query("SELECT f.* 
                         FROM facturas_venta f 
                         WHERE f.estadoFactura != 'AUTORIZADA' 
                         AND f.creadoDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         ORDER BY f.creadoDate DESC");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$is_json)
        echo "Facturas encontradas para revisar: " . count($facturas) . "\n";

    foreach ($facturas as $f) {
        $results['checked']++;
        $status_msg = "";
        $authorized_now = false;

        // PRIORIDAD 1: BÚSQUEDA DIRECTA EN TABLA COMPROBANTES (API)
        // Buscar primero en la tabla comprobantes usando serie y secuencial del numeroFactura
        try {
            if ($remotePdo === null) {
                $remotePdo = new PDO($remoteDsn, $remoteUser, $remotePass, [
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            }

            // Extraer serie (001-999) y secuencial (000000112) del numeroFactura
            $numeroFactura = $f['numeroFactura'];
            $parts = explode('-', $numeroFactura);
            
            if (count($parts) >= 3) {
                $hasNumericFormat = (bool)preg_match('/^\d{3}$/', $parts[0])
                    && (bool)preg_match('/^\d{3}$/', $parts[1])
                    && (bool)preg_match('/^\d+$/', $parts[2]);

                if (!$hasNumericFormat) {
                    if ($is_json) {
                        $results['details'][] = [
                            'id' => $f['id'],
                            'numero' => $f['numeroFactura'],
                            'estado' => 'FORMATO_NUMEROFACTURA_INVALIDO'
                        ];
                    }
                    // No intentamos buscar en comprobantes si el formato no corresponde
                } else {
                    $serie = trim($parts[0] . '-' . $parts[1]); // 001-999
                    $secuencial = trim($parts[2]); // 000000112

                // Variantes comunes en BD remota
                $serieHyphen = $serie; // 001-999
                $serieSpace = trim($parts[0] . ' ' . $parts[1]); // 001 999
                $serieConcat = trim($parts[0] . $parts[1]); // 001999

                $secuencialFull = $secuencial; // 000000112
                $secuencial8 = substr($secuencialFull, -8); // 00000112
                $secuencialShort = ltrim($secuencialFull, '0'); // 112
                if ($secuencialShort === '') {
                    $secuencialShort = '0';
                }
                
                if (!$is_json) {
                    echo "Buscando factura " . $numeroFactura . " (serie: $serie, secuencial: $secuencial)... ";
                }
                
                // Buscar en comprobantes (tolerante a formatos)
                $stmtR = $remotePdo->prepare("SELECT numero_autorizacion, fecha_autorizacion, clave_acceso
                                              FROM comprobantes
                                              WHERE (TRIM(serie) = ? OR TRIM(serie) = ? OR TRIM(serie) = ?)
                                              AND (TRIM(secuencial) = ? OR TRIM(secuencial) = ? OR TRIM(secuencial) = ? OR TRIM(secuencial) LIKE ?)
                                              AND numero_autorizacion IS NOT NULL
                                              AND numero_autorizacion != ''
                                              ORDER BY id DESC
                                              LIMIT 1");

                $stmtR->execute([
                    $serieHyphen,
                    $serieSpace,
                    $serieConcat,
                    $secuencialFull,
                    $secuencial8,
                    $secuencialShort,
                    "%$secuencialShort"
                ]);
                $comprobante = $stmtR->fetch(PDO::FETCH_ASSOC);

                if ($comprobante) {
                    // ¡Encontrado! Actualizar facturas_venta
                    $numAuth = $comprobante['numero_autorizacion'];
                    $claveAcceso = $comprobante['clave_acceso'] ?? $numAuth;
                    $fechaAuth = $comprobante['fecha_autorizacion'] ?? date('Y-m-d H:i:s');

                    $upd = $pdo->prepare("UPDATE facturas_venta 
                                         SET estadoFactura = 'AUTORIZADA', 
                                             numeroAutorizacion = ?,
                                             fechaAutorizacion = ?
                                         WHERE id = ?");
                    $upd->execute([$claveAcceso, $fechaAuth, $f['id']]);

                    $authorized_now = true;
                    $results['updated']++;
                    $status_msg = "AUTORIZADA";
                    
                    if (!$is_json) {
                        echo "✓ AUTORIZADA\n";
                    } else {
                        $results['details'][] = [
                            'id' => $f['id'],
                            'numero' => $f['numeroFactura'],
                            'serie' => $serieHyphen,
                            'secuencial' => $secuencialFull,
                            'estado' => $status_msg,
                            'numero_autorizacion' => $numAuth,
                            'fecha_autorizacion' => $fechaAuth
                        ];
                    }
                    
                    continue; // Ya está autorizada, pasar a la siguiente
                } else {
                    if (!$is_json) {
                        echo "no encontrada en comprobantes\n";
                    } else {
                        $results['details'][] = [
                            'id' => $f['id'],
                            'numero' => $f['numeroFactura'],
                            'serie' => $serieHyphen,
                            'secuencial' => $secuencialFull,
                            'estado' => 'NO_ENCONTRADA_EN_COMPROBANTES',
                            'serie_variantes' => [$serieHyphen, $serieSpace, $serieConcat],
                            'secuencial_variantes' => [$secuencialFull, $secuencial8, $secuencialShort, "%$secuencialShort"]
                        ];
                    }
                }
            }
            } else {
                if ($is_json) {
                    $results['details'][] = [
                        'id' => $f['id'],
                        'numero' => $f['numeroFactura'],
                        'estado' => 'FORMATO_NUMEROFACTURA_INVALIDO'
                    ];
                }
            }
        } catch (Exception $e_ext) {
            $results['last_error'] = "Error BD comprobantes: " . $e_ext->getMessage();
            error_log("Error consultando comprobantes: " . $e_ext->getMessage());
            $results['errors']++;
            if (!$is_json) {
                echo "error: " . $e_ext->getMessage() . "\n";
            } else {
                $results['details'][] = [
                    'id' => $f['id'],
                    'numero' => $f['numeroFactura'],
                    'estado' => 'ERROR_CONSULTANDO_COMPROBANTES',
                    'error' => $e_ext->getMessage()
                ];
            }

            // Si hubo error o formato inválido, pasamos a la siguiente factura
            continue;
        }

        // Si llegamos aquí, no se encontró en comprobantes
        // Continuar con el flujo normal solo para logging o fallback
        $results['pending']++;
        $status_msg = "PENDIENTE";
        
        if (!$is_json) {
            echo "Factura " . $f['numeroFactura'] . " -> PENDIENTE (no encontrada)\n";
        }
    }

    if (!$is_json)
        echo "--- Proceso Finalizado ---\n";

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

