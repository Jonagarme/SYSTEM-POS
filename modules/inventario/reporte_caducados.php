<?php
/**
 * Expired Products Report - Productos Caducados
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario';

// Mock data for expired products
$productos = [
    ['codigo' => '7861148011999', 'nombre' => 'ABANIX 100MG SUSP FCO * 60ML', 'lote' => 'L2305-A', 'vencimiento' => '2025-12-15', 'stock' => '5.00', 'status' => 'Vencido', 'status_class' => 'st-expired'],
    ['codigo' => '7862101619832', 'nombre' => '3-DERMICO CREMA * 30 G.', 'lote' => 'D4412XP', 'vencimiento' => '2026-02-10', 'stock' => '12.00', 'status' => 'Próximo', 'status_class' => 'st-warning'],
    ['codigo' => '76313', 'nombre' => '*LACTOFAES BEBE GOTAS(3025)', 'lote' => 'LOT-778', 'vencimiento' => '2026-06-22', 'stock' => '8.00', 'status' => 'Vigente', 'status_class' => 'st-ok'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Caducados | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .cad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .cad-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cad-header h1 i {
            color: #f97316;
        }

        .summary-mini-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .s-mini-card {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid #e2e8f0;
        }

        .s-mini-card .val {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            display: block;
        }

        .s-mini-card .lbl {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
        }

        .border-red {
            border-left-color: #ef4444;
        }

        .border-orange {
            border-left-color: #f97316;
        }

        .border-green {
            border-left-color: #10b981;
        }

        .border-blue {
            border-left-color: #3b82f6;
        }

        .filters-report {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 150px;
            gap: 15px;
            align-items: flex-end;
        }

        .filters-report label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .cad-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .cad-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cad-table th {
            background: #1e293b;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .cad-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .st-expired {
            background: #fee2e2;
            color: #dc2626;
        }

        .st-warning {
            background: #fff7ed;
            color: #f97316;
        }

        .st-ok {
            background: #f0fdf4;
            color: #166534;
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
                <div class="cad-header">
                    <h1><i class="fas fa-exclamation-triangle"></i> Control de Caducidades</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #198754; border-color: #198754;">
                            <i class="fas fa-file-excel"></i> Exportar
                        </button>
                        <button class="btn btn-outline" style="color: #dc3545; border-color: #dc3545;">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <div class="summary-mini-grid">
                    <div class="s-mini-card border-red">
                        <span class="val">1</span>
                        <span class="lbl">Productos Vencidos</span>
                    </div>
                    <div class="s-mini-card border-orange">
                        <span class="val">1</span>
                        <span class="lbl">Vencimiento Próximo (30 días)</span>
                    </div>
                    <div class="s-mini-card border-green">
                        <span class="val">1</span>
                        <span class="lbl">Productos Vigentes</span>
                    </div>
                    <div class="s-mini-card border-blue">
                        <span class="val">3</span>
                        <span class="lbl">Total Lotes Revisados</span>
                    </div>
                </div>

                <div class="filters-report">
                    <div>
                        <label>Buscar Producto / Lote</label>
                        <input type="text" class="form-control" placeholder="Nombre, código o número de lote...">
                    </div>
                    <div>
                        <label>Rango de Vencimiento (Desde)</label>
                        <input type="date" class="form-control">
                    </div>
                    <div>
                        <label>Estado</label>
                        <select class="form-control">
                            <option>Todos los estados</option>
                            <option>Vencidos</option>
                            <option>Próximos a vencer</option>
                            <option>Vigentes</option>
                        </select>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                </div>

                <div class="cad-table-container">
                    <table class="cad-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción del Producto</th>
                                <th>Lote</th>
                                <th>Fecha Vencimiento</th>
                                <th style="text-align: right;">Stock Lote</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #2563eb;">
                                        <?php echo $p['codigo']; ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php echo $p['nombre']; ?>
                                    </td>
                                    <td style="font-family: monospace;">
                                        <?php echo $p['lote']; ?>
                                    </td>
                                    <td style="font-weight: 700; color: #1e293b;">
                                        <?php echo date('d/m/Y', strtotime($p['vencimiento'])); ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">
                                        <?php echo $p['stock']; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-status <?php echo $p['status_class']; ?>">
                                            <?php echo $p['status']; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="btn btn-text" style="color: #6366f1;"><i
                                                class="fas fa-eye"></i></button>
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