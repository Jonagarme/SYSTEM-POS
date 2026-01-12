<?php
/**
 * New Caja Form - Crear Nueva Caja
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'caja_index';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Caja | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .nc-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-back-nc {
            background: #64748b;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nc-panel {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-bottom: 30px;
        }

        .nc-panel-header {
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nc-panel-body {
            padding: 30px;
        }

        .nc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group-nc label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .nc-info-box {
            background: #ecfeff;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #0891b2;
            margin-bottom: 25px;
        }

        .nc-info-box h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #155e75;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nc-info-box ul {
            list-style: none;
            padding: 0;
            font-size: 0.85rem;
            color: #164e63;
        }

        .nc-info-box ul li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nc-info-box ul li::before {
            content: "\f111";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 0.4rem;
            color: #0891b2;
        }

        .suggestions-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .sugg-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0d9488;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sugg-list {
            list-style: none;
            padding: 0;
            font-size: 0.8rem;
            color: #1e293b;
        }

        .sugg-list li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
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
                <div class="nc-header">
                    <h1><i class="fas fa-plus-circle"></i> Crear Nueva Caja</h1>
                    <a href="index.php" class="btn-back-nc"><i class="fas fa-arrow-left"></i> Volver a Lista</a>
                </div>

                <div class="nc-panel">
                    <div class="nc-panel-header">
                        <i class="fas fa-keyboard"></i> Información de la Caja
                    </div>
                    <div class="nc-panel-body">
                        <div class="nc-grid">
                            <div class="form-group-nc">
                                <label><i class="fas fa-barcode"></i> Código de Caja *</label>
                                <input type="text" class="form-control" placeholder="Ej: CAJA001">
                                <p style="font-size: 0.7rem; color: #64748b; margin-top: 5px;">Código único
                                    identificador de la caja</p>
                            </div>
                            <div class="form-group-nc">
                                <label><i class="fas fa-tag"></i> Nombre de la Caja *</label>
                                <input type="text" class="form-control" placeholder="Ej: Caja Principal">
                                <p style="font-size: 0.7rem; color: #64748b; margin-top: 5px;">Nombre descriptivo de la
                                    caja</p>
                            </div>
                        </div>

                        <div class="nc-info-box">
                            <h4><i class="fas fa-info-circle"></i> Información Importante</h4>
                            <ul>
                                <li>La caja se creará en estado <strong>activo</strong> por defecto</li>
                                <li>El código debe ser único en el sistema</li>
                                <li>Una vez creada, podrá activar/desactivar la caja desde la lista</li>
                                <li>Solo las cajas activas están disponibles para abrir turno</li>
                            </ul>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                            <button class="btn btn-secondary" style="padding: 10px 30px;"><i class="fas fa-times"></i>
                                Cancelar</button>
                            <button class="btn btn-primary" style="padding: 10px 30px; background: #2563eb;"><i
                                    class="fas fa-save"></i> Crear Caja</button>
                        </div>
                    </div>
                </div>

                <div class="suggestions-section">
                    <div>
                        <div class="sugg-title"><i class="fas fa-lightbulb"></i> Sugerencias</div>
                        <p style="font-size: 0.8rem; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Códigos
                            recomendados:</p>
                        <ul class="sugg-list">
                            <li><span style="color: #db2777; font-weight: 700;">CAJA001</span> - Caja Principal</li>
                            <li><span style="color: #db2777; font-weight: 700;">CAJA002</span> - Caja Secundaria</li>
                            <li><span style="color: #db2777; font-weight: 700;">CAJA003</span> - Caja Principal</li>
                            <li><span style="color: #db2777; font-weight: 700;">VENTA01</span> - Caja Ventas</li>
                        </ul>
                    </div>
                    <div>
                        <p
                            style="font-size: 0.8rem; font-weight: 700; color: #1e293b; margin-top: 30px; margin-bottom: 10px;">
                            Nombres descriptivos:</p>
                        <ul class="sugg-list">
                            <li>Caja Principal</li>
                            <li>Caja Mostrador</li>
                            <li>Caja Principal</li>
                            <li>Caja Emergencia</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>