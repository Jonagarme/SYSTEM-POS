<?php
/**
 * Automatic Stock Configuration - Configuración de Stock Automático
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_config';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Stock Automático | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .stock-header h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box-stock {
            background: #e0f7fa;
            border-left: 4px solid #00acc1;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .info-box-stock i {
            color: #00acc1;
            font-size: 1.2rem;
            margin-top: 3px;
        }

        .info-box-stock .content h4 {
            margin: 0 0 5px 0;
            font-size: 0.9rem;
            color: #00838f;
        }

        .info-box-stock .content p {
            margin: 0;
            font-size: 0.8rem;
            color: #006064;
            line-height: 1.5;
        }

        .filters-panel-stock {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        .filters-panel-stock>div {
            flex: 1;
        }

        .filters-panel-stock label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .stock-list-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .list-header-purple {
            background: #6366f1;
            color: white;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .empty-state-stock {
            padding: 60px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty-state-stock i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state-stock h3 {
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-state-stock p {
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .btn-purple {
            background: #6366f1;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-purple:hover {
            background: #4f46e5;
        }

        /* Modal Styles */
        .modal-body-stock {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group-stock {
            margin-bottom: 15px;
        }

        .form-group-stock label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .form-group-stock p {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 5px;
        }

        .modal-footer-stock {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
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
                <div class="stock-header">
                    <h1><i class="fas fa-cogs"></i> Configuración de Stock Automático</h1>
                    <button class="btn btn-primary" onclick="openStockModal()">
                        <i class="fas fa-plus"></i> Nueva Configuración
                    </button>
                </div>

                <div class="info-box-stock">
                    <i class="fas fa-info-circle"></i>
                    <div class="content">
                        <h4>¿Cómo funciona el stock automático?</h4>
                        <p>Configure niveles mínimos de stock para sus productos. Cuando el stock actual sea menor o
                            igual al nivel mínimo, el sistema generará automáticamente órdenes de compra para mantener
                            su inventario optimizado.</p>
                    </div>
                </div>

                <div class="filters-panel-stock">
                    <div style="flex: 2;">
                        <label>Buscar Producto</label>
                        <input type="text" class="form-control" placeholder="Nombre del producto...">
                    </div>
                    <div>
                        <label>Estado</label>
                        <select class="form-control">
                            <option>Todos</option>
                            <option>Activo</option>
                            <option>Inactivo</option>
                        </select>
                    </div>
                    <div style="flex: 1.5;">
                        <label>Proveedor</label>
                        <select class="form-control">
                            <option>Todos los proveedores</option>
                        </select>
                    </div>
                    <div style="flex: 0; display: flex; gap: 8px;">
                        <button class="btn btn-outline" style="min-width: 90px;"><i class="fas fa-search"></i>
                            Filtrar</button>
                        <button class="btn btn-secondary" style="min-width: 90px;"><i class="fas fa-times"></i>
                            Limpiar</button>
                    </div>
                </div>

                <div class="stock-list-container">
                    <div class="list-header-purple">
                        <i class="fas fa-list-ul"></i> Configuraciones de Stock
                    </div>
                    <div class="empty-state-stock">
                        <i class="fas fa-box-open"></i>
                        <h3>No hay configuraciones de stock</h3>
                        <p>Configure niveles de stock automático para sus productos.</p>
                        <button class="btn-purple" onclick="openStockModal()">
                            <i class="fas fa-plus"></i> Agregar Primera Configuración
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: NUEVA CONFIGURACIÓN -->
    <div class="modal-overlay" id="stock-modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2><i class="fas fa-cogs"></i> Nueva Configuración de Stock</h2>
                <button class="btn-text" onclick="closeStockModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-body-stock">
                    <div class="form-group-stock">
                        <label>Producto *</label>
                        <select class="form-control">
                            <option>Seleccione un producto...</option>
                        </select>
                    </div>
                    <div class="form-group-stock">
                        <label>Stock Actual</label>
                        <input type="text" class="form-control" readonly style="background: #f8fafc;">
                    </div>
                    <div class="form-group-stock">
                        <label>Stock Mínimo *</label>
                        <input type="text" class="form-control" placeholder="Ej: 10">
                        <p>Cuando el stock llegue a este nivel, se generará una orden automática</p>
                    </div>
                    <div class="form-group-stock">
                        <label>Cantidad a Pedir *</label>
                        <input type="text" class="form-control" placeholder="Ej: 50">
                        <p>Cantidad que se pedirá automáticamente</p>
                    </div>
                    <div class="form-group-stock">
                        <label>Proveedor Preferido</label>
                        <select class="form-control">
                            <option>Seleccione un proveedor...</option>
                        </select>
                    </div>
                    <div class="form-group-stock">
                        <label>Estado</label>
                        <select class="form-control">
                            <option>Activo</option>
                            <option>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer-stock" style="padding: 20px; border-top: 1px solid #f1f5f9;">
                <button class="btn btn-secondary" onclick="closeStockModal()">Cancelar</button>
                <button class="btn-purple" style="padding: 10px 20px;">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function openStockModal() {
            document.getElementById('stock-modal').style.display = 'flex';
        }
        function closeStockModal() {
            document.getElementById('stock-modal').style.display = 'none';
        }
    </script>
</body>

</html>