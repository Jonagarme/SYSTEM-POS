<?php
/**
 * Printer-friendly Closure Ticket
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Error: No se proporcionó el ID de cierre.");
}

// 1. Obtener datos del cierre
$stmt = $pdo->prepare("
    SELECT c.*, ca.nombre as caja_nombre, ca.codigo as caja_codigo,
           uA.nombreCompleto as usuario_apertura,
           uC.nombreCompleto as usuario_cierre
    FROM cierres_caja c
    LEFT JOIN cajas ca ON c.idCaja = ca.id
    LEFT JOIN usuarios uA ON c.idUsuarioApertura = uA.id
    LEFT JOIN usuarios uC ON c.idUsuarioCierre = uC.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$cierre = $stmt->fetch();

if (!$cierre) {
    die("Error: Cierre no encontrado.");
}

// 2. Obtener datos de la empresa
$stmtEmp = $pdo->query("SELECT * FROM empresas LIMIT 1");
$empresa = $stmtEmp->fetch();

// 3. Resumen de Ventas por Forma de Pago
$stmtVentas = $pdo->prepare("
    SELECT formaPago, COUNT(*) as transacciones, SUM(total) as total
    FROM facturas_venta
    WHERE idCierreCaja = ? AND anulado = 0
    GROUP BY formaPago
");
$stmtVentas->execute([$id]);
$resumen_pagos = $stmtVentas->fetchAll();

$total_ventas = 0;
foreach ($resumen_pagos as $p) {
    $total_ventas += $p['total'];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ticket de Cierre #
        <?php echo $id; ?>
    </title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
            width: 80mm;
            /* Ancho estándar ticket térmico */
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .header {
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .totals-section {
            margin-top: 10px;
        }

        .footer {
            margin-top: 30px;
        }

        .signature {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 150px;
            margin-left: auto;
            margin-right: auto;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">Imprimir Ticket</button>
        <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer;">Cerrar</button>
    </div>

    <div class="header text-center">
        <h2>
            <?php echo htmlspecialchars($empresa['nombre_comercial'] ?? 'SISTEMA POS'); ?>
        </h2>
        <div>RUC:
            <?php echo htmlspecialchars($empresa['ruc'] ?? '0000000000001'); ?>
        </div>
        <div>
            <?php echo htmlspecialchars($empresa['direccion_matriz'] ?? ''); ?>
        </div>
        <div class="divider"></div>
        <div class="bold">COMPROBANTE DE CIERRE DE CAJA</div>
        <div>ID Cierre: #
            <?php echo str_pad($cierre['id'], 6, '0', STR_PAD_LEFT); ?>
        </div>
        <div>Estado:
            <?php echo $cierre['estado']; ?>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span>Caja:</span>
            <span class="bold">
                <?php echo htmlspecialchars($cierre['caja_nombre']); ?>
            </span>
        </div>
        <div class="info-row">
            <span>Apertura:</span>
            <span>
                <?php echo date('d/m/Y H:i', strtotime($cierre['fechaApertura'])); ?>
            </span>
        </div>
        <div class="info-row">
            <span>Cierre:</span>
            <span>
                <?php echo $cierre['fechaCierre'] ? date('d/m/Y H:i', strtotime($cierre['fechaCierre'])) : 'N/A'; ?>
            </span>
        </div>
        <div class="info-row">
            <span>Usuario:</span>
            <span>
                <?php echo htmlspecialchars($cierre['usuario_apertura']); ?>
            </span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="financial-section">
        <div class="info-row">
            <span>SALDO INICIAL:</span>
            <span class="bold">$
                <?php echo number_format($cierre['saldoInicial'], 2); ?>
            </span>
        </div>

        <div style="margin-top: 8px; font-weight: bold;">VENTAS POR FORMA DE PAGO:</div>
        <?php foreach ($resumen_pagos as $p): ?>
            <div class="info-row" style="padding-left: 10px;">
                <span>
                    <?php echo $p['formaPago']; ?> (
                    <?php echo $p['transacciones']; ?>):
                </span>
                <span>$
                    <?php echo number_format($p['total'], 2); ?>
                </span>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>

        <div class="info-row bold">
            <span>TOTAL VENTAS:</span>
            <span>$
                <?php echo number_format($total_ventas, 2); ?>
            </span>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span>SALDO TEÓRICO:</span>
            <span>$
                <?php echo number_format($cierre['saldoTeoricoSistema'], 2); ?>
            </span>
        </div>
        <div class="info-row">
            <span>EFECTIVO FÍSICO:</span>
            <span class="bold">$
                <?php echo number_format($cierre['totalContadoFisico'], 2); ?>
            </span>
        </div>

        <div class="info-row bold" style="margin-top: 5px;">
            <span>DIFERENCIA:</span>
            <span style="color: <?php echo $cierre['diferencia'] < 0 ? 'red' : 'green'; ?>">
                $
                <?php echo number_format($cierre['diferencia'], 2); ?>
            </span>
        </div>
    </div>

    <div class="divider"></div>

    <?php if (!empty($cierre['observaciones'])): ?>
        <div style="margin-bottom: 15px;">
            <span class="bold">Observaciones:</span><br>
            <?php echo nl2br(htmlspecialchars($cierre['observaciones'])); ?>
        </div>
    <?php endif; ?>

    <div class="footer text-center">
        <div class="signature"></div>
        <div style="margin-top: 5px;">Firma del Cajero</div>
        <div style="font-size: 10px; margin-top: 5px;">
            <?php echo htmlspecialchars($cierre['usuario_cierre'] ?? $cierre['usuario_apertura']); ?>
        </div>

        <div class="signature" style="margin-top: 30px;"></div>
        <div style="margin-top: 5px;">Firma Supervisor</div>

        <div class="divider"></div>
        <div style="font-size: 10px;">Impreso el:
            <?php echo date('d/m/Y H:i:s'); ?>
        </div>
        <div style="font-size: 10px;">Warehouse POS System</div>
    </div>

</body>

</html>