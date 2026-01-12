<?php
/**
 * Stock Transfers List - Transferencias de Stock
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
    <title>Transferencias de Stock | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .transfer-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .transfer-title h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .breadcrumb {
            display: flex;
            gap: 5px;
            font-size: 0.75rem;
            color: #64748b;
        }

        .breadcrumb a {
            color: #2563eb;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn-locations {
            background: white;
            color: #0ea5e9;
            border: 1px solid #0ea5e9;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-grid-transfer {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .trans-card {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .trans-card .info h3 {
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0 0 10px 0;
            opacity: 0.9;
        }

        .trans-card .info .value {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .trans-card .icon {
            font-size: 2.2rem;
            opacity: 0.3;
        }

        .filters-panel-transfer {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 100px 100px;
            gap: 15px;
            align-items: flex-end;
        }

        .filters-panel-transfer label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .empty-state-transfer {
            background: white;
            border-radius: 12px;
            padding: 80px 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .empty-state-transfer .icon-container {
            font-size: 4rem;
            color: #475569;
            margin-bottom: 20px;
        }

        .empty-state-transfer h3 {
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-state-transfer p {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 25px;
        }

        .btn-create-center {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
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
                <div class="transfer-header">
                    <div class="transfer-title">
                        <h1>Transferencias de Stock</h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="../inventario/kardex.php">Inventario</a>
                            / <span>Transferencias de Stock</span>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="nueva_transferencia.php" class="btn btn-primary"
                            style="padding: 10px 20px; font-weight: 600;">
                            <i class="fas fa-plus"></i> Nueva Transferencia
                        </a>
                        <a href="ubicaciones.php" class="btn-locations">
                            <i class="fas fa-map-marker-alt"></i> Gestionar Ubicaciones
                        </a>
                    </div>
                </div>

                <div class="summary-grid-transfer">
                    <div class="trans-card">
                        <div class="info">
                            <h3>0</h3>
                            <div class="value">Total Transferencias</div>
                        </div>
                        <i class="fas fa-exchange-alt icon"></i>
                    </div>
                    <div class="trans-card">
                        <div class="info">
                            <h3>0</h3>
                            <div class="value">En Esta Página</div>
                        </div>
                        <i class="fas fa-list-ul icon"></i>
                    </div>
                </div>

                <div class="filters-panel-transfer">
                    <div>
                        <label>Estado</label>
                        <select class="form-control">
                            <option>Todos los estados</option>
                            <option>Completado</option>
                            <option>Pendiente</option>
                            <option>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label>Ubicación Origen</label>
                        <select class="form-control">
                            <option>Todas las ubicaciones</option>
                        </select>
                    </div>
                    <div>
                        <label>Ubicación Destino</label>
                        <select class="form-control">
                            <option>Todas las ubicaciones</option>
                        </select>
                    </div>
                    <button class="btn btn-outline" style="border-color: #2563eb; color: #2563eb; height: 42px;">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <button class="btn btn-outline" style="border-color: #64748b; color: #64748b; height: 42px;">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>

                <div class="empty-state-transfer">
                    <div class="icon-container">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>No hay transferencias de stock</h3>
                    <p>Comienza creando tu primera transferencia entre ubicaciones</p>
                    <a href="nueva_transferencia.php" class="btn-create-center">
                        <i class="fas fa-plus"></i> Crear Primera Transferencia
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>