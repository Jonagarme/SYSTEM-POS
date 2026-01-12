<?php
/**
 * New Inventory Adjustment - Nuevo Ajuste
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
    <title>Nuevo Ajuste | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .section-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .section-header {
            background: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #2563eb;
        }

        .section-body {
            padding: 20px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .required {
            color: #ef4444;
        }

        .product-search-bar {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 60px;
            gap: 15px;
            align-items: flex-end;
        }

        .btn-add-prod {
            height: 42px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ajuste-detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ajuste-detail-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }

        .ajuste-detail-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #94a3b8;
        }

        .item-count-badge {
            background: #2563eb;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: auto;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-save {
            background: #0d6efd;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Search Dropdown Mock */
        .search-container {
            position: relative;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-radius: 8px;
            margin-top: 5px;
            display: none;
        }

        .search-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.8rem;
        }

        .search-item:hover {
            background: #f1f5f9;
        }

        .search-item:last-child {
            border-bottom: none;
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
                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i> Información General
                    </div>
                    <div class="section-body">
                        <div class="form-grid-3">
                            <div>
                                <label class="form-label">Fecha</label>
                                <input type="text" class="form-control" value="11/01/2026" readonly
                                    style="background: #f8fafc;">
                            </div>
                            <div>
                                <label class="form-label">Tipo de Ajuste <span class="required">*</span></label>
                                <select class="form-control">
                                    <option>Seleccionar...</option>
                                    <option>Entrada</option>
                                    <option>Salida</option>
                                    <option>Corrección</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Motivo <span class="required">*</span></label>
                                <select class="form-control">
                                    <option>Seleccionar...</option>
                                    <option>Inventario Físico</option>
                                    <option>Corrección de Costo</option>
                                    <option>Daño/Mermas</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" placeholder="Nota opcional..."
                                    style="height: 42px; resize: none;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-box-open"></i> Agregar Productos
                    </div>
                    <div class="section-body">
                        <div class="product-search-bar">
                            <div class="search-container">
                                <label class="form-label">Buscar Producto (Código o Nombre)</label>
                                <input type="text" class="form-control" placeholder="Escriba código o nombre..."
                                    id="prod-search" autocomplete="off">
                                <div class="search-results" id="search-results">
                                    <div class="search-item">CLORUROJUVENC - ***CLORURO SODIO 0,9% DE 100ML JUVENCIA
                                    </div>
                                    <div class="search-item">76313 - *LACTOFAES BEBE GOTAS(3025)</div>
                                    <div class="search-item">7862117789215 - +BIOTINA*COLAGENO
                                        HIDROLIZADO+ZINC+VITAMINA/A,E,D3,C,B1,B2,B5,B6,B12</div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Stock Actual</label>
                                <input type="text" class="form-control" readonly style="background: #f8fafc;">
                            </div>
                            <div>
                                <label class="form-label">Precio Actual</label>
                                <input type="text" class="form-control" readonly style="background: #f8fafc;">
                            </div>
                            <div>
                                <label class="form-label">Nuevo Precio</label>
                                <input type="text" class="form-control">
                            </div>
                            <div>
                                <label class="form-label">Nueva Cantidad</label>
                                <input type="text" class="form-control">
                            </div>
                            <div>
                                <button class="btn-add-prod"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div
                            style="text-align: right; margin-top: 10px; font-size: 0.75rem; font-weight: 700; color: #64748b;">
                            Diferencia de Stock: <span style="color: #1e293b;">0</span>
                        </div>
                    </div>
                </div>

                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-list-ul"></i> Detalle del Ajuste
                        <span class="item-count-badge">0 items</span>
                    </div>
                    <div class="section-body" style="padding: 0;">
                        <table class="ajuste-detail-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Precio Actual</th>
                                    <th>Nuevo Precio</th>
                                    <th>Stock Sistema</th>
                                    <th>Cant. Nueva</th>
                                    <th>Diferencia</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-arrow-up"
                                            style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                                        Agregue productos para realizar el ajuste
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-footer">
                    <a href="ajustes.php" class="btn-cancel">Cancelar</a>
                    <button class="btn-save"><i class="fas fa-save"></i> Guardar Ajuste</button>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        // Mock Search Dropdown logic
        const searchInput = document.getElementById('prod-search');
        const resultsBox = document.getElementById('search-results');

        searchInput.addEventListener('focus', () => resultsBox.style.display = 'block');
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.style.display = 'none';
            }
        });
    </script>
</body>

</html>