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
    $usuario_id = $_SESSION['user_id'] ?? 1; // ID por defecto si no hay sesión

    // 1. Insertar en facturas_venta
    // Nota: Usamos los nombres de columna de tu tabla real detectados en el backup
    $stmt = $pdo->prepare("INSERT INTO facturas_venta 
        (idCliente, idUsuario, numeroFactura, fechaEmision, subtotal, descuento, iva, total, estado, creadoPor, creadoDate) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'PAGADA', ?, NOW())");

    // Obtener la secuencia correcta (Ecuador: Establecimiento-PuntoEmision-Secuencial)
    // Buscamos el último número que siga el formato 001-001-
    $stmtSeq = $pdo->query("SELECT numeroFactura FROM facturas_venta WHERE numeroFactura LIKE '001-001-%' ORDER BY id DESC LIMIT 1");
    $lastFactura = $stmtSeq->fetchColumn();

    $secuencial = 1;
    if ($lastFactura) {
        $partes = explode('-', $lastFactura);
        $ultimoValor = end($partes);
        $secuencial = intval($ultimoValor) + 1;
    }
    $numFactura = "001-001-" . str_pad($secuencial, 9, '0', STR_PAD_LEFT);

    $stmt->execute([
        $data['cliente_id'] ?? 275, // Consumidor final por defecto
        $usuario_id,
        $numFactura,
        $venta['totalSinImpuestos'],
        $venta['totalDescuento'],
        $venta['impuestos'][0]['valor'],
        $venta['importeTotal'],
        $usuario_id
    ]);

    $idVenta = $pdo->lastInsertId();

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

    // 4. Forward to Logifact API
    $token = LogifactAPI::login();
    $external_res = null;
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

        $external_res = LogifactAPI::sendInvoice($venta, $token);
    }

    echo json_encode([
        'success' => true,
        'id' => $idVenta,
        'numero' => $numFactura,
        'external' => $external_res
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
