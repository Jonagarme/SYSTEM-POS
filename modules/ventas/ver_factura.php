<?php
/**
 * Detailed Sales Invoice View - Factura de Venta full view
 */
session_start();
require_once '../../includes/db.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    die("ID de factura no proporcionado.");
}

// 1. Obtener cabecera de la factura, datos del cliente y vendedor
$stmt = $pdo->prepare("
    SELECT f.*, 
    CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) as cliente_nombre,
    c.cedula_ruc as cliente_ruc,
    c.direccion as cliente_direccion,
    c.celular as cliente_celular,
    c.email as cliente_email,
    u.nombreUsuario as vendedor_nombre
    FROM facturas_venta f
    LEFT JOIN clientes c ON f.idCliente = c.id
    LEFT JOIN usuarios u ON f.idUsuario = u.id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Factura no encontrada.");
}

// Mapeo a los nombres de variables usados en la vista
$factura = [
    'numero' => $data['numeroFactura'],
    'fecha' => date('d/m/Y H:i', strtotime($data['fechaEmision'])),
    'creacion' => date('d/m/Y H:i', strtotime($data['creadoDate'])),
    'cliente' => $data['cliente_nombre'],
    'identificacion' => $data['cliente_ruc'],
    'telefono' => $data['cliente_celular'] ?: 'N/A',
    'email' => $data['cliente_email'] ?: 'N/A',
    'direccion' => $data['cliente_direccion'] ?: 'N/A',
    'estado' => $data['estado'],
    'vendedor' => $data['vendedor_nombre'] ?: 'Admin',
    'id_cliente' => $data['idCliente'],
    'subtotal' => number_format($data['subtotal'], 2),
    'iva' => number_format($data['iva'], 2),
    'total' => number_format($data['total'], 2)
];

