<?php
/**
 * Purchase Orders List - Órdenes de Compra
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
    <title>Órdenes de Compra | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .no-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .no-list-header h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            font-weight: 700;
        }

        .no-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .no-table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .empty-state-no {
            padding: 80px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty-state-no i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
            display: block;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .status-draft {
            background: #f1f5f9;
            color: #475569;
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
                <div class="no-list-header">
                    <h1><i class="fas fa-file-invoice"></i> Órdenes de Compra (PO)</h1>
                    <a href="nueva_orden.php" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600;">
                        <i class="fas fa-plus"></i> Nueva Orden
                    </a>
                </div>

                <div class="no-table-container">
                    <table class="no-table">
                        <thead>
                            <tr>
                                <th>Orden #</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Total Estimado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="empty-state-no">
                        <i class="fas fa-file-alt"></i>
                        <p>No se encontraron órdenes de compra</p>
                        <a href="nueva_orden.php" class="btn btn-primary" style="margin-top: 15px;">Crear Mi Primera
                            Orden</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>