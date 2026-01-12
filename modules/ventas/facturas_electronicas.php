<?php
/**
 * Electronic Invoices - Facturas Electrónicas
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'ventas_facturas';

$search = $_GET['search'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '2025-01-01';
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$where = "WHERE f.anulado = 0 AND DATE(f.fechaEmision) BETWEEN ? AND ?";
$params = [$fecha_inicio, $fecha_fin];

if (!empty($search)) {
    $where .= " AND (f.numeroFactura LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ? OR f.numeroAutorizacion LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Fetch invoices with client info
$stmt = $pdo->prepare("
    SELECT f.*, 
           CONCAT(c.nombres, ' ', c.apellidos) as cliente_nombre
    FROM facturas_venta f
    LEFT JOIN clientes c ON f.idCliente = c.id
    $where
    ORDER BY f.fechaEmision DESC
    LIMIT 100
");
$stmt->execute($params);
$facturas = $stmt->fetchAll();

$facturas_count = count($facturas);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas Electrónicas | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .ef-header-banner {
            background: #6366f1;
            color: white;
            padding: 20px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .ef-header-banner h1 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ef-header-banner p {
            margin: 5px 0 0 37px;
            opacity: 0.8;
            font-size: 0.85rem;
        }

        .ef-filter-panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
        }

        .ef-filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr 150px;
            gap: 20px;
            align-items: flex-end;
        }

        .ef-filter-grid label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .quick-search-box {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .ef-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border-collapse: collapse;
        }

        .ef-table th {
            text-align: left;
            padding: 15px 20px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            border-bottom: 1px solid #f1f5f9;
        }

        .ef-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #475569;
        }

        .ef-table tr:hover {
            background: #f8fafc;
        }

        .badge-pagada {
            background: #dcfce7;
            color: #15803d;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .action-btns {
            display: flex;
            gap: 5px;
        }

        .btn-action {
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

        .btn-eye {
            background: #22d3ee;
            color: white;
        }

        .btn-print {
            background: #64748b;
            color: white;
        }

        .btn-dots {
            background: #cbd5e1;
            color: #475569;
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 180px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.1);
            z-index: 100;
            border-radius: 8px;
            padding: 8px 0;
            border: 1px solid #e2e8f0;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-item {
            padding: 10px 15px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f8fafc;
        }

        .dropdown-item.danger {
            color: #ef4444;
        }

        /* Detail Modal Table */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .detail-table th {
            text-align: left;
            font-size: 0.75rem;
            color: #64748b;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
        }

        .detail-table td {
            padding: 12px 0;
            font-size: 0.85rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .summary-box {
            width: 250px;
            font-size: 0.85rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .summary-total {
            background: #dbeafe;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-weight: 800;
            color: #1e293b;
        }

        /* Modal Table Styling */
        .modal-body .detail-table,
        .modal-body .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            overflow: hidden;
        }

        .modal-body .detail-table th,
        .modal-body .cart-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            padding: 12px 15px;
            font-weight: 700;
            border-bottom: 2px solid #f1f5f9;
        }

        .modal-body .detail-table td,
        .modal-body .cart-table td {
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
    </style>
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>

            <div class="content-wrapper">
                <div class="ef-header-banner">
                    <div>
                        <h1><i class="fas fa-file-invoice"></i> Facturas Electrónicas</h1>
                        <p>Gestión y consulta de facturas electrónicas</p>
                    </div>
                    <div
                        style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">
                        <i class="fas fa-file-alt"></i> <?php echo $facturas_count; ?> facturas encontradas
                    </div>
                </div>

                <div class="ef-filter-panel">
                    <form method="GET" action="" class="ef-filter-grid">
                        <div>
                            <label>Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control"
                                value="<?php echo $fecha_inicio; ?>">
                        </div>
                        <div>
                            <label>Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                        </div>
                        <div>
                            <label>Buscar por Cliente, Número o Autorización</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Nombre del cliente, número de factura..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary"
                            style="height: 42px; width: 100%; border-radius: 8px;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </form>
                </div>

                <table class="ef-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número Factura</th>
                            <th>Cliente</th>
                            <th>Fecha Emisión</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Autorización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facturas as $f): ?>
                            <tr>
                                <td>
                                    <?php echo $f['id']; ?>
                                </td>
                                <td style="font-weight: 700; color: #2563eb;">
                                    <?php echo $f['numeroFactura']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($f['cliente_nombre'] ?? 'CONSUMIDOR FINAL'); ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($f['fechaEmision'])); ?>
                                </td>
                                <td style="font-weight: 800;">$
                                    <?php echo number_format($f['total'], 2); ?>
                                </td>
                                <td><span class="badge-pagada">
                                        <?php echo $f['estado']; ?>
                                    </span></td>
                                <td style="font-size: 0.75rem; color: #64748b;">
                                    <?php echo $f['numeroAutorizacion'] ?: 'Sin autorización'; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-action btn-eye"
                                            onclick="showInvoiceDetail(<?php echo $f['id']; ?>)"><i
                                                class="fas fa-eye"></i></button>
                                        <button class="btn-action btn-print"><i class="fas fa-print"></i></button>
                                        <div class="dropdown">
                                            <button class="btn-action btn-dots"><i class="fas fa-ellipsis-v"></i></button>
                                            <div class="dropdown-content">
                                                <div class="dropdown-item"><i class="fas fa-paper-plane"
                                                        style="color: #3b82f6;"></i> Enviar al SRI</div>
                                                <div class="dropdown-item danger"><i class="fas fa-times-circle"></i> Anular
                                                    Factura</div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL: DETALLE DE FACTURA -->
    <div class="modal-overlay" id="modal-invoice-detail">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> Detalle de Factura</h2>
                <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="invoice-header-grid"
                    style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; margin-bottom: 25px;">
                    <div>
                        <h4
                            style="font-size: 0.8rem; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> Información de la Factura
                        </h4>
                        <div style="display: grid; grid-template-columns: 120px 1fr; row-gap: 8px; font-size: 0.85rem;">
                            <strong>Número:</strong> <span>001-001-000000018</span>
                            <strong>Fecha:</strong> <span>03/12/2025 02:28</span>
                            <strong>Estado:</strong> <span class="badge-pagada"
                                style="width: fit-content;">PAGADA</span>
                            <strong>Autorización:</strong> <span style="color: #64748b;">Sin autorización</span>
                            <strong>Método Pago:</strong> <span>N/A</span>
                        </div>
                    </div>
                    <div>
                        <h4
                            style="font-size: 0.8rem; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fas fa-user"></i> Información del Cliente
                        </h4>
                        <div style="display: grid; grid-template-columns: 120px 1fr; row-gap: 8px; font-size: 0.85rem;">
                            <strong>Cliente:</strong> <span>AARON NATANAEL FARIAS TEJADA</span>
                            <strong>Identificación:</strong> <span>0951543594</span>
                            <strong>Dirección:</strong> <span>GUAYAS/GUAYAQUIL/FEBRES CORDERO III CLLJN Q ENTRE 26 Y 27
                                NN</span>
                            <strong>Teléfono:</strong> <span>099</span>
                        </div>
                    </div>
                </div>

                <h4
                    style="font-size: 0.8rem; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
                    <i class="fas fa-list"></i> Detalle de Productos
                </h4>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th style="text-align: center;">Cant.</th>
                            <th style="text-align: right;">P. Unit.</th>
                            <th style="text-align: right;">Descuento</th>
                            <th style="text-align: right;">Subtotal</th>
                            <th style="text-align: right;">IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>7861001363982</td>
                            <td>CREMA PARA PEINAR - SAVITAL MULTIVITAMINAS Y SABILA 22ML</td>
                            <td style="text-align: center;">2.00</td>
                            <td style="text-align: right;">$3.00</td>
                            <td style="text-align: right;">$0.00</td>
                            <td style="text-align: right;">$6.00</td>
                            <td style="text-align: right;">$0.90</td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary-row">
                    <div class="summary-box">
                        <div class="summary-item"><span>Subtotal:</span> <strong>$6.00</strong></div>
                        <div class="summary-item"><span>Descuento:</span> <strong>$0.00</strong></div>
                        <div class="summary-item"><span>IVA:</span> <strong>$0.90</strong></div>
                        <div class="summary-total">
                            <div class="summary-item" style="font-size: 1.1rem;"><span>TOTAL:</span> <span>$6.90</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
                    <button class="btn"
                        style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700;">
                        <i class="fas fa-times-circle"></i> Anular Factura (Generar Nota de Crédito)
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary close-modal"
                    style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; background: #64748b; color: white; font-weight: 600;">Cerrar</button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const modal = document.getElementById('modal-invoice-detail');
        const closes = document.querySelectorAll('.close-modal');

        async function showInvoiceDetail(id) {
            modal.style.display = 'flex';
            const body = modal.querySelector('.modal-body');
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
                    modal.style.display = 'none';
                    return;
                }

                const v = data.venta;
                // Update header sections
                body.querySelector('.invoice-header-grid').innerHTML = `
                    <div>
                        <h4 style="font-size: 0.8rem; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> Información de la Factura</h4>
                        <div style="display: grid; grid-template-columns: 120px 1fr; row-gap: 8px; font-size: 0.85rem;">
                            <strong>Número:</strong> <span>${v.numeroFactura}</span>
                            <strong>Fecha:</strong> <span>${v.fechaEmision}</span>
                            <strong>Estado:</strong> <span class="badge-pagada" style="width: fit-content;">${v.estado}</span>
                            <strong>Autorización:</strong> <span style="color: #64748b;">${v.numeroAutorizacion || 'Sin autorización'}</span>
                            <strong>Vendedor:</strong> <span>${v.vendedor_nombre || 'Admin'}</span>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-size: 0.8rem; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fas fa-user"></i> Información del Cliente</h4>
                        <div style="display: grid; grid-template-columns: 120px 1fr; row-gap: 8px; font-size: 0.85rem;">
                            <strong>Cliente:</strong> <span>${v.cliente_nombre || 'CONSUMIDOR FINAL'}</span>
                            <strong>Identificación:</strong> <span>${v.cliente_ruc || '9999999999999'}</span>
                            <strong>Dirección:</strong> <span>${v.cliente_direccion || 'N/A'}</span>
                            <strong>Teléfono:</strong> <span>${v.cliente_telefono || 'N/A'}</span>
                        </div>
                    </div>
                `;

                // Products
                let rows = '';
                data.detalles.forEach(d => {
                    rows += `
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
                body.querySelector('.detail-table tbody').innerHTML = rows;

                // Summary
                body.querySelector('.summary-box').innerHTML = `
                    <div class="summary-item"><span>Subtotal:</span> <strong>$${parseFloat(v.subtotal).toFixed(2)}</strong></div>
                    <div class="summary-item"><span>Descuento:</span> <strong>$${parseFloat(v.descuento).toFixed(2)}</strong></div>
                    <div class="summary-item"><span>IVA:</span> <strong>$${parseFloat(v.iva).toFixed(2)}</strong></div>
                    <div class="summary-total">
                        <div class="summary-item" style="font-size: 1.1rem;"><span>TOTAL:</span> <span>$${parseFloat(v.total).toFixed(2)}</span></div>
                    </div>
                `;

                body.style.opacity = '1';
            } catch (e) {
                console.error(e);
                alert('Error al cargar detalles: ' + e.message);
            }
        }

        closes.forEach(btn => btn.onclick = () => modal.style.display = 'none');
    </script>
</body>

</html>