// 2. Obtener detalles de la factura y unir con productos para el código
$stmtDet = $pdo->prepare("
    SELECT d.*, p.codigoPrincipal as codigo 
    FROM facturas_venta_detalle d
    LEFT JOIN productos p ON d.idProducto = p.id
    WHERE d.idFacturaVenta = ?
");
$stmtDet->execute([$id]);
$productos_db = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

$productos = [];
foreach ($productos_db as $p) {
    $productos[] = [
        'id' => $p['idProducto'],
        'codigo' => $p['codigo'],
        'nombre' => $p['productoNombre'],
        'cant' => number_format($p['cantidad'], 2),
        'precio' => number_format($p['precioUnitario'], 2),
        'desc' => number_format($p['descuentoValor'], 2),
        'iva' => number_format($p['ivaValor'], 2),
        'total' => number_format($p['total'], 2)
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura
        <?php echo $factura['numero']; ?> | Warehouse POS
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .invoice-full-container {
            background: #f4f7fa;
            min-height: 100vh;
            padding: 20px;
        }

        .invoice-header-purple {
            background: #6366f1;
            color: white;
            padding: 15px 25px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-header-purple h1 {
            font-size: 1.2rem;
            margin: 0;
            font-weight: 700;
        }

        .invoice-header-purple .meta {
            text-align: right;
        }

        .invoice-header-purple .meta .num {
            font-size: 1.1rem;
            font-weight: 800;
            display: block;
        }

        .invoice-header-purple .meta .date {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        .invoice-top-cards {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin: 20px 0;
        }

        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #edf2f7;
        }

        .info-card h3 {
            font-size: 0.85rem;
            color: #4a5568;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h3 i {
            color: #6366f1;
        }

        .client-info-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
        }

        .info-item {
            margin-bottom: 12px;
        }

        .info-item label {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            color: #1a202c;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-item span {
            font-size: 0.8rem;
            color: #4a5568;
        }

        .status-panel {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .status-badge-big {
            background: #10b981;
            color: white;
            padding: 10px 30px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .vendedor-label {
            font-size: 0.75rem;
            color: #718096;
        }

        .products-table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #edf2f7;
            margin-bottom: 20px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            background: #2d3748;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .products-table td {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.85rem;
        }

        .invoice-footer-row {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .info-blue-box {
            background: #e0f2fe;
            color: #0369a1;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            align-self: flex-start;
        }

        .summary-pink-box {
            background: #f472b6;
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(244, 114, 182, 0.3);
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .summary-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 1.8rem;
            font-weight: 800;
        }

        .actions-bar {
            background: #475569;
            padding: 12px 25px;
            border-radius: 8px;
            margin-top: 30px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .actions-label {
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            margin-right: 10px;
        }

        .btn-action-full {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-print {
            background: #059669;
        }

        .btn-history {
            background: #06b6d4;
        }

        .btn-void {
            background: #ef4444;
        }

        .btn-action-full:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        $current_page = 'ventas';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <div class="invoice-full-container">

                <!-- Purple Header -->
                <div class="invoice-header-purple">
                    <h1>FACTURA DE VENTA</h1>
                    <div class="meta">
                        <span class="num">
                            <?php echo $factura['numero']; ?>
                        </span>
                        <span class="date"><i class="far fa-calendar-alt"></i>
                            <?php echo $factura['fecha']; ?>
                        </span>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="invoice-top-cards">
                    <div class="info-card">
                        <h3><i class="fas fa-user-circle"></i> Datos del Cliente</h3>
                        <div class="client-info-grid">
                            <div>
                                <div class="info-item">
                                    <label>Cliente:</label>
                                    <span>
                                        <?php echo $factura['cliente']; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Teléfono:</label>
                                    <span>
                                        <?php echo $factura['telefono']; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Dirección:</label>
                                    <span>
                                        <?php echo $factura['direccion']; ?>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="info-item">
                                    <label>Identificación:</label>
                                    <span>
                                        <?php echo $factura['identificacion']; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Email:</label>
                                    <span>
                                        <?php echo $factura['email']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-card status-panel">
                        <h3><i class="fas fa-info-circle"></i> Estado de la Factura</h3>
                        <div class="status-badge-big">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $factura['estado']; ?>
                        </div>
                        <span class="vendedor-label">Vendedor:
                            <?php echo $factura['vendedor']; ?>
                        </span>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="products-table-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Producto</th>
                                <th style="text-align: center;">Cantidad</th>
                                <th style="text-align: right;">Precio Unit.</th>
                                <th style="text-align: right;">Descuento</th>
                                <th style="text-align: right;">IVA</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $idx => $p): ?>
                                <tr>
                                    <td>
                                        <?php echo $idx + 1; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;">
                                            <?php echo $p['nombre']; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #718096;">ID:
                                            <?php echo $p['id']; ?> | Código:
                                            <?php echo $p['codigo']; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span
                                            style="background: #22d3ee; color: white; padding: 4px 10px; border-radius: 4px; font-weight: 700;">
                                            <?php echo $p['cant']; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">$
                                        <?php echo $p['precio']; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <?php echo $p['desc']; ?>
                                    </td>
                                    <td style="text-align: right;">$
                                        <?php echo $p['iva']; ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 800;">$
                                        <?php echo $p['total']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary Row -->
                <div class="invoice-footer-row">
                    <div class="info-blue-box">
                        <i class="fas fa-info-circle"></i>
                        <span>Información: Esta factura fue creada el
                            <?php echo $factura['creacion']; ?>
                        </span>
                    </div>
                    <div class="summary-pink-box">
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span>$
                                <?php echo $factura['subtotal']; ?>
                            </span>
                        </div>
                        <div class="summary-line">
                            <span>IVA (15%):</span>
                            <span>$
                                <?php echo $factura['iva']; ?>
                            </span>
                        </div>
                        <div class="summary-line summary-total">
                            <span>TOTAL:</span>
                            <span>$
                                <?php echo $factura['total']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions Bar -->
                <div class="actions-bar">
                    <span class="actions-label"><i class="fas fa-cogs"></i> Acciones</span>
                    <button class="btn-action-full btn-print" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Factura
                    </button>
                    <button class="btn-action-full btn-history"
                        onclick="verHistorial(<?php echo $factura['id_cliente']; ?>)">
                        <i class="fas fa-history"></i> Ver Historial del Cliente
                    </button>
                    <button class="btn-action-full btn-void" onclick="anularFactura(<?php echo $id; ?>)">
                        <i class="fas fa-ban"></i> Anular Factura
                    </button>
                </div>

            </div>
        </main>
    </div>
    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function verHistorial(idCliente) {
            window.location.href = 'index.php?idCliente=' + idCliente;
        }

        async function anularFactura(id) {
            if (!confirm('¿Estás seguro de que deseas anular esta factura? Se generará una Nota de Crédito electrónica.')) {
                return;
            }

            const btn = document.querySelector('.btn-void');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Anulando...';

            try {
                const response = await fetch('api_anular_factura.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const res = await response.json();

                if (res.success) {
                    alert('Factura anulada con éxito. Nota de Crédito generada: ' + (res.nota_credito || 'Pendiente'));
                    window.location.reload();
                } else {
                    alert('Error: ' + res.error);
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión al anular la factura');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
</body>

</html>