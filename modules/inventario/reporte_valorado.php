<?php
/**
 * Valued Inventory Report - Inventario Valorado
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario';

// Mock data
$productos = [
    ['codigo' => '7861148011999', 'nombre' => 'ABANIX 100MG SUSP FCO * 60ML', 'stock' => '25.00', 'costo' => '5.20', 'precio' => '7.75', 'valor_costo' => '130.00', 'valor_venta' => '193.75', 'margen' => '32.9%'],
    ['codigo' => '7862101619832', 'nombre' => '3-DERMICO CREMA * 30 G.', 'stock' => '10.00', 'costo' => '1.50', 'precio' => '2.10', 'valor_costo' => '15.00', 'valor_venta' => '21.00', 'margen' => '28.5%'],
    ['codigo' => '76313', 'nombre' => '*LACTOFAES BEBE GOTAS(3025)', 'stock' => '8.00', 'costo' => '12.40', 'precio' => '16.52', 'valor_costo' => '99.20', 'valor_venta' => '132.16', 'margen' => '24.9%'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Valorado | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .val-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .val-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .val-header h1 i {
            color: #059669;
        }

        .valuation-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .v-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .v-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .v-cost::after {
            background: #6366f1;
        }

        .v-sale::after {
            background: #10b981;
        }

        .v-profit::after {
            background: #f59e0b;
        }

        .v-card .info h3 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 10px;
        }

        .v-card .info .value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
        }

        .v-card .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .v-cost .icon-box {
            background: #eef2ff;
            color: #6366f1;
        }

        .v-sale .icon-box {
            background: #ecfdf5;
            color: #10b981;
        }

        .v-profit .icon-box {
            background: #fffbeb;
            color: #f59e0b;
        }

        .val-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .val-table {
            width: 100%;
            border-collapse: collapse;
        }

        .val-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .val-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }

        .num-col {
            text-align: right;
            font-family: 'Inter', sans-serif;
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
                <div class="val-header">
                    <h1><i class="fas fa-hand-holding-usd"></i> Inventario Valorado</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #6366f1; border-color: #6366f1;"><i
                                class="fas fa-file-pdf"></i> PDF</button>
                        <button class="btn btn-outline" style="color: #198754; border-color: #198754;"><i
                                class="fas fa-file-excel"></i> Excel</button>
                    </div>
                </div>

                <div class="valuation-grid">
                    <div class="v-card v-cost">
                        <div class="info">
                            <h3>Valor Total (Costo)</h3>
                            <div class="value">$ 244.20</div>
                        </div>
                        <div class="icon-box"><i class="fas fa-receipt"></i></div>
                    </div>
                    <div class="v-card v-sale">
                        <div class="info">
                            <h3>Valor Total (Venta)</h3>
                            <div class="value">$ 346.91</div>
                        </div>
                        <div class="icon-box"><i class="fas fa-tags"></i></div>
                    </div>
                    <div class="v-card v-profit">
                        <div class="info">
                            <h3>Utilidad Estimada</h3>
                            <div class="value">$ 102.71</div>
                        </div>
                        <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>

                <div class="filters-report"
                    style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); margin-bottom: 25px; display: flex; gap: 15px; align-items: flex-end;">
                    <div style="flex: 2;">
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Filtrar
                            por Categoría / Ubicación</label>
                        <select class="form-control">
                            <option>Todas las categorías</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Ubicación</label>
                        <select class="form-control">
                            <option>Todas las ubicaciones</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" style="height: 42px; padding: 0 30px;"><i class="fas fa-search"></i>
                        Calcular</button>
                </div>

                <div class="val-table-container">
                    <table class="val-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align: right;">Existencia</th>
                                <th style="text-align: right;">Costo Prom.</th>
                                <th style="text-align: right;">Precio Vta.</th>
                                <th style="text-align: right;">Total Costo</th>
                                <th style="text-align: right;">Total Venta</th>
                                <th style="text-align: right;">Margen %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;">
                                            <?php echo $p['nombre']; ?>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b;">
                                            <?php echo $p['codigo']; ?>
                                        </div>
                                    </td>
                                    <td class="num-col" style="font-weight: 800;">
                                        <?php echo $p['stock']; ?>
                                    </td>
                                    <td class="num-col">$
                                        <?php echo $p['costo']; ?>
                                    </td>
                                    <td class="num-col">$
                                        <?php echo $p['precio']; ?>
                                    </td>
                                    <td class="num-col" style="color: #6366f1; font-weight: 700;">$
                                        <?php echo $p['valor_costo']; ?>
                                    </td>
                                    <td class="num-col" style="color: #10b981; font-weight: 700;">$
                                        <?php echo $p['valor_venta']; ?>
                                    </td>
                                    <td class="num-col">
                                        <span
                                            style="background: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                            <?php echo $p['margen']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>