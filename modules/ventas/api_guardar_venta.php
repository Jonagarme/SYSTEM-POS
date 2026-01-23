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
    $usuario_id = $_SESSION['user_id'] ?? 1;
    $idPuntoEmision = $data['id_punto_emision'] ?? null;

    // 1. Obtener datos del punto de emisión y establecimiento
    if ($idPuntoEmision) {
        $stmtP = $pdo->prepare("SELECT p.*, e.codigo as cod_est FROM puntos_emision p JOIN establecimientos e ON p.id_establecimiento = e.id WHERE p.id = ? AND p.activo = 1");
        $stmtP->execute([$idPuntoEmision]);
    } else {
        $stmtP = $pdo->query("SELECT p.*, e.codigo as cod_est FROM puntos_emision p JOIN establecimientos e ON p.id_establecimiento = e.id WHERE p.activo = 1 ORDER BY e.codigo, p.codigo LIMIT 1");
    }
    $punto = $stmtP->fetch(PDO::FETCH_ASSOC);

    if (!$punto) {
        throw new Exception("No hay un punto de emisión activo configurado. Por favor, configure uno en el menú Configuración > Puntos de Emisión.");
    }

    $codEst = $punto['cod_est'];
    $codPunto = $punto['codigo'];
    $secuencial = $punto['secuencial_factura'];
    $numFactura = $codEst . "-" . $codPunto . "-" . str_pad($secuencial, 9, '0', STR_PAD_LEFT);

    // 2. Insertar en facturas_venta (estado inicial PENDIENTE hasta que SRI autorice)
    $stmt = $pdo->prepare("INSERT INTO facturas_venta 
        (idCliente, idUsuario, numeroFactura, fechaEmision, subtotal, descuento, iva, total, estado, estadoFactura, creadoPor, creadoDate) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'PAGADA', 'PENDIENTE', ?, NOW())");

    $stmt->execute([
        $data['cliente_id'] ?? 275,
        $usuario_id,
        $numFactura,
        $venta['totalSinImpuestos'],
        $venta['totalDescuento'],
        $venta['impuestos'][0]['valor'],
        $venta['importeTotal'],
        $usuario_id
    ]);

    $idVenta = $pdo->lastInsertId();

    // 3. Incrementar el secuencial
    $pdo->prepare("UPDATE puntos_emision SET secuencial_factura = secuencial_factura + 1 WHERE id = ?")
        ->execute([$punto['id']]);

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

        // 3. Descontar Stock y Registrar en Kardex
        if ($idProducto > 0) {
            // Descontar stock
            $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?")
                ->execute([$det['cantidad'], $idProducto]);

            // Obtener el saldo actual después del descuento
            $stmtStock = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
            $stmtStock->execute([$idProducto]);
            $saldoActual = $stmtStock->fetchColumn();

            // Registrar movimiento en kardex
            $stmtKardex = $pdo->prepare("INSERT INTO kardex_movimientos 
                (idProducto, tipoMovimiento, cantidad, ingreso, egreso, saldo, precio, detalle, numeroDocumento, fecha) 
                VALUES (?, 'VENTA', ?, 0, ?, ?, ?, ?, ?, NOW())");

            $stmtKardex->execute([
                $idProducto,
                $det['cantidad'],
                $det['cantidad'],
                $saldoActual,
                $det['precioUnitario'],
                'Venta - ' . $det['description'],
                $numFactura
            ]);
        }
    }

    $pdo->commit();

    // Log Audit
    require_once '../../includes/audit.php';
    registrarAuditoria('Ventas', 'CREAR', 'facturas_venta', $idVenta, "Nueva venta realizada: Factura #$numFactura (Total: $venta[importeTotal])");

    // 4. Forward to Logifact API (NO afecta el éxito de la venta local)
    // Inicializar variables para respuesta
    $external_res = null;
    $sriError = null;
    $estadoFinal = 'PENDIENTE';
    $authNumber = null;

    try {
        $token = LogifactAPI::login();
        if ($token) {
            // Inyectar/Corregir ruta de certificado para el servidor remoto
            try {
                $stmtE = $pdo->query("SELECT * FROM empresas LIMIT 1");
                $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $stmtE = $pdo->query("SELECT * FROM usuarios_configuracionempresa LIMIT 1");
                $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);
            }

            $certPath = trim($empresa['certificado_p12_path'] ?? '');
            if (empty($certPath) || stripos($certPath, 'C:') !== false || strpos($certPath, '\\') !== false || stripos($certPath, 'Users') !== false) {
                $certPath = "/home/vol10_3/infinityfree.com/if0_40698217/htdocs/certs/CELIDA_SABINA_GOMEZ_SANCHEZ_151024.p12";
            }

            $certPass = trim($empresa['certificado_password'] ?? '');
            if (empty($certPass) || $certPath == "/home/vol10_3/infinityfree.com/if0_40698217/htdocs/certs/CELIDA_SABINA_GOMEZ_SANCHEZ_151024.p12") {
                $certPass = "Cg2875caae";
            }

            // --- NORMALIZACIÓN PARA SRI (Logifact) ---
            $partesNum = explode('-', $numFactura);
            $venta['codDoc'] = "01";
            $venta['establecimiento'] = $partesNum[0];
            $venta['puntoEmision'] = $partesNum[1];
            $venta['secuencial'] = $partesNum[2];
            $venta['ambiente'] = ($empresa['sri_ambiente'] == 2) ? 2 : 1;
            $venta['tipoEmision'] = "1";
            $venta['obligadoContabilidad'] = (isset($empresa['obligado_contabilidad']) && ($empresa['obligado_contabilidad'] == '1' || $empresa['obligado_contabilidad'] == 'SI')) ? "SI" : "NO";

            // Formatear montos a 2 decimales (texto para evitar precisión flotante)
            $venta['totalSinImpuestos'] = number_format((float) $venta['totalSinImpuestos'], 2, '.', '');
            $venta['totalDescuento'] = number_format((float) $venta['totalDescuento'], 2, '.', '');
            $venta['importeTotal'] = number_format((float) $venta['importeTotal'], 2, '.', '');

            if (isset($venta['impuestos'][0])) {
                $venta['impuestos'][0]['baseImponible'] = number_format((float) $venta['impuestos'][0]['baseImponible'], 2, '.', '');
                $venta['impuestos'][0]['valor'] = number_format((float) $venta['impuestos'][0]['valor'], 2, '.', '');
                $venta['impuestos'][0]['codigoPorcentaje'] = "4"; // IVA 15%
            }

            if (isset($venta['pagos'][0])) {
                $venta['pagos'][0]['total'] = number_format((float) $venta['pagos'][0]['total'], 2, '.', '');
            }

            foreach ($venta['detalles'] as &$det) {
                $det['cantidad'] = number_format((float) $det['cantidad'], 6, '.', '');
                $det['precioUnitario'] = number_format((float) $det['precioUnitario'], 6, '.', '');
                $det['descuento'] = number_format((float) $det['descuento'], 2, '.', '');
                $det['precioTotalSinImpuesto'] = number_format((float) $det['precioTotalSinImpuesto'], 2, '.', '');
                if (isset($det['impuestos'][0])) {
                    $det['impuestos'][0]['baseImponible'] = number_format((float) $det['impuestos'][0]['baseImponible'], 2, '.', '');
                    $det['impuestos'][0]['valor'] = number_format((float) $det['impuestos'][0]['valor'], 2, '.', '');
                    $det['impuestos'][0]['codigoPorcentaje'] = "4"; // IVA 15%
                    $det['impuestos'][0]['tarifa'] = 15;
                }
            }

            $venta['certificado_p12_path'] = $certPath;
            $venta['certificado_password'] = $certPass;

            // Sobrescribir fecha con la del servidor (Ecuador) para evitar problemas de sincronización
            date_default_timezone_set('America/Guayaquil');
            $venta['fechaEmision'] = date('d/m/Y');

            // IMPORTANTE: Envolver en 'tipo' y 'data' para Logifact
            $payload = [
                "tipo" => "factura",
                "data" => $venta
            ];

            $external_res = LogifactAPI::sendInvoice($payload, $token);

            // LOG CRÍTICO: Ver qué responde Logifact
            error_log("=== RESPUESTA LOGIFACT (factura $numFactura) ===");
            error_log("Estado recibido: " . ($external_res['estado'] ?? 'NO ESTADO'));
            error_log("JSON completo: " . json_encode($external_res));

            // --- ACTUALIZAR ESTADO EN BASE DE DATOS LOCAL ---
            if ($external_res && isset($external_res['estado'])) {
                $sriEstado = strtoupper((string) $external_res['estado']);
                $authNumber = $external_res['numeroAutorizacion'] ?? $external_res['autorizacion'] ?? $external_res['claveAcceso'] ?? null;

                error_log("Estado normalizado: $sriEstado | Auth: " . ($authNumber ? substr((string) $authNumber, 0, 20) . '...' : 'NO AUTH'));

                if (is_array($authNumber)) {
                    $authNumber = json_encode($authNumber);
                }

                // Normalizar estado local
                $dbEstado = 'PENDIENTE';
                if ($sriEstado === 'AUTORIZADO' || $sriEstado === 'AUTORIZADA') {
                    $dbEstado = 'AUTORIZADA';
                } elseif ($sriEstado === 'DEVUELTA' || $sriEstado === 'NO AUTORIZADO' || $sriEstado === 'RECHAZADO') {
                    $dbEstado = 'RECHAZADO';
                } elseif (in_array($sriEstado, ['PROCESANDO', 'RECIBIDA', 'EN PROCESO'], true)) {
                    $dbEstado = 'PROCESANDO';
                }

                // Construir UPDATE tolerante a esquema
                $setParts = ['estadoFactura = ?'];
                $paramsUpd = [$dbEstado];

                if (!empty($authNumber)) {
                    $setParts[] = 'numeroAutorizacion = ?';
                    $paramsUpd[] = (string) $authNumber;
                }

                if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'fechaAutorizacion')) {
                    $setParts[] = "fechaAutorizacion = " . ($dbEstado === 'AUTORIZADA' ? 'NOW()' : 'NULL');
                }

                if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'respuesta_sri')) {
                    $setParts[] = 'respuesta_sri = ?';
                    $paramsUpd[] = json_encode($external_res);
                }

                $paramsUpd[] = $idVenta;
                $sqlUpd = 'UPDATE facturas_venta SET ' . implode(', ', $setParts) . ' WHERE id = ?';

                error_log("SQL UPDATE: $sqlUpd");
                error_log("Params: " . json_encode($paramsUpd));

                $stmtUpd = $pdo->prepare($sqlUpd);
                $stmtUpd->execute($paramsUpd);

                error_log("UPDATE ejecutado exitosamente. Rows affected: " . $stmtUpd->rowCount());
                error_log("Estado final guardado: $dbEstado");

                // Guardar estado normalizado para retornar
                $estadoFinal = $dbEstado;
            } else {
                $estadoFinal = 'PENDIENTE';
            }
        } // Cierra if ($token)
    } catch (Exception $sriEx) {
        // Si falla el SRI, la venta ya se guardó localmente como PENDIENTE
        $sriError = $sriEx->getMessage();
        $estadoFinal = 'PENDIENTE';
        error_log("Error al enviar al SRI (factura $numFactura): " . $sriError);
    }

    echo json_encode([
        'success' => true,
        'id' => $idVenta,
        'numero' => $numFactura,
        'external' => $external_res,
        'sri_error' => $sriError,
        'estado_factura' => $estadoFinal ?? 'PENDIENTE',
        'numero_autorizacion' => $authNumber ?? null
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
