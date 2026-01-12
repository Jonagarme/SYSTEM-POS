<?php
/**
 * General Kardex - Kardex General
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_kardex';

// Mock data for Kardex
$movimientos = [
    ['fecha' => '14/12/2025 16:40', 'producto' => 'AGUA ALL NATURAL S-GASX1LT', 'tipo' => 'AJUSTE INGRESO', 'tipo_class' => 'bg-info', 'detalle' => 'Ajuste AJ-20251214-28/AB4: Inventario Físico', 'ingreso' => '+1,00', 'egreso' => '-', 'saldo' => '3,00'],
    ['fecha' => '02/12/2025 20:26', 'producto' => 'CREMA PARA PEINAR - SAVITAL MULTIVITAMINAS Y SABILA 22ML', 'tipo' => 'VENTA', 'tipo_class' => 'bg-primary', 'detalle' => 'Factura Venta N° 001-001-000000018', 'ingreso' => '-', 'egreso' => '-2,00', 'saldo' => '6,00'],
    ['fecha' => '26/11/2025 18:29', 'producto' => 'VITAMINA C FRESA SOL ORAL GOTAS X30ML - MK', 'tipo' => 'VENTA', 'tipo_class' => 'bg-primary', 'detalle' => 'Factura Venta N° 001-001-000000017', 'ingreso' => '-', 'egreso' => '-3,00', 'saldo' => '0,00'],
    ['fecha' => '26/11/2025 18:29', 'producto' => 'VITAMINA C NARANJA 500MG SX12 TAB MAST - LASANTE', 'tipo' => 'VENTA', 'tipo_class' => 'bg-primary', 'detalle' => 'Factura Venta N° 001-001-000000017', 'ingreso' => '-', 'egreso' => '-3,00', 'saldo' => '66,00'],
    ['fecha' => '24/11/2025 20:43', 'producto' => 'ACCUALAXAN 8.5G SOBRES CAJA X 7', 'tipo' => 'VENTA', 'tipo_class' => 'bg-primary', 'detalle' => 'Factura Venta N° 001-001-000000016', 'ingreso' => '-', 'egreso' => '-3,00', 'saldo' => '9,00'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex General | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .kardex-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .kardex-title h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-cards-kardex {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .k-card {
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .k-card .info h3 {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .k-card .info .value {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .k-card .icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .k-blue {
            background: #007bff;
        }

        .k-green {
            background: #198754;
        }

        .k-red {
            background: #dc3545;
        }

        .k-orange {
            background: #ffc107;
            color: white;
        }

        .filters-panel-kardex {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr 180px;
            gap: 15px;
            align-items: flex-end;
        }

        .filters-panel-kardex label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .k-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .k-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .k-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.8rem;
            color: #1e293b;
        }

        .k-table tr:hover {
            background: #f8fafc;
        }

        .badge-k {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
        }

        .bg-info {
            background: #0dcaf0;
        }

        .bg-primary {
            background: #0d6efd;
        }

        .action-eye {
            color: #0d6efd;
            background: #e7f1ff;
            border: 1px solid #0d6efd;
            padding: 4px 8px;
            border-radius: 4px;
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
                <div class="kardex-header">
                    <div class="kardex-title">
                        <h1><i class="fas fa-chart-line"></i> Kardex General</h1>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #198754; border-color: #198754;">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button class="btn btn-outline" style="color: #dc3545; border-color: #dc3545;">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <div class="filters-panel-kardex">
                    <div>
                        <label>Producto (Buscar por nombre o código)</label>
                        <input type="text" class="form-control" placeholder="Buscar por nombre o código...">
                    </div>
                    <div>
                        <label>Fecha Desde</label>
                        <input type="date" class="form-control">
                    </div>
                    <div>
                        <label>Fecha Hasta</label>
                        <input type="date" class="form-control">
                    </div>
                    <div>
                        <label>Tipo Movimiento</label>
                        <select class="form-control">
                            <option>Todos</option>
                            <option>Ventas</option>
                            <option>Compras</option>
                            <option>Ajustes</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button class="btn btn-primary" style="flex: 1;"><i class="fas fa-search"></i> Filtrar</button>
                        <button class="btn btn-secondary"><i class="fas fa-times"></i> Limpiar</button>
                    </div>
                </div>

                <div class="summary-cards-kardex">
                    <div class="k-card k-blue">
                        <div class="info">
                            <h3>Total Movimientos</h3>
                            <div class="value">19</div>
                        </div>
                        <i class="fas fa-exchange-alt icon"></i>
                    </div>
                    <div class="k-card k-green">
                        <div class="info">
                            <h3>Total Entradas</h3>
                            <div class="value">601,00</div>
                        </div>
                        <i class="fas fa-arrow-up icon"></i>
                    </div>
                    <div class="k-card k-red">
                        <div class="info">
                            <h3>Total Salidas</h3>
                            <div class="value">165,00</div>
                        </div>
                        <i class="fas fa-arrow-down icon"></i>
                    </div>
                    <div class="k-card k-orange">
                        <div class="info">
                            <h3>Productos Activos</h3>
                            <div class="value">12</div>
                        </div>
                        <i class="fas fa-box icon"></i>
                    </div>
                </div>

                <table class="k-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tipo Movimiento</th>
                            <th>Detalle</th>
                            <th style="text-align: right;">Ingreso</th>
                            <th style="text-align: right;">Egreso</th>
                            <th style="text-align: right;">Saldo</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td style="font-size: 0.7rem;">
                                    <?php echo $m['fecha']; ?>
                                </td>
                                <td style="font-weight: 700; width: 300px;">
                                    <?php echo $m['producto']; ?>
                                </td>
                                <td><span class="badge-k <?php echo $m['tipo_class']; ?>">+
                                        <?php echo $m['tipo']; ?>
                                    </span></td>
                                <td style="color: #64748b; font-size: 0.75rem;">
                                    <?php echo $m['detalle']; ?>
                                </td>
                                <td style="text-align: right; color: #198754; font-weight: 700;">
                                    <?php echo $m['ingreso']; ?>
                                </td>
                                <td style="text-align: right; color: #dc3545; font-weight: 700;">
                                    <?php echo $m['egreso']; ?>
                                </td>
                                <td style="text-align: right; font-weight: 800;">
                                    <?php echo $m['saldo']; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="kardex_detalle.php?producto=<?php echo urlencode($m['producto']); ?>"
                                        class="action-eye">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>