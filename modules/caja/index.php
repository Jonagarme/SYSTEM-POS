<?php
/**
 * Caja Management - Gestión de Cajas
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'caja_index';

// Obtener cajas desde la base de datos
$stmt = $pdo->query("SELECT *, CASE 
                        WHEN activa = 1 THEN 'Activa' 
                        ELSE 'Inactiva' 
                    END as estado_nombre,
                    CASE 
                        WHEN activa = 1 THEN 'badge-success' 
                        ELSE 'badge-danger' 
                    END as status_class
                    FROM cajas WHERE anulado = 0 ORDER BY nombre ASC");
$cajas = $stmt->fetchAll();

$totales = [
    'total' => count($cajas),
    'activas' => 0,
    'inactivas' => 0
];

foreach ($cajas as $c) {
    if ($c['activa'] == 1)
        $totales['activas']++;
    else
        $totales['inactivas']++;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cajas | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .cajas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .cajas-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-btns {
            display: flex;
            gap: 10px;
        }

        .btn-caja-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-blue {
            background: #2563eb;
        }

        .btn-cyan {
            background: #0891b2;
        }

        .btn-green {
            background: #059669;
        }

        .summary-grid-cajas {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .s-caja-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border: 1px solid #f1f5f9;
        }

        .s-caja-card .info .lbl {
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
        }

        .s-caja-card .info .val {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1e293b;
        }

        .s-caja-card .info .val.text-green {
            color: #059669;
        }

        .s-caja-card .icon {
            font-size: 1.5rem;
            color: #1e293b;
        }

        .cajas-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .table-header-custom {
            padding: 15px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .c-table {
            width: 100%;
            border-collapse: collapse;
        }

        .c-table th {
            text-align: left;
            padding: 12px 20px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .c-table td {
            padding: 12px 20px;
            font-size: 0.85rem;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-active {
            background: #059669;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-btns-c {
            display: flex;
            gap: 5px;
        }

        .btn-act {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            background: white;
        }

        .btn-act.edit {
            color: #2563eb;
            border-color: #2563eb;
        }

        .btn-act.pause {
            color: #f59e0b;
            border-color: #f59e0b;
        }

        .btn-act.lock {
            color: #059669;
            border-color: #059669;
        }

        .btn-act.delete {
            color: #dc2626;
            border-color: #dc2626;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 992px) {
            .summary-grid-cajas {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .cajas-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-btns {
                flex-direction: column;
                width: 100%;
            }

            .btn-caja-action {
                width: 100%;
                justify-content: center;
            }

            .summary-grid-cajas {
                grid-template-columns: 1fr;
            }

            .table-responsive-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .c-table {
                min-width: 600px;
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
                <div class="cajas-header">
                    <h1><i class="fas fa-cash-register"></i> Gestión de Cajas</h1>
                    <div class="header-btns">
                        <a href="nueva_caja.php" class="btn-caja-action btn-blue"><i class="fas fa-plus"></i> Nueva
                            Caja</a>
                        <a href="estado.php" class="btn-caja-action btn-cyan"><i class="fas fa-list-alt"></i> Estado de
                            Caja</a>
                        <a href="aperturas.php" class="btn-caja-action btn-green"><i class="fas fa-unlock-alt"></i>
                            Abrir Caja</a>
                    </div>
                </div>

                <div class="summary-grid-cajas">
                    <div class="s-caja-card">
                        <div class="info">
                            <span class="lbl">TOTAL DE CAJAS</span>
                            <span class="val"><?php echo $totales['total']; ?></span>
                        </div>
                        <i class="fas fa-boxes icon"></i>
                    </div>
                    <div class="s-caja-card">
                        <div class="info">
                            <span class="lbl" style="color: #059669;">CAJAS ACTIVAS</span>
                            <span class="val"><?php echo $totales['activas']; ?></span>
                        </div>
                        <i class="fas fa-check-circle icon"></i>
                    </div>
                    <div class="s-caja-card">
                        <div class="info">
                            <span class="lbl" style="color: #f59e0b;">CAJAS INACTIVAS</span>
                            <span class="val"><?php echo $totales['inactivas']; ?></span>
                        </div>
                        <i class="fas fa-times-circle icon"></i>
                    </div>
                    <div class="s-caja-card">
                        <div class="info">
                            <span class="lbl" style="color: #0ea5e9;">ESTADO DEL SISTEMA</span>
                            <span class="val text-green">Operativo</span>
                        </div>
                        <i class="fas fa-server icon"></i>
                    </div>
                </div>

                <div class="cajas-table-container">
                    <div class="table-header-custom">
                        <i class="fas fa-list-ul"></i> Listado de Cajas
                    </div>
                    <div class="table-responsive-container">
                        <table class="c-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cajas as $c): ?>
                                    <tr>
                                        <td style="color: #2563eb; font-weight: 700;"><?php echo $c['codigo']; ?></td>
                                        <td style="font-weight: 600;"><?php echo $c['nombre']; ?></td>
                                        <td>
                                            <span class="badge-active"><i class="fas fa-check"></i> Activa</span>
                                        </td>
                                        <td>
                                            <div class="action-btns-c">
                                                <button class="btn-act edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn-act pause"><i class="fas fa-pause"></i></button>
                                                <button class="btn-act lock"><i class="fas fa-lock"></i></button>
                                                <button class="btn-act delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>