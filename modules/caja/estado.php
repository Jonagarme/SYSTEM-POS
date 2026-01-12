<?php
/**
 * Caja Status - Estado de Caja
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'caja_estado';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Caja | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .header-estado {
            margin-bottom: 25px;
        }

        .header-estado h1 {
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 700;
        }

        .main-grid-estado {
            display: grid;
            grid-template-columns: 350px 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .panel-estado {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .panel-estado-header {
            padding: 12px 20px;
            background: #0ea5e9;
            color: white;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-estado-body {
            padding: 20px;
        }

        .info-row-estado {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .info-row-estado .label {
            font-weight: 700;
            color: #1e293b;
        }

        .info-row-estado .value {
            color: #475569;
        }

        .btn-cierre-caja {
            width: 100%;
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            margin-top: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-movimientos-link {
            width: 100%;
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .card-stat-caja {
            background: #2563eb;
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .card-stat-caja .lbl {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .card-stat-caja .val {
            font-size: 2rem;
            font-weight: 800;
        }

        .card-stat-green {
            background: #059669;
        }

        .ventas-section {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .ventas-header {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ventas-empty {
            padding: 40px;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
        }

        .movimientos-recientes {
            margin-top: 25px;
            background: white;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
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
                <div class="header-estado">
                    <h1>Estado de Caja</h1>
                </div>

                <div class="main-grid-estado">
                    <!-- Left Panel: Caja Info -->
                    <div class="panel-estado">
                        <div class="panel-estado-header">
                            <i class="fas fa-cash-register"></i> Información de Caja
                        </div>
                        <div class="panel-estado-body">
                            <div class="info-row-estado">
                                <span class="label">Caja:</span>
                                <span class="value">CAJA SECUNDARIA</span>
                            </div>
                            <div class="info-row-estado">
                                <span class="label">Apertura:</span>
                                <span class="value">10/11/2025 20:46</span>
                            </div>
                            <div class="info-row-estado">
                                <span class="label">Saldo Inicial:</span>
                                <span class="value">$ 0,00</span>
                            </div>

                            <a href="cerrar_caja.php" class="btn-cierre-caja" style="text-decoration: none;">
                                <i class="fas fa-lock"></i> Cerrar Caja
                            </a>
                            <a href="movimientos.php" class="btn-movimientos-link">
                                <i class="fas fa-exchange-alt"></i> Movimientos
                            </a>
                        </div>
                    </div>

                    <!-- Middle Stat: Ventas Realizadas -->
                    <div class="card-stat-caja">
                        <span class="val">0</span>
                        <span class="lbl">Ventas Realizadas</span>
                    </div>

                    <!-- Right Stat: Total en Ventas -->
                    <div class="card-stat-caja card-stat-green">
                        <i class="fas fa-dollar-sign" style="font-size: 1.5rem; margin-bottom: 10px;"></i>
                        <span class="lbl">Total en Ventas</span>
                        <span class="val">$ 0.00</span>
                    </div>
                </div>

                <!-- Ventas Section -->
                <div class="ventas-section">
                    <div class="ventas-header">
                        <i class="fas fa-receipt"></i> Últimas Ventas
                    </div>
                    <div class="ventas-empty">
                        No se han realizado ventas hoy
                    </div>
                </div>

                <!-- Movimientos Section -->
                <div class="movimientos-recientes">
                    <div class="ventas-header">
                        <i class="fas fa-exchange-alt"></i> Movimientos Recientes
                    </div>
                    <div class="ventas-empty">
                        No hay movimientos registrados
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>