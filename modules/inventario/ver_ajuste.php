<?php
/**
 * View Inventory Adjustment - Ver Ajuste
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ajustes';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Ajuste | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .view-ajuste-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .view-ajuste-header h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 20px;
            border: 1px solid #f1f5f9;
        }

        .info-card h4 {
            font-size: 0.85rem;
            color: #2563eb;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 10px;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        .data-row strong {
            color: #1e293b;
        }

        .data-row span {
            color: #475569;
        }

        .badge-correction {
            background: #ffc107;
            color: white;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .products-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .products-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .view-table {
            width: 100%;
            border-collapse: collapse;
        }

        .view-table th {
            text-align: left;
            padding: 12px 20px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .view-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .qty-badge {
            background: #6c757d;
            color: white;
            padding: 2px 10px;
            border-radius: 6px;
            font-weight: 700;
        }

        .qty-badge-cyan {
            background: #0dcaf0;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.85rem;
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
                <div class="view-ajuste-header">
                    <h1><i class="fas fa-balance-scale"></i> Ajuste AJ-20251214-67278A</h1>
                    <a href="ajustes.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver a Ajustes</a>
                </div>

                <div class="info-grid-2">
                    <div class="info-card">
                        <h4><i class="fas fa-info-circle"></i> Información General</h4>
                        <div class="data-row">
                            <strong>Número de Ajuste:</strong>
                            <span>AJ-20251214-67278A</span>
                        </div>
                        <div class="data-row">
                            <strong>Fecha:</strong>
                            <span>14/12/2025 16:34</span>
                        </div>
                        <div class="data-row">
                            <strong>Tipo de Ajuste:</strong>
                            <div><span class="badge-correction"><i class="fas fa-edit"></i> Corrección</span></div>
                        </div>
                        <div class="data-row">
                            <strong>Motivo:</strong>
                            <span>Corrección de Costo</span>
                        </div>
                        <div class="data-row">
                            <strong>Usuario:</strong>
                            <span>Usuario Administrador</span>
                        </div>
                    </div>

                    <div class="info-card">
                        <h4><i class="fas fa-comment-alt"></i> Observaciones</h4>
                        <p style="font-size: 0.85rem; color: #475569;">Prueba2</p>
                    </div>
                </div>

                <div class="products-card">
                    <div class="products-card-header">
                        <i class="fas fa-boxes"></i> Productos Ajustados
                    </div>
                    <table class="view-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th style="text-align: center;">Cantidad Anterior</th>
                                <th style="text-align: center;">Cantidad Nueva</th>
                                <th style="text-align: center;">Diferencia</th>
                                <th style="text-align: right;">Precio Nuevo</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="color: #64748b;">7861075200060</td>
                                <td style="font-weight: 700;">AGUA ALL NATURAL S-GASX1LT</td>
                                <td style="text-align: center;"><span class="qty-badge">2</span></td>
                                <td style="text-align: center;"><span class="qty-badge qty-badge-cyan">2</span></td>
                                <td style="text-align: center; font-weight: 700;">0</td>
                                <td style="text-align: right; color: #64748b;">$5,45</td>
                                <td style="color: #94a3b8;">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>