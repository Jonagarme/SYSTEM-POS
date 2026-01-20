<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/logifact_api.php';
session_start();
date_default_timezone_set('America/Guayaquil');

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID de factura no proporcionado']);
    exit;
}

try {
    // 1. Obtener la factura y datos del cliente
    $stmt = $pdo->prepare("
        SELECT f.*, c.nombres, c.apellidos, c.cedula_ruc, c.tipo_identificacion, c.email
        FROM facturas_venta f
        JOIN clientes c ON f.idCliente = c.id
        WHERE f.id = ?
    ");
    $stmt->execute([$id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception('Factura no encontrada');
    }

    if (!empty($venta['numeroAutorizacion'])) {
        throw new Exception('Esta factura ya tiene un número de autorización y no puede ser reenviada');
    }

    // 2. Obtener datos de la empresa
    try {
        $stmtE = $pdo->query("SELECT * FROM empresas LIMIT 1");
        $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmtE = $pdo->query("SELECT * FROM usuarios_configuracionempresa LIMIT 1");
        $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);
    }

    $dirEstablecimiento = $empresa['direccion_matriz'] ?? $empresa['direccion'] ?? 'Av. Principal 123';
    $obligadoContabilidad = (isset($empresa['obligado_contabilidad']) && $empresa['obligado_contabilidad'] == 1) ? 'SI' : 'NO';

    // 3. Obtener detalles
    $stmtDet = $pdo->prepare("
        SELECT d.*, p.codigoPrincipal 
        FROM facturas_venta_detalle d
        LEFT JOIN productos p ON d.idProducto = p.id
        WHERE d.idFacturaVenta = ?
    ");
    $stmtDet->execute([$id]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    // 3. Mapeo de identificación para SRI
    $tipoId = '07'; // Consumidor Final
    if ($venta['cedula_ruc'] == '9999999999999') {
        $tipoId = '07';
    } else {
        if ($venta['tipo_identificacion'] == 'RUC' || strlen($venta['cedula_ruc']) == 13)
            $tipoId = '04';
        else if ($venta['tipo_identificacion'] == 'CEDULA' || strlen($venta['cedula_ruc']) == 10)
            $tipoId = '05';
        else if ($venta['tipo_identificacion'] == 'PASAPORTE')
            $tipoId = '06';
    }

    // 4. Construir JSON de Factura (Basado en la estructura de Logifact)
    $certPath = trim($empresa['certificado_p12_path'] ?? '');
    // Override si es ruta de Windows (C:, c:, \, /Users/) o está vacía
    if (empty($certPath) || stripos($certPath, 'C:') !== false || strpos($certPath, '\\') !== false || stripos($certPath, 'Users') !== false) {
        $certPath = "/home/vol10_3/infinityfree.com/if0_40698217/htdocs/certs/CELIDA_SABINA_GOMEZ_SANCHEZ_151024.p12";
    }

    $certPass = trim($empresa['certificado_password'] ?? '');
    if (empty($certPass) || $certPath == "/home/vol10_3/infinityfree.com/if0_40698217/htdocs/certs/CELIDA_SABINA_GOMEZ_SANCHEZ_151024.p12") {
        $certPass = "Cg2875caae";
    }

    $jsonFactura = [
        "tipo" => "factura",
        "data" => [
            "ambiente" => ($empresa['ambiente'] == 2) ? 2 : 1,
            "tipoEmision" => "1",
            "identificacionEmisor" => $empresa['ruc'] ?? '0915912604001',
            "razonSocialEmisor" => $empresa['razon_social'] ?? 'GOMEZ SANCHEZ CELIDA SABINA',
            "certificado_p12_path" => $certPath,
            "certificado_password" => $certPass,
            "codDoc" => "01",
            "establecimiento" => explode('-', $venta['numeroFactura'])[0] ?? '001',
            "puntoEmision" => explode('-', $venta['numeroFactura'])[1] ?? '001',
            "secuencial" => explode('-', $venta['numeroFactura'])[2] ?? '000000001',
            "fechaEmision" => date('d/m/Y', strtotime($venta['fechaEmision'])),
            "dirEstablecimiento" => !empty($empresa['direccion_matriz']) ? $empresa['direccion_matriz'] : 'Matriz',
            "obligadoContabilidad" => (isset($empresa['obligado_contabilidad']) && ($empresa['obligado_contabilidad'] == '1' || $empresa['obligado_contabilidad'] == 'SI')) ? "SI" : "NO",
            "tipoIdentificacionComprador" => (strlen($venta['cedula_ruc'] ?? '') == 13 ? "04" : (strlen($venta['cedula_ruc'] ?? '') == 10 ? "05" : "06")),
            "razonSocialComprador" => !empty(trim($venta['nombres'] ?? '')) ? trim(($venta['nombres'] ?? '') . ' ' . ($venta['apellidos'] ?? '')) : "CONSUMIDOR FINAL",
            "identificacionComprador" => !empty($venta['cedula_ruc']) ? $venta['cedula_ruc'] : "9999999999",
            "totalSinImpuestos" => number_format((float) $venta['subtotal'], 2, '.', ''),
            "totalDescuento" => number_format((float) $venta['descuento'], 2, '.', ''),
            "importeTotal" => number_format((float) $venta['total'], 2, '.', ''),
            "moneda" => "DOLAR",
            "impuestos" => [
                [
                    "codigo" => "2", // IVA
                    "codigoPorcentaje" => "4", // 15% (Según normativa SRI)
                    "baseImponible" => number_format((float) $venta['subtotal'], 2, '.', ''),
                    "valor" => number_format((float) $venta['iva'], 2, '.', '')
                ]
            ],
            "pagos" => [
                [
                    "formaPago" => "01", // Sin utilización del sistema financiero (Efectivo)
                    "total" => number_format((float) $venta['total'], 2, '.', '')
                ]
            ],
            "detalles" => []
        ]
    ];

    foreach ($detalles as $det) {
        $jsonFactura['data']['detalles'][] = [
            "codigoPrincipal" => $det['codigoPrincipal'] ?: 'PROD' . $det['idProducto'],
            "description" => $det['productoNombre'],
            "cantidad" => number_format((float) $det['cantidad'], 6, '.', ''),
            "precioUnitario" => number_format((float) $det['precioUnitario'], 6, '.', ''),
            "descuento" => number_format((float) $det['descuentoValor'], 2, '.', ''),
            "precioTotalSinImpuesto" => number_format((float) ($det['precioUnitario'] * $det['cantidad'] - $det['descuentoValor']), 2, '.', ''),
            "impuestos" => [
                [
                    "codigo" => "2",
                    "codigoPorcentaje" => "4",
                    "tarifa" => 15,
                    "baseImponible" => number_format((float) ($det['precioUnitario'] * $det['cantidad'] - $det['descuentoValor']), 2, '.', ''),
                    "valor" => number_format((float) $det['ivaValor'], 2, '.', '')
                ]
            ]
        ];
    }

    // 5. Enviar a Logifact
    $token = LogifactAPI::login();
    if (!$token) {
        throw new Exception("No se pudo autenticar con la API de Logifact");
    }

    // LOG PARA DEBUG (Puedes revisarlo en los logs del servidor)
    error_log("Enviando factura con certificado: " . $certPath);

    // LOG PARA DEBUG
    $res = LogifactAPI::sendInvoice($jsonFactura, $token);
    error_log("Respuesta Logifact: " . json_encode($res));

    // Determinar estado real
    $sriEstado = $res['estado'] ?? '';
    $isAuthorized = ($sriEstado === 'AUTORIZADO'); // SRIV2 es más estricto

    // Si NO está autorizado pero devolvió algo (error del SRI o DEVUELTA)
    if (!$isAuthorized && (!empty($sriEstado) || isset($res['mensajes']))) {
        $msgStatus = $sriEstado ?: 'RECHAZADO';
        $msg = "SRI: $msgStatus";

        if (isset($res['mensajes'])) {
            $mensajesRes = $res['mensajes'];
            if (isset($mensajesRes['mensaje']['mensaje'])) {
                $msg = $mensajesRes['mensaje']['mensaje'];
            } else if (is_array($mensajesRes)) {
                $msg = json_encode($mensajesRes);
            }
        }

        // Si es DEVUELTA o ERROR, debemos marcarlo correctamente en DB para permitir reenvío
        $dbEstado = ($msgStatus === 'DEVUELTA' || $msgStatus === 'NO AUTORIZADO') ? 'RECHAZADO' : $msgStatus;
        $pdo->prepare("UPDATE facturas_venta SET estadoFactura = ?, numeroAutorizacion = NULL WHERE id = ?")
            ->execute([$dbEstado, $id]);

        throw new Exception($msg);
    }

    if ($isAuthorized) {
        // Actualizar número de autorización o clave de acceso
        $auth = $res['numeroAutorizacion'] ?? $res['autorizacion'] ?? $res['claveAcceso'] ?? null;

        // Asegurarnos de que no sea un array
        if (is_array($auth)) {
            $auth = json_encode($auth);
        }

        if ($auth) {
            $pdo->prepare("UPDATE facturas_venta SET numeroAutorizacion = ?, estadoFactura = 'AUTORIZADA' WHERE id = ?")
                ->execute([$auth, $id]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Factura procesada con éxito',
            'external' => $res
        ]);
    } else {
        $errorMessage = $res['error'] ?? $res['mensaje'] ?? $res['message'] ?? 'Error desconocido al enviar al SRI';
        throw new Exception($errorMessage);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
