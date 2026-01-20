<?php
/**
 * User Profile / Details - Perfil de Usuario
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'usuarios_index';
$user = $_GET['user'] ?? 'admin1';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .p-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .p-header h1 {
            font-size: 1.25rem;
            color: #1e293b;
            font-weight: 700;
        }

        .p-grid-top {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .card-profile-main {
            background: white;
            border-radius: 12px;
            padding: 30px;
            display: flex;
            gap: 40px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            position: relative;
        }

        .status-badge-p {
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            background: #059669;
            color: white;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 700;
            border: 2px solid white;
        }

        .p-info-main {
            flex: 1;
        }

        .p-info-main h2 {
            font-size: 1.6rem;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .p-info-main .username {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: block;
        }

        .p-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item .lbl {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }

        .info-item .val {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
        }

        .info-item .val a {
            color: #2563eb;
            text-decoration: none;
        }

        .side-panels {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .side-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .side-panel-header {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .side-panel-body {
            padding: 15px 20px;
        }

        .history-item {
            display: flex;
            gap: 12px;
            font-size: 0.85rem;
        }

        .history-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #3b82f6;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .history-text strong {
            display: block;
            color: #1e293b;
        }

        .history-text span {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-qa {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            border: 1px solid;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-qa-warn {
            color: #f59e0b;
            border-color: #fef3c7;
            background: white;
        }

        .btn-qa-danger {
            color: #dc2626;
            border-color: #fee2e2;
            background: white;
        }

        .p-grid-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .bottom-card {
            background: white;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .bottom-card h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Responsive Improvements */
        @media (max-width: 992px) {
            .p-grid-top {
                grid-template-columns: 1fr;
            }

            .card-profile-main {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }

            .p-info-grid {
                justify-items: center;
            }
        }

        @media (max-width: 768px) {
            .p-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .p-header div {
                width: 100%;
            }

            .p-header a {
                flex: 1;
                justify-content: center;
            }

            .p-grid-bottom {
                grid-template-columns: 1fr;
            }

            .p-info-grid {
                grid-template-columns: 1fr;
            }

            .content-wrapper {
                padding: 15px;
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
                <div class="p-header">
                    <h1><i class="fas fa-user-circle"></i> Detalle de Usuario</h1>
                    <div style="display: flex; gap: 10px;">
                        <a href="editar.php?user=<?php echo $user; ?>" class="btn btn-primary"
                            style="background: #2563eb; font-size: 0.85rem;">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="index.php" class="btn btn-secondary" style="font-size: 0.85rem;">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="p-grid-top">
                    <div class="card-profile-main">
                        <div class="avatar-large">
                            <?php echo strtoupper(substr($user, 0, 1)); ?>
                            <span class="status-badge-p">Activo</span>
                        </div>
                        <div class="p-info-main">
                            <h2>Usuario Admin1</h2>
                            <span class="username">@
                                <?php echo $user; ?>
                            </span>

                            <div class="p-info-grid">
                                <div class="info-item">
                                    <span class="lbl">Email:</span>
                                    <span class="val"><a href="mailto:admin1@gmial.com">admin1@gmial.com</a></span>
                                </div>
                                <div class="info-item">
                                    <span class="lbl">Rol:</span>
                                    <span class="val">Administrador</span>
                                </div>
                                <div class="info-item">
                                    <span class="lbl">Estado:</span>
                                    <span class="val">Activo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="side-panels">
                        <div class="side-panel">
                            <div class="side-panel-header"><i class="fas fa-history"></i> Historial de Cambios</div>
                            <div class="side-panel-body">
                                <div class="history-item">
                                    <div class="history-dot"></div>
                                    <div class="history-text">
                                        <strong>Usuario Creado</strong>
                                        <span>29/12/2025 23:17</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="side-panel">
                            <div class="side-panel-header"><i class="fas fa-tools"></i> Acciones Rápidas</div>
                            <div class="side-panel-body">
                                <div class="quick-actions">
                                    <button class="btn-qa btn-qa-warn"><i class="fas fa-pause"></i> Desactivar
                                        Usuario</button>
                                    <button class="btn-qa btn-qa-danger"><i class="fas fa-ban"></i> Anular
                                        Usuario</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-grid-bottom">
                    <div class="bottom-card">
                        <h4><i class="fas fa-clock" style="color: #2563eb;"></i> Fechas Importantes</h4>
                        <div class="info-item">
                            <span class="lbl">Creado:</span>
                            <span class="val" style="font-size: 1rem;">29/12/2025 23:17</span>
                        </div>
                    </div>
                    <div class="bottom-card" style="border-left-color: #3b82f6;">
                        <h4><i class="fas fa-shield-alt" style="color: #3b82f6;"></i> Información de Seguridad</h4>
                        <div class="p-info-grid">
                            <div class="info-item">
                                <span class="lbl">Usuario único:</span>
                                <span class="val">
                                    <?php echo $user; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="lbl">Acceso al sistema:</span>
                                <span class="val" style="color: #059669;">Permitido</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>