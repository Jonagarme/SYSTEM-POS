<?php
/**
 * Sales Returns List - Lista de Devoluciones
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'ventas_devoluciones';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Devoluciones | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --return-red: #c5221f;
            --return-red-dark: #a51b19;
        }

        .returns-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .returns-title h1 {
            font-size: 1.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .returns-title h1 i {
            color: var(--return-red);
        }

        .returns-title p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 0.85rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card-red {
            background: var(--return-red);
            color: white;
            padding: 20px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(197, 34, 31, 0.2);
        }

        .summary-card-red .info h3 {
            font-size: 0.85rem;
            font-weight: 500;
            margin: 0 0 5px 0;
            opacity: 0.9;
        }

        .summary-card-red .info .value {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .summary-card-red .icon {
            font-size: 2.5rem;
            opacity: 0.3;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-item label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            font-size: 1.1rem;
            color: #475569;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .btn-green-lg {
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-green-lg:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-blue {
            background: #2563eb;
            color: white;
        }

        .btn-blue:hover {
            background: #1d4ed8;
        }

        .btn-outline {
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-filter {
            height: 42px;
            padding: 0 20px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            flex: 1;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 768px) {
            .returns-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn-green-lg {
                width: 100%;
                justify-content: center;
            }

            .table-responsive-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
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
                <div class="returns-header">
                    <div class="returns-title">
                        <h1><i class="fas fa-undo"></i> Lista de Devoluciones</h1>
                        <p>Historial de devoluciones realizadas (MODO DEMO)</p>
                    </div>
                    <a href="nueva_devolucion.php" class="btn-green-lg">
                        <i class="fas fa-plus"></i> Nueva Devolución
                    </a>
                </div>

                <div class="summary-grid">
                    <div class="summary-card-red">
                        <div class="info">
                            <h3>Total Devoluciones</h3>
                            <div class="value">0</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list-ul"></i>
                        </div>
                    </div>
                    <div class="summary-card-red" style="background: #dc2626;">
                        <div class="info">
                            <h3>$ Monto Total Devuelto</h3>
                            <div class="value">$0.00</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>

                <div class="pos-panel" style="margin-bottom: 25px; padding: 25px;">
                    <div class="filter-grid">
                        <div class="filter-item">
                            <label>Número Devolución</label>
                            <input type="text" class="form-control" placeholder="DEV-20251028-0001">
                        </div>
                        <div class="filter-item">
                            <label>Número Factura</label>
                            <input type="text" class="form-control" placeholder="FAC-20251028-0001">
                        </div>
                        <div class="filter-item">
                            <label>Cliente</label>
                            <input type="text" class="form-control" placeholder="Nombre del cliente">
                        </div>
                        <div class="filter-item">
                            <label>Motivo</label>
                            <select class="form-control">
                                <option>Todos los motivos</option>
                                <option>Producto Defectuoso</option>
                                <option>Error en Cobro</option>
                                <option>Cambio de Opinión</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label>Usuario</label>
                            <input type="text" class="form-control" placeholder="Nombre de usuario">
                        </div>
                        <div class="filter-item">
                            <label>Fecha Inicio</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="filter-item">
                            <label>Fecha Fin</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="filter-actions">
                            <button class="btn-filter btn-blue"><i class="fas fa-search"></i> Filtrar</button>
                            <button class="btn-filter btn-outline"><i class="fas fa-times"></i> Limpiar</button>
                        </div>
                    </div>
                </div>

                <div class="empty-state">
                    <i class="fas fa-undo"></i>
                    <h3>No se encontraron devoluciones</h3>
                    <p>No hay devoluciones que coincidan con los filtros aplicados.</p>
                    <a href="nueva_devolucion.php" class="btn-green-lg" style="background: #10b981;">
                        <i class="fas fa-plus"></i> Crear Primera Devolución
                    </a>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>