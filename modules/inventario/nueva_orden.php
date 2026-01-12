<?php
/**
 * New Purchase Order Form - Nueva Orden de Compra
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ordenes';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Orden de Compra | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .no-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .no-title h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0;
            font-weight: 700;
        }

        .no-breadcrumb {
            display: flex;
            gap: 5px;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 5px;
        }

        .no-breadcrumb a {
            color: #2563eb;
        }

        .btn-back-no {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .no-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .no-panel-header {
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bg-blue {
            background: #2563eb;
        }

        .bg-green {
            background: #198754;
        }

        .no-panel-body {
            padding: 25px;
        }

        .form-grid-2-no {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid-3-no {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .form-group-no label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .dashed-dropzone {
            border: 2px dashed #2563eb;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: #2563eb;
            cursor: pointer;
            transition: all 0.2s;
            background: #f0f7ff;
        }

        .dashed-dropzone:hover {
            background: #e0efff;
        }

        .dashed-dropzone i {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
        }

        .info-note {
            background: #e0f2fe;
            color: #0369a1;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            max-width: 600px;
        }

        .total-panel {
            background: #22c55e;
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: right;
            margin-left: auto;
            width: 300px;
            box-shadow: var(--shadow-sm);
        }

        .total-panel .val {
            font-size: 1.8rem;
            font-weight: 800;
            display: block;
        }

        .total-panel .lbl {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .no-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
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
                <div class="no-header">
                    <div class="no-title">
                        <h1>Nueva Orden de Compra</h1>
                        <div class="no-breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="../inventario/kardex.php">Inventario</a>
                            / <a href="ordenes_compra.php">Órdenes de Compra</a> / <span>Nueva Orden</span>
                        </div>
                    </div>
                    <a href="ordenes_compra.php" class="btn-back-no">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Section: General Info -->
                <div class="no-panel">
                    <div class="no-panel-header bg-blue">
                        <span><i class="fas fa-info-circle"></i> Información General</span>
                    </div>
                    <div class="no-panel-body">
                        <div class="form-grid-2-no">
                            <div class="form-group-no">
                                <label>Proveedor *</label>
                                <select class="form-control">
                                    <option>Seleccionar proveedor...</option>
                                </select>
                            </div>
                            <div class="form-group-no">
                                <label>Ubicación Destino *</label>
                                <select class="form-control">
                                    <option>Seleccionar ubicación...</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-grid-3-no">
                            <div class="form-group-no">
                                <label>Fecha Entrega Esperada</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="form-group-no">
                                <label>Prioridad</label>
                                <select class="form-control">
                                    <option>Normal</option>
                                    <option>Urgente</option>
                                    <option>Baja</option>
                                </select>
                            </div>
                            <div class="form-group-no">
                                <label>Observaciones</label>
                                <textarea class="form-control" rows="1"
                                    placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Products -->
                <div class="no-panel">
                    <div class="no-panel-header bg-green">
                        <span><i class="fas fa-boxes"></i> Productos</span>
                        <button class="btn btn-outline"
                            style="color: white; border-color: white; padding: 4px 12px; font-size: 0.75rem;">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                    <div class="no-panel-body">
                        <div class="dashed-dropzone" onclick="openProductModal()">
                            <i class="fas fa-plus-circle"></i>
                            <strong>Agregar Primer Producto</strong>
                        </div>
                    </div>
                </div>

                <div class="no-footer">
                    <div class="info-note">
                        <i class="fas fa-info-circle"></i> <strong>Nota:</strong> La orden se creará en estado
                        "Borrador" y podrá ser enviada al proveedor posteriormente.
                    </div>
                    <div class="total-panel">
                        <div class="lbl">Total Estimado</div>
                        <div class="val">$ 0.00</div>
                        <div class="lbl">0 productos</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: SELECCIONAR PRODUCTO (Based on image 3) -->
    <div class="modal-overlay" id="product-modal">
        <div class="modal-content" style="max-width: 900px; padding: 0; overflow: hidden;">
            <div class="modal-header" style="padding: 15px 20px; border-bottom: 2px solid #2563eb;">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #1e293b;">Seleccionar Producto</h2>
                <button class="btn-text" onclick="closeProductModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <input type="text" class="form-control" placeholder="Buscar producto por nombre o código..."
                    style="margin-bottom: 15px;">

                <table class="table-simple" style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; text-align: left;">
                            <th style="padding: 10px;">CÓDIGO</th>
                            <th style="padding: 10px;">DESCRIPCIÓN</th>
                            <th style="padding: 10px; text-align: right;">STOCK</th>
                            <th style="padding: 10px; text-align: right;">PRECIO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer;">
                            <td style="padding: 12px; font-weight: 700;">CLORUROJUVENC</td>
                            <td style="padding: 12px;">***CLORURO SODIO 0,9% DE 100ML JUVENCIA</td>
                            <td style="padding: 12px; text-align: right;">0,00</td>
                            <td style="padding: 12px; text-align: right;">$ 0,00</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer;">
                            <td style="padding: 12px; font-weight: 700;">76313</td>
                            <td style="padding: 12px;">*LACTOFAES BEBE GOTAS(3025)</td>
                            <td style="padding: 12px; text-align: right;">0,00</td>
                            <td style="padding: 12px; text-align: right;">$ 16,52</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer;">
                            <td style="padding: 12px; font-weight: 700;">7862117789215</td>
                            <td style="padding: 12px;">+BIOTINA*COLAGENO HIDROLIZADO+ZINC+VITAMINAS A,E,D3,C,B...</td>
                            <td style="padding: 12px; text-align: right;">0,00</td>
                            <td style="padding: 12px; text-align: right;">$ 9,98</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function openProductModal() {
            document.getElementById('product-modal').style.display = 'flex';
        }
        function closeProductModal() {
            document.getElementById('product-modal').style.display = 'none';
        }
    </script>
</body>

</html>