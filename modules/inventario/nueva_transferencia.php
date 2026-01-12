<?php
/**
 * New Stock Transfer Form - Nueva Transferencia
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_transferencias';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Transferencia | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .nt-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back-trans {
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

        .nt-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .nt-panel-header {
            background: #6366f1;
            color: white;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nt-panel-body {
            padding: 25px;
        }

        .form-grid-3-nt {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-grid-2-nt {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group-nt label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .btn-add-p-trans {
            background: white;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: auto;
        }

        .empty-state-p {
            padding: 60px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty-state-p i {
            display: block;
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }

        .nt-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-create-nt {
            background: #6366f1;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            opacity: 0.8;
        }

        .btn-cancel-nt {
            background: #64748b;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Modal Product Search Styles */
        .search-item-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .search-item-info {
            flex: 1;
        }

        .search-item-info h4 {
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .search-item-info span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .search-item-stats {
            text-align: right;
            margin-right: 20px;
        }

        .stat-badge-s {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            display: block;
            margin-bottom: 5px;
        }

        .price-badge-s {
            background: #f1fdf1;
            border: 1px solid #dcfce7;
            color: #166534;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .btn-add-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
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
                <div class="nt-header">
                    <h1><i class="fas fa-exchange-alt"></i> Nueva Transferencia de Stock</h1>
                    <a href="transferencias.php" class="btn-back-trans">
                        <i class="fas fa-arrow-left"></i> Volver a Transferencias
                    </a>
                </div>

                <div class="nt-panel">
                    <div class="nt-panel-header">
                        <i class="fas fa-info-circle"></i> Información General
                    </div>
                    <div class="nt-panel-body">
                        <div class="form-grid-3-nt">
                            <div class="form-group-nt">
                                <label>Ubicación Origen *</label>
                                <select class="form-control">
                                    <option>Seleccione ubicación origen...</option>
                                    <option>Bodega Principal</option>
                                    <option>Sucursal Centro</option>
                                </select>
                            </div>
                            <div class="form-group-nt">
                                <label>Ubicación Destino *</label>
                                <select class="form-control">
                                    <option>Seleccione ubicación destino...</option>
                                    <option>Sucursal Centro</option>
                                    <option>Sucursal Norte</option>
                                </select>
                            </div>
                            <div class="form-group-nt">
                                <label>Fecha de Transferencia *</label>
                                <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="form-grid-2-nt">
                            <div class="form-group-nt">
                                <label>Motivo de Transferencia *</label>
                                <select class="form-control">
                                    <option>Seleccione un motivo...</option>
                                    <option>Reposición de Stock</option>
                                    <option>Pedido de Sucursal</option>
                                    <option>Devolución a Bodega</option>
                                </select>
                            </div>
                            <div class="form-group-nt">
                                <label>Observaciones</label>
                                <textarea class="form-control" rows="1"
                                    placeholder="Observaciones adicionales sobre la transferencia..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nt-panel">
                    <div class="nt-panel-header">
                        <i class="fas fa-boxes"></i> Productos a Transferir
                        <button class="btn-add-p-trans" onclick="openProductModal()">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                    <div class="nt-panel-body" style="padding: 0;">
                        <div class="empty-state-p">
                            <i class="fas fa-box-open"></i>
                            <h4 style="color: #1e293b; margin-bottom: 5px;">No hay productos agregados</h4>
                            <p style="font-size: 0.85rem;">Agregue productos para transferir usando el botón "Agregar
                                Producto"</p>
                        </div>
                    </div>
                </div>

                <div class="nt-footer">
                    <a href="transferencias.php" class="btn-cancel-nt">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button class="btn-create-nt">
                        <i class="fas fa-save"></i> Crear Transferencia
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: AGREGAR PRODUCTO -->
    <div class="modal-overlay" id="product-modal">
        <div class="modal-content" style="max-width: 800px; padding: 0; overflow: hidden;">
            <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #f1f5f9;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><i class="fas fa-plus"></i> Agregar
                    Producto a Transferencia</h2>
                <button class="btn-text" onclick="closeProductModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 20px; max-height: 500px; overflow-y: auto;">
                <div class="form-group-nt" style="margin-bottom: 20px;">
                    <label>Buscar Producto</label>
                    <input type="text" class="form-control" placeholder="Escriba el nombre o código del producto...">
                </div>

                <div class="search-results-list">
                    <!-- Item 1 -->
                    <div class="search-item-box">
                        <div class="search-item-info">
                            <h4>***CLORURO SODIO 0,9% DE 100ML JUVENCIA</h4>
                            <span><i class="fas fa-barcode"></i> CLORUROJUVENC</span>
                        </div>
                        <div class="search-item-stats">
                            <span class="stat-badge-s">Stock: 0,00</span>
                            <span class="price-badge-s">$0,00</span>
                        </div>
                        <button class="btn-add-circle"><i class="fas fa-plus"></i></button>
                    </div>
                    <!-- Item 2 -->
                    <div class="search-item-box">
                        <div class="search-item-info">
                            <h4>*LACTOFAES BEBE GOTAS(3025)</h4>
                            <span><i class="fas fa-barcode"></i> 76313</span>
                        </div>
                        <div class="search-item-stats">
                            <span class="stat-badge-s">Stock: 0,00</span>
                            <span class="price-badge-s">$23,60</span>
                        </div>
                        <button class="btn-add-circle"><i class="fas fa-plus"></i></button>
                    </div>
                    <!-- Item 3 -->
                    <div class="search-item-box">
                        <div class="search-item-info">
                            <h4>+ BIOTINA*COLAGENO HIDROLIZADO+ZINC+VITAMINAS A,E,D3,C,B...</h4>
                            <span><i class="fas fa-barcode"></i> 7862117789215</span>
                        </div>
                        <div class="search-item-stats">
                            <span class="stat-badge-s">Stock: 0,00</span>
                            <span class="price-badge-s">$14,25</span>
                        </div>
                        <button class="btn-add-circle"><i class="fas fa-plus"></i></button>
                    </div>
                    <!-- Item 4 -->
                    <div class="search-item-box">
                        <div class="search-item-info">
                            <h4>3-DERMICO CREMA * 30 G.</h4>
                            <span><i class="fas fa-barcode"></i> 7862101619832</span>
                        </div>
                        <div class="search-item-stats">
                            <span class="stat-badge-s">Stock: 2,00</span>
                            <span class="price-badge-s">$3,00</span>
                        </div>
                        <button class="btn-add-circle"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
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

        // Close on overlay click
        window.onclick = function (event) {
            if (event.target == document.getElementById('product-modal')) {
                closeProductModal();
            }
        }
    </script>
</body>

</html>