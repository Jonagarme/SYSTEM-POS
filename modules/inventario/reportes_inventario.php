<?php
/**
 * Inventory Reports Dashboard - Reportes de Inventario
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Inventario | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .reports-header {
            margin-bottom: 30px;
        }

        .reports-header h1 {
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reports-header h1 i {
            color: #10b981;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .report-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: #10b981;
        }

        .report-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: #f0fdf4;
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .report-info h3 {
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .report-info p {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
        }

        .report-footer {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #10b981;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-new {
            background: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
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
                <div class="reports-header">
                    <h1><i class="fas fa-chart-bar"></i> Reportes de Inventario</h1>
                    <p style="color: #64748b; font-size: 0.9rem;">Seleccione el tipo de reporte que desea generar para
                        su análisis de stock.</p>
                </div>

                <div class="reports-grid">
                    <!-- Stock Report -->
                    <a href="reporte_stock.php" class="report-card">
                        <div class="report-icon"><i class="fas fa-boxes"></i></div>
                        <div class="report-info">
                            <h3>Stock Actual por Ubicación</h3>
                            <p>Visualice las existencias reales de sus productos segmentadas por bodega o sucursal.</p>
                        </div>
                        <div class="report-footer">
                            <span>Generar Reporte</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Expiration Report -->
                    <a href="reporte_caducados.php" class="report-card">
                        <div class="report-icon" style="background: #fff7ed; color: #f97316;"><i
                                class="fas fa-exclamation-triangle"></i></div>
                        <div class="report-info">
                            <h3>Vencimientos Próximos</h3>
                            <p>Controle los productos que están cerca de su fecha de caducidad para evitar mermas.</p>
                        </div>
                        <div class="report-footer" style="color: #f97316;">
                            <span>Ver Alertas</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Valuation Report -->
                    <a href="reporte_valorado.php" class="report-card">
                        <div class="report-icon" style="background: #eff6ff; color: #3b82f6;"><i
                                class="fas fa-dollar-sign"></i></div>
                        <div class="report-info">
                            <h3>Inventario Valorado</h3>
                            <p>Análisis financiero del stock basado en costos promedio y precios de venta actuales.</p>
                        </div>
                        <div class="report-footer" style="color: #3b82f6;">
                            <span>Ver Valoración</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Movements Report -->
                    <a href="kardex.php" class="report-card">
                        <div class="report-icon" style="background: #fdf2f8; color: #db2777;"><i
                                class="fas fa-exchange-alt"></i></div>
                        <div class="report-info">
                            <h3>Movimientos de Kardex</h3>
                            <p>Rastreo detallado de cada entrada y salida de mercancía por un periodo de tiempo.</p>
                        </div>
                        <div class="report-footer" style="color: #db2777;">
                            <span>Consultar Historial</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Low Stock Report -->
                    <a href="reporte_stock_bajo.php" class="report-card">
                        <div class="report-icon" style="background: #fef2f2; color: #dc2626;"><i
                                class="fas fa-arrow-down"></i></div>
                        <div class="report-info">
                            <h3>Productos Stock Bajo</h3>
                            <p>Listado de artículos que han llegado a su nivel mínimo y requieren reposición.</p>
                        </div>
                        <div class="report-footer" style="color: #dc2626;">
                            <span>Revisar Reposición</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- ABC Analysis -->
                    <a href="#" class="report-card">
                        <div class="report-icon" style="background: #f5f3ff; color: #7c3aed;"><i
                                class="fas fa-layer-group"></i></div>
                        <div class="report-info">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3>Análisis ABC</h3>
                                <span class="badge-new">Próximamente</span>
                            </div>
                            <p>Clasificación de inventario por importancia (valor de consumo) para optimizar recursos.
                            </p>
                        </div>
                        <div class="report-footer" style="color: #7c3aed;">
                            <span>Más información</span>
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>