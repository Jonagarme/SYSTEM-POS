<?php
/**
 * New Purchase Form - Nueva Compra
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_compras';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .nc-header h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back-nc {
            background: #64748b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .nc-panel {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .nc-panel-header {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 20px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nc-panel-body {
            padding: 20px;
        }

        .form-group-nc {
            margin-bottom: 15px;
        }

        .form-group-nc label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .search-container-nc {
            position: relative;
        }

        .search-info-nc {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nc-products-panel {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            box-shadow: var(--shadow-sm);
        }

        .empty-cart-nc {
            padding: 60px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty-cart-nc i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
            display: block;
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
                <div class="nc-header">
                    <h1><i class="fas fa-plus-circle"></i> Nueva Compra</h1>
                    <a href="compras.php" class="btn-back-nc">
                        <i class="fas fa-arrow-left"></i> Volver a Compras
                    </a>
                </div>

                <div class="nc-grid">
                    <!-- Left Side: General Info -->
                    <div class="nc-panel">
                        <div class="nc-panel-header">
                            <i class="fas fa-info-circle"></i> Información General
                        </div>
                        <div class="nc-panel-body">
                            <div class="form-group-nc">
                                <label>Proveedor *</label>
                                <select class="form-control">
                                    <option>Seleccionar proveedor...</option>
                                </select>
                            </div>
                            <div class="form-group-nc">
                                <label>Número de Factura del Proveedor</label>
                                <input type="text" class="form-control" placeholder="Ej: FAC-001">
                            </div>
                            <div class="form-group-nc">
                                <label>Fecha de Factura *</label>
                                <input type="date" class="form-control" value="2026-01-11">
                            </div>
                            <div class="form-group-nc">
                                <label>Tipo de Pago</label>
                                <select class="form-control">
                                    <option>Efectivo</option>
                                    <option>Crédito</option>
                                    <option>Transferencia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Product Search -->
                    <div class="nc-panel">
                        <div class="nc-panel-header">
                            <i class="fas fa-search"></i> Agregar Productos
                        </div>
                        <div class="nc-panel-body">
                            <div class="form-group-nc">
                                <label>Buscar Producto</label>
                                <div class="search-container-nc">
                                    <input type="text" class="form-control" placeholder="Buscar por nombre o código...">
                                </div>
                                <div class="search-info-nc">
                                    <i class="fas fa-info-circle"></i> Escribe el nombre o código del producto para
                                    buscarlo
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="nc-products-panel">
                    <div class="nc-panel-header">
                        <i class="fas fa-list-ul"></i> Productos de la Compra
                    </div>
                    <div class="empty-cart-nc">
                        <i class="fas fa-shopping-cart"></i>
                        <p>No hay productos agregados a la compra</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>