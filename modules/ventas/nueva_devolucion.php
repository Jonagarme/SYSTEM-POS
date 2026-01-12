<?php
/**
 * Create New Return - Nueva Devolución
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'ventas_devoluciones';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Devolución | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .search-banner-green {
            background: #10b981;
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-banner-green h2 {
            margin: 0;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-banner-green .search-box {
            display: flex;
            gap: 0;
            max-width: 700px;
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 8px;
        }

        .search-banner-green input {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            padding: 10px 15px;
            font-size: 1rem;
            outline: none;
        }

        .search-banner-green input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-search-inner {
            background: #10b981;
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .return-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .return-section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0369a1;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .return-product-row {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .product-main-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .product-meta h4 {
            font-size: 0.85rem;
            margin: 0;
            color: #1e293b;
        }

        .product-meta span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .return-qty-controls {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .label {
            display: block;
            font-size: 0.65rem;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .stat-item .value-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .stat-item .value-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .return-input-box {
            text-align: right;
        }

        .return-input-box label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .return-input-box input {
            width: 80px;
            text-align: center;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .motivo-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 80px;
        }

        .footer-return-bar {
            position: fixed;
            bottom: 0;
            left: 260px;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .warning-note {
            background: #fff8e1;
            color: #856404;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 60%;
        }

        .return-total-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .total-box-label {
            text-align: right;
        }

        .total-box-label span {
            display: block;
            font-size: 0.8rem;
            color: #64748b;
        }

        .total-box-label strong {
            font-size: 1.4rem;
            color: #c5221f;
        }

        .btn-confirm-return {
            background: #c5221f;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        body.sidebar-collapsed .footer-return-bar {
            left: 70px;
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1
                            style="font-size: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 10px; margin: 0;">
                            <i class="fas fa-plus-circle" style="color: #10b981;"></i> Crear Nueva Devolución
                        </h1>
                        <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">Busque la factura y seleccione
                            los productos a devolver (MODO DEMO)</p>
                    </div>
                    <a href="devoluciones.php" class="btn btn-outline" style="padding: 10px 20px;">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                </div>

                <!-- Search Section -->
                <div class="search-banner-green">
                    <h2><i class="fas fa-search"></i> Buscar Factura para Devolver</h2>
                    <div class="search-box">
                        <input type="text" id="invoice-id"
                            placeholder="Ingrese el número de factura (ej: 001-001-000000017)"
                            value="001-001-000000017">
                        <button class="btn-search-inner" onclick="loadInvoice()"><i class="fas fa-search"></i> Buscar
                            Factura</button>
                    </div>
                    <i class="fas fa-file-invoice" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>

                <div id="return-details" style="display: none;">
                    <div class="return-detail-grid">
                        <div class="pos-panel" style="padding: 20px;">
                            <h3 class="return-section-title"><i class="fas fa-info-circle"></i> Información de la
                                Factura</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.85rem;">
                                <div><strong style="color: #475569;">Número de Factura:</strong></div>
                                <div id="lbl-invoice-no">001-001-000000017</div>
                                <div><strong style="color: #475569;">Fecha de Venta:</strong></div>
                                <div>27/11/2025 00:29</div>
                                <div><strong style="color: #475569;">Total Original:</strong></div>
                                <div style="font-weight: 700;">$ 8.35</div>
                            </div>
                        </div>
                        <div class="pos-panel" style="padding: 20px;">
                            <h3 class="return-section-title"><i class="fas fa-user"></i> Información del Cliente</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.85rem;">
                                <div><strong style="color: #475569;">Nombre:</strong></div>
                                <div>WASHINGTON JORGE DEL ROSARIO CARRERA</div>
                                <div><strong style="color: #475569;">Documento:</strong></div>
                                <div>0920259256</div>
                            </div>
                        </div>
                    </div>

                    <h3 class="return-section-title" style="color: #1e293b;"><i class="fas fa-boxes"></i> Productos
                        Disponibles para Devolución</h3>
                    <div
                        style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                        <div class="return-product-row">
                            <div class="product-main-info">
                                <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;">
                                <div class="product-meta">
                                    <h4>VITAMINA C NARANJA 500MG SX12 TAB MAST - LASANTE</h4>
                                    <span>Código: 1779 | Precio unitario: $ 0.38</span>
                                </div>
                            </div>
                            <div class="return-qty-controls">
                                <div class="stat-item">
                                    <span class="label">Cant. Original</span>
                                    <span class="value-badge">3.00</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Disponible</span>
                                    <span class="value-badge-green value-badge">3.00</span>
                                </div>
                                <div class="return-input-box">
                                    <label>Devolver:</label>
                                    <input type="number" value="1" min="0" max="3">
                                    <div style="font-size: 0.7rem; color: #15803d; font-weight: 700; margin-top: 4px;">
                                        Subtotal disponible: $ 0.74</div>
                                </div>
                            </div>
                        </div>
                        <div class="return-product-row">
                            <div class="product-main-info">
                                <input type="checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                                <div class="product-meta">
                                    <h4>VITAMINA C FRESA SOL ORAL GOTAS X30ML - MK</h4>
                                    <span>Código: 1778 | Precio unitario: $ 2.34</span>
                                </div>
                            </div>
                            <div class="return-qty-controls">
                                <div class="stat-item">
                                    <span class="label">Cant. Original</span>
                                    <span class="value-badge">3.00</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Disponible</span>
                                    <span class="value-badge-green value-badge">3.00</span>
                                </div>
                                <div class="return-input-box">
                                    <label>Devolver:</label>
                                    <input type="number" value="1" min="0" max="3">
                                    <div style="font-size: 0.7rem; color: #15803d; font-weight: 700; margin-top: 4px;">
                                        Subtotal disponible: $ 7.02</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="motivo-box">
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                            <div>
                                <h3 class="return-section-title" style="color: #1e293b;"><i
                                        class="fas fa-comment-dots"></i> Motivo de la Devolución</h3>
                                <label style="display: block; font-size: 0.8rem; margin-bottom: 8px;">Motivo *</label>
                                <select class="form-control">
                                    <option>Seleccione un motivo</option>
                                    <option>Producto Dañado</option>
                                    <option>Vencimiento Próximo</option>
                                    <option>Error de Compra</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    style="display: block; font-size: 0.8rem; margin-bottom: 8px; font-weight: 700;">Observaciones</label>
                                <textarea class="form-control" rows="3"
                                    placeholder="Detalles adicionales sobre la devolución (opcional)"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="footer-return-bar">
                        <div class="warning-note">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Importante: Los productos devueltos serán restituidos al inventario automáticamente.
                                Esta acción no se puede deshacer una vez procesada.</span>
                        </div>
                        <div class="return-total-section">
                            <div class="total-box-label">
                                <span>Total a Devolver</span>
                                <strong>$ 0.74</strong>
                            </div>
                            <button class="btn-confirm-return" onclick="processReturn()">
                                <i class="fas fa-check"></i> PROCESAR DEVOLUCIÓN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function loadInvoice() {
            const id = document.getElementById('invoice-id').value;
            if (id) {
                document.getElementById('lbl-invoice-no').innerText = id;
                document.getElementById('return-details').style.display = 'block';
                window.scrollTo({ top: 500, behavior: 'smooth' });
            }
        }

        function processReturn() {
            Swal.fire({
                title: '¿Confirmar Devolución?',
                text: "Los productos seleccionados volverán al inventario.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c5221f',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        '¡Procesado!',
                        'La devolución se ha registrado correctamente.',
                        'success'
                    ).then(() => {
                        window.location.href = 'devoluciones.php';
                    });
                }
            })
        }
    </script>
</body>

</html>