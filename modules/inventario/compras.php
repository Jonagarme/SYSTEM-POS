<?php
/**
 * Purchase Management - Gestión de Compras
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
    <title>Gestión de Compras | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .purchases-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .purchases-header h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filters-panel-compra {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .filters-panel-compra label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .summary-cards-purchases {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .p-card {
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }

        .p-card .info h3 {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .p-card .info .value {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .p-card .icon {
            font-size: 1.8rem;
            opacity: 0.3;
        }

        .p-blue {
            background: #007bff;
        }

        .p-green {
            background: #198754;
        }

        .p-orange {
            background: #ffc107;
        }

        .p-cyan {
            background: #0dcaf0;
        }

        .purchases-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .purchases-table {
            width: 100%;
            border-collapse: collapse;
        }

        .purchases-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .empty-state-purchases {
            padding: 100px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty-state-purchases i {
            font-size: 3.5rem;
            color: #cbd5e1;
            margin-bottom: 20px;
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
                <div class="purchases-header">
                    <h1><i class="fas fa-shopping-cart"></i> Gestión de Compras</h1>
                    <a href="nueva_compra.php" class="btn btn-primary"
                        style="padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-plus"></i> Nueva Compra
                    </a>
                </div>

                <div class="filters-panel-compra">
                    <div>
                        <label>Fecha Inicio</label>
                        <input type="date" class="form-control" value="2026-01-01">
                    </div>
                    <div>
                        <label>Fecha Fin</label>
                        <input type="date" class="form-control" value="2026-01-11">
                    </div>
                    <div>
                        <label>Proveedor</label>
                        <select class="form-control">
                            <option>Todos los proveedores</option>
                        </select>
                    </div>
                    <div>
                        <label>Estado</label>
                        <select class="form-control">
                            <option>Todos los estados</option>
                        </select>
                    </div>
                </div>

                <div class="summary-cards-purchases">
                    <div class="p-card p-blue">
                        <div class="info">
                            <h3>Total Compras</h3>
                            <div class="value">0</div>
                        </div>
                        <i class="fas fa-shopping-cart icon"></i>
                    </div>
                    <div class="p-card p-green">
                        <div class="info">
                            <h3>Monto Total</h3>
                            <div class="value">$ 0,00</div>
                        </div>
                        <i class="fas fa-dollar-sign icon"></i>
                    </div>
                    <div class="p-card p-orange">
                        <div class="info">
                            <h3>Pendientes</h3>
                            <div class="value">0</div>
                        </div>
                        <i class="fas fa-clock icon"></i>
                    </div>
                    <div class="p-card p-cyan">
                        <div class="info">
                            <h3>Este Mes</h3>
                            <div class="value">0</div>
                        </div>
                        <i class="fas fa-calendar icon"></i>
                    </div>
                </div>

                <div class="purchases-table-container">
                    <table class="purchases-table">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Estado</th>
                                <th>Productos</th>
                                <th>Subtotal</th>
                                <th>ISV</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="empty-state-purchases">
                        <i class="fas fa-shopping-cart"></i>
                        <p>No se encontraron compras</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>