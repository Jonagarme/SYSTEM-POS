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

        // 3. Descontar Stock
        if ($idProducto > 0) {
            $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?")
                ->execute([$det['cantidad'], $idProducto]);
        }
    }

    $pdo->commit();

    // 4. Forward to Logifact API (NO afecta el éxito de la venta local)
    $external_res = null;
    $sriError = null;
    
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
        $venta['ambiente'] = ($empresa['ambiente'] == 2) ? 2 : 1;
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

        // --- ACTUALIZAR ESTADO EN BASE DE DATOS LOCAL ---
        if ($external_res && isset($external_res['estado'])) {
            $sriEstado = strtoupper($external_res['estado']);
            $authNumber = $external_res['numeroAutorizacion'] ?? $external_res['autorizacion'] ?? $external_res['claveAcceso'] ?? null;

            if (is_array($authNumber))
                $authNumber = json_encode($authNumber);

            // Guardamos la clave de acceso/autorización SIEMPRE que venga algo
            if ($authNumber) {
                $dbEstado = ($sriEstado === 'AUTORIZADO' || $sriEstado === 'AUTORIZADA') ? 'AUTORIZADA' : 'PENDIENTE';
                if ($sriEstado === 'DEVUELTA' || $sriEstado === 'NO AUTORIZADO' || $sriEstado === 'RECHAZADO')
                    $dbEstado = 'RECHAZADO';

                $pdo->prepare("UPDATE facturas_venta SET estadoFactura = ?, numeroAutorizacion = ?, fechaAutorizacion = " . ($dbEstado === 'AUTORIZADA' ? "NOW()" : "NULL") . " WHERE id = ?")
                    ->execute([$dbEstado, $authNumber, $idVenta]);
            }
        }
        }
    } catch (Exception $sriEx) {
        // Si falla el SRI, la venta ya se guardó localmente como PENDIENTE
        $sriError = $sriEx->getMessage();
        error_log("Error al enviar al SRI (factura $numFactura): " . $sriError);
    }

    echo json_encode([
        'success' => true,
        'id' => $idVenta,
        'numero' => $numFactura,
        'external' => $external_res,
        'sri_error' => $sriError,
        'estado_factura' => $external_res && isset($external_res['estado']) ? strtoupper($external_res['estado']) : 'PENDIENTE'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
