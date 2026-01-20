<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/logifact_api.php';
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID de factura no proporcionado']);
    exit;
}

try {
    $pdo->beginTransaction();

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

    if ($venta['anulado'] == 1) {
        throw new Exception('La factura ya está anulada');
    }

    // 2. Obtener detalles
    $stmtDet = $pdo->prepare("
        SELECT d.*, p.codigoPrincipal 
        FROM facturas_venta_detalle d
        LEFT JOIN productos p ON d.idProducto = p.id
        WHERE d.idFacturaVenta = ?
    ");
    $stmtDet->execute([$id]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener datos de la empresa (Intentar tabla empresas primero)
    try {
        $stmtE = $pdo->query("SELECT * FROM empresas LIMIT 1");
        $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmtE = $pdo->query("SELECT * FROM usuarios_configuracionempresa LIMIT 1");
        $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);
    }

    $dirEstablecimiento = $empresa['direccion_matriz'] ?? $empresa['direccion'] ?? 'Av. Principal 123';

    // Override de certificado para API remota
    $certPath = trim($empresa['certificado_p12_path'] ?? '');
    if (empty($certPath) || stripos($certPath, 'C:') !== false || strpos($certPath, '\\') !== false || stripos($certPath, 'Users') !== false) {
        $certPath = "/home/vol10_3/infinityfree.com/if0_40698217/htdocs/certs/CELIDA_SABINA_GOMEZ_SANCHEZ_151024.p12";
    }
    $certPass = trim($empresa['certificado_password'] ?? '');
    if (empty($certPass) || $certPath == "/home/vol10_3/infinityfree.com/if0_40698217/htdocs/certs/CELIDA_SABINA_GOMEZ_SANCHEZ_151024.p12") {
        $certPass = "Cg2875caae";
    }

    // 4. Mapeo de identificación para SRI
    $tipoId = '07'; // Consumidor Final por defecto
    if ($venta['tipo_identificacion'] == 'RUC')
        $tipoId = '04';
    if ($venta['tipo_identificacion'] == 'CEDULA')
        $tipoId = '05';
    if ($venta['tipo_identificacion'] == 'PASAPORTE')
        $tipoId = '06';

    // 5. Construir JSON de Nota de Crédito
    $notaCredito = [
        "tipo" => "notaCredito",
        "data" => [
            "certificado_p12_path" => $certPath,
            "certificado_password" => $certPass,
            "fechaEmision" => date('d/m/Y'),
            "dirEstablecimiento" => $dirEstablecimiento,
            "tipoIdentificacionComprador" => $tipoId,
            "razonSocialComprador" => trim($venta['nombres'] . ' ' . ($venta['apellidos'] ?? '')),
            "identificacionComprador" => $venta['cedula_ruc'],
            "codDocModificado" => "01", // 01 es Factura
            "numDocModificado" => $venta['numeroFactura'],
            "fechaEmisionDocSustento" => date('d/m/Y', strtotime($venta['fechaEmision'])),
            "totalSinImpuestos" => (float) $venta['subtotal'],
            "valorModificacion" => (float) $venta['total'],
            "motivo" => "DEVOLUCION",
            "moneda" => "DOLAR",
            "impuestos" => [
                [
                    "codigo" => "2", // IVA
                    "codigoPorcentaje" => "4", // 15% (Según normativa SRI)
                    "baseImponible" => (float) $venta['subtotal'],
                    "valor" => (float) $venta['iva']
                ]
            ],
            "detalles" => [],
            "infoAdicional" => [
                "email" => $venta['email'] ?: 'cliente@example.com'
            ]
        ]
    ];

    foreach ($detalles as $det) {
        $notaCredito['data']['detalles'][] = [
            "codigoInterno" => $det['codigoPrincipal'] ?: 'PROD' . $det['idProducto'],
            "descripcion" => $det['productoNombre'],
            "cantidad" => (float) $det['cantidad'],
            "precioUnitario" => (float) $det['precioUnitario'],
            "descuento" => (float) $det['descuentoValor'],
            "precioTotalSinImpuesto" => (float) ($det['precioUnitario'] * $det['cantidad'] - $det['descuentoValor']),
            "impuestos" => [
                [
                    "codigo" => "2",
                    "codigoPorcentaje" => "4",
                    "tarifa" => 15,
                    "baseImponible" => (float) ($det['precioUnitario'] * $det['cantidad'] - $det['descuentoValor']),
                    "valor" => (float) $det['ivaValor']
                ]
            ]
        ];

        // 5. Devolver stock (opcional, dependiendo de si es anulación total)
        if ($det['idProducto'] > 0) {
            $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?")
                ->execute([$det['cantidad'], $det['idProducto']]);
        }
    }

    // 6. Marcar como anulada en la base de datos
    $pdo->prepare("UPDATE facturas_venta SET anulado = 1 WHERE id = ?")->execute([$id]);

    $pdo->commit();

    // 7. Enviar a SRI vía Logifact
    $external_res = null;
    $token = LogifactAPI::login();
    if ($token) {
        $external_res = LogifactAPI::sendInvoice($notaCredito, $token); // Se usa el mismo método ya que el endpoint recibe el JSON con "tipo"
    }

    echo json_encode([
        'success' => true,
        'nota_credito' => $external_res['numero'] ?? 'Generada en SRI',
        'external' => $external_res
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
