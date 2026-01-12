<?php
/**
 * View Sales History - Ventas Realizadas
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'ventas';

$search = $_GET['search'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '2025-01-01';
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$where = "WHERE f.anulado = 0 AND DATE(f.fechaEmision) BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];

if (!empty($search)) {
    $where .= " AND (f.numeroFactura LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ? OR u.nombreUsuario LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Fetch sales with client and user info
$stmt = $pdo->prepare("
    SELECT f.*, 
           CONCAT(c.nombres, ' ', c.apellidos) as cliente_nombre,
           u.nombreUsuario as vendedor_nombre
    FROM facturas_venta f
    LEFT JOIN clientes c ON f.idCliente = c.id
    LEFT JOIN usuarios u ON f.idUsuario = u.id
    $where
    ORDER BY f.fechaEmision DESC
    LIMIT 100
");
$stmt->execute($params);
$ventas = $stmt->fetchAll();

$ventas_count = count($ventas);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas Realizadas | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/productos_grid.css">
    <style>
        .sales-header-banner {
            background: #10b981;
            color: white;
            padding: 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }

        .sales-header-banner h1 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sales-header-banner p {
            margin: 5px 0 0 37px;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .filter-header-item {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 5px;
            display: block;
        }

        .sales-table {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            box-shadow: var(--shadow-sm);
            border-collapse: collapse;
            overflow: hidden;
        }

        .sales-table th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid #f1f5f9;
        }

        .sales-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .badge-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .badge-pagada {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-factura {
            background: #fef3c7;
            color: #92400e;
            font-size: 0.65rem;
        }

        .btn-table-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view {
            background: #e0f2fe;
            color: #0369a1;
        }

        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-pdf {
            background: #eef2ff;
            color: #3730a3;
        }

        /* Modal specific for sale details */
        .sale-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .detail-section h4 {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }

        .detail-info-row label {
            font-weight: 700;
            color: #1e293b;
        }

        /* Modal Table Styling */
        .modal-body .cart-table,
        .modal-body .detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            overflow: hidden;
        }

        .modal-body .cart-table th,
        .modal-body .detail-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            padding: 12px 15px;
            font-weight: 700;
            border-bottom: 2px solid #f1f5f9;
        }

        .modal-body .cart-table td,
        .modal-body .detail-table td {
            padding: 12px 15px;
            font-size: 0.85rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .modal-body tr:last-child td {
            border-bottom: none;
        }

        .modal-body tr:nth-child(even) {
            background-color: #fbfcfe;
        }

        .col-price {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
            color: #0f172a;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 992px) {
            .sale-detail-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .sales-header-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .pos-panel .panel-body form div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }

            .pos-panel .panel-body div[style*="justify-content: flex-end"] {
                justify-content: center !important;
            }

            .content-wrapper {
                padding: 15px;
            }

            /* Contenedor para scroll de tabla */
            .table-responsive-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 12px;
                box-shadow: var(--shadow-sm);
            }

            .sales-table {
                min-width: 800px;
                /* Forzar scroll horizontal en móviles */
            }

            .sales-header-banner p {
                margin-left: 0;
            }
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
            <?php include $root . 'includes/navbar.php'; ?>

            <div class="content-wrapper">

                <!-- Banner Title -->
                <div class="sales-header-banner">
                    <div>
                        <h1><i class="fas fa-file-invoice-dollar"></i> Ventas Realizadas</h1>
                        <p>Historial y consulta de ventas realizadas</p>
                    </div>
                    <div
                        style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 6px; font-weight: 700;">
                        <i class="fas fa-list"></i> <?php echo $ventas_count; ?> ventas encontradas
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="pos-panel" style="margin-bottom: 25px;">
                    <div class="panel-body">
                        <form method="GET" action="">
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                                <div>
                                    <label class="filter-header-item">Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control"
                                        value="<?php echo $fecha_inicio; ?>">
                                </div>
                                <div>
                                    <label class="filter-header-item">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control"
                                        value="<?php echo $fecha_fin; ?>">
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="filter-header-item">Buscar por Cliente, Número o Vendedor</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Factura, cliente, vendedor..."
                                            value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i>
                                            Buscar</button>
                                        <a href="index.php" class="btn btn-outline"><i class="fas fa-sync"></i>
                                            Reajustar</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div
                            style="display: flex; justify-content: flex-end; margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                            <a href="pos.php" class="btn btn-primary"
                                style="background: #10b981; border-color: #10b981;">
                                <i class="fas fa-plus"></i> Nueva Venta
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="table-responsive-container">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Número Venta</th>
                                <th>Cliente</th>
                                <th>Fecha Venta</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Método Pago</th>
                                <th>Tipo</th>
                                <th>Vendedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $v): ?>
                                <tr>
                                    <td>
                                        <?php echo $v['id']; ?>
                                    </td>
                                    <td style="font-weight: 700; color: #0061f2;">
                                        <?php echo $v['numeroFactura']; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($v['cliente_nombre'] ?? 'CONSUMIDOR FINAL'); ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($v['fechaEmision'])); ?>
                                    </td>
                                    <td style="font-weight: 800;">$
                                        <?php echo number_format($v['total'], 2); ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge-status <?php echo $v['estado'] == 'PAGADA' ? 'badge-pagada' : ''; ?>">
                                            <?php echo $v['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        ELECTRONICO
                                    </td>
                                    <td>
                                        <span class="badge-status badge-factura"> FACTURA </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($v['vendedor_nombre'] ?? 'Admin'); ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button class="btn-table-action btn-view"
                                                onclick="showSaleDetail(<?php echo $v['id']; ?>)"
                                                title="Ver Detalle Rápido"><i class="fas fa-eye"></i></button>
                                            <a href="ver_factura.php?id=<?php echo $v['id']; ?>"
                                                class="btn-table-action btn-pdf" title="Ver Factura Completa"><i
                                                    class="fas fa-file-invoice"></i></a>
                                            <button class="btn-table-action btn-delete" title="Anular"><i
                                                    class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: DETALLE DE VENTA -->
    <div class="modal-overlay" id="modal-detalle-venta">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2><i class="fas fa-list-alt"></i> Detalle de Venta</h2>
                <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="sale-detail-grid">
                    <!-- Sale Info -->
                    <div class="detail-section">
                        <h4><i class="fas fa-file-invoice"></i> Información de la Venta</h4>
                        <div class="detail-info-row"><label>Número:</label><span>001-001-000000018</span></div>
                        <div class="detail-info-row"><label>Fecha:</label><span>2025-12-03 02:28:52</span></div>
                        <div class="detail-info-row"><label>Estado:</label><span>PAGADA</span></div>
                        <div class="detail-info-row"><label>Método Pago:</label><span class="badge-primary"
                                style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #e0f2fe; color: #0369a1;">EFECTIVO</span>
                        </div>
                        <div class="detail-info-row"><label>Vendedor:</label><span>Sistema POS</span></div>
                    </div>
                    <!-- Client Info -->
                    <div class="detail-section">
                        <h4><i class="fas fa-user"></i> Información del Cliente</h4>
                        <div class="detail-info-row"><label>Cliente:</label><span>AARON NATANAEL FARIAS TEJADA</span>
                        </div>
                        <div class="detail-info-row"><label>Identificación:</label><span>0951543594</span></div>
                        <div class="detail-info-row"><label>Dirección:</label><span>GUAYAS/GUAYAQUIL/FEBRES CORDERO III
                                CLLJN Q ENTRE 26 Y 27 NN</span></div>
                        <div class="detail-info-row"><label>Teléfono:</label><span>N/A</span></div>
                    </div>
                </div>

                <div style="border-top: 1px solid #edf2f7; margin: 20px 0;"></div>
                <h4 style="font-size: 0.8rem; color: #64748b; margin-bottom: 15px;"><i class="fas fa-box-open"></i>
                    Detalle de Productos</h4>

                <table class="cart-table" style="border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="color: #1e293b; background: #f8fafc;">Código</th>
                            <th style="color: #1e293b; background: #f8fafc;">Descripción</th>
                            <th style="color: #1e293b; background: #f8fafc; text-align: center;">Cant.</th>
                            <th style="color: #1e293b; background: #f8fafc; text-align: right;">P. Unit.</th>
                            <th style="color: #1e293b; background: #f8fafc; text-align: right;">Descuento</th>
                            <th style="color: #1e293b; background: #f8fafc; text-align: right;">Subtotal</th>
                            <th style="color: #1e293b; background: #f8fafc; text-align: right;">IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>7861001363982</td>
                            <td>CREMA PARA PEINAR - SAVITAL MULTIVITAMINAS Y SABILA 22ML</td>
                            <td style="text-align: center;">2</td>
                            <td style="text-align: right;">$3.00</td>
                            <td style="text-align: right;">$0.00</td>
                            <td style="text-align: right;">$6.90</td>
                            <td style="text-align: right;">$0.90</td>
                        </tr>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: flex-end; margin-top: 25px;">
                    <div class="summary-box"
                        style="width: 250px; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #eef2f7;">
                        <div class="detail-info-row" style="margin-bottom: 8px;">
                            <label>Subtotal:</label><span>$6.00</span>
                        </div>
                        <div class="detail-info-row" style="margin-bottom: 8px;">
                            <label>Descuento:</label><span>$0.00</span>
                        </div>
                        <div class="detail-info-row" style="margin-bottom: 12px;"><label>IVA:</label><span>$0.90</span>
                        </div>
                        <div class="detail-info-row"
                            style="border-top: 2px solid #10b981; padding-top: 10px; font-size: 1.1rem; color: #1e293b;">
                            <label style="font-weight: 800;">TOTAL:</label><span style="font-weight: 800;">$6.90</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cerrar</button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const modalDetalle = document.getElementById('modal-detalle-venta');
        const btnsClose = document.querySelectorAll('.close-modal');

        async function showSaleDetail(id) {
            modalDetalle.style.display = 'flex';
            const body = modalDetalle.querySelector('.modal-body');
            body.style.opacity = '0.5';

            try {
                const resp = await fetch(`get_venta_detalle.php?id=${id}`);

                if (!resp.ok) {
                    const errText = await resp.text();
                    throw new Error(`Error en el servidor (${resp.status}): ${errText}`);
                }

                const data = await resp.json();

                if (!data.success) {
                    alert('Error: ' + data.error);
                    modalDetalle.style.display = 'none';
                    return;
                }

                const v = data.venta;
                // Fill header info
                body.querySelector('.sale-detail-grid').innerHTML = `
                    <div class="detail-section">
                        <h4><i class="fas fa-file-invoice"></i> Información de la Venta</h4>
                        <div class="detail-info-row"><label>Número:</label><span>${v.numeroFactura}</span></div>
                        <div class="detail-info-row"><label>Fecha:</label><span>${v.fechaEmision}</span></div>
                        <div class="detail-info-row"><label>Estado:</label><span>${v.estado}</span></div>
                        <div class="detail-info-row"><label>Vendedor:</label><span>${v.vendedor_nombre || 'Admin'}</span></div>
                    </div>
                    <div class="detail-section">
                        <h4><i class="fas fa-user"></i> Información del Cliente</h4>
                        <div class="detail-info-row"><label>Cliente:</label><span>${v.cliente_nombre || 'CONSUMIDOR FINAL'}</span></div>
                        <div class="detail-info-row"><label>Identificación:</label><span>${v.cliente_ruc || '9999999999999'}</span></div>
                        <div class="detail-info-row"><label>Dirección:</label><span>${v.cliente_direccion || 'N/A'}</span></div>
                    </div>
                `;

                // Fill products table
                let tableHtml = '';
                data.detalles.forEach(d => {
                    tableHtml += `
                        <tr>
                            <td style="color: #64748b; font-size: 0.75rem;">${d.idProducto || 'N/A'}</td>
                            <td style="font-weight: 500;">${d.productoNombre}</td>
                            <td style="text-align: center; font-weight: 600;">${parseFloat(d.cantidad).toFixed(2)}</td>
                            <td style="text-align: right;" class="col-price">$${parseFloat(d.precioUnitario).toFixed(4)}</td>
                            <td style="text-align: right; color: #ef4444;">$${parseFloat(d.descuentoValor).toFixed(2)}</td>
                            <td style="text-align: right; font-weight: 600;">$${(parseFloat(d.total) - parseFloat(d.ivaValor)).toFixed(2)}</td>
                            <td style="text-align: right; color: #64748b;">$${parseFloat(d.ivaValor).toFixed(2)}</td>
                        </tr>
                    `;
                });
                body.querySelector('.cart-table tbody').innerHTML = tableHtml;

                // Fill totals
                body.querySelector('.summary-box').innerHTML = `
                    <div class="detail-info-row" style="margin-bottom: 8px;">
                        <label>Subtotal:</label><span>$${parseFloat(v.subtotal).toFixed(2)}</span>
                    </div>
                    <div class="detail-info-row" style="margin-bottom: 8px;">
                        <label>Descuento:</label><span>$${parseFloat(v.descuento).toFixed(2)}</span>
                    </div>
                    <div class="detail-info-row" style="margin-bottom: 12px;"><label>IVA:</label><span>$${parseFloat(v.iva).toFixed(2)}</span>
                    </div>
                    <div class="detail-info-row" style="border-top: 2px solid #10b981; padding-top: 10px; font-size: 1.1rem; color: #1e293b;">
                        <label style="font-weight: 800;">TOTAL:</label><span style="font-weight: 800;">$${parseFloat(v.total).toFixed(2)}</span>
                    </div>
                `;

                body.style.opacity = '1';
            } catch (e) {
                console.error(e);
                alert('Error al cargar detalles: ' + e.message);
            }
        }

        btnsClose.forEach(btn => btn.onclick = () => modalDetalle.style.display = 'none');
    </script>
</body>

</html>