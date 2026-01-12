<?php
/**
 * User My Profile - Mi Perfil
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'dashboard'; // Highlight dashboard or nothing
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .p-header {
            margin-bottom: 25px;
        }

        .p-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .p-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 30px;
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .p-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .p-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .p-form-group label span {
            color: #dc2626;
        }

        .p-form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .p-form-group input:disabled {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }

        .hint-text {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        .p-section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #f1f5f9;
            padding-top: 25px;
        }

        .p-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .p-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .p-bottom-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #f1f5f9;
        }

        .p-bottom-info h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .p-bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .p-bottom-grid span {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 600;
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
                    <h1><i class="fas fa-user-cog"></i> Mi Perfil</h1>
                </div>

                <div class="p-card">
                    <form action="#">
                        <div class="p-grid">
                            <div class="p-form-group">
                                <label>Nombre de Usuario</label>
                                <input type="text" value="admin" disabled>
                                <p class="hint-text">No se puede cambiar el nombre de usuario</p>
                            </div>
                            <div class="p-form-group">
                                <label>Nombre Completo <span>*</span></label>
                                <input type="text" value="Usuario Administrador">
                            </div>
                            <div class="p-form-group">
                                <label>Email <span>*</span></label>
                                <input type="email" value="admin@logipharm.com">
                            </div>
                            <div class="p-form-group">
                                <label>Rol</label>
                                <input type="text" value="Administrador" disabled>
                                <p class="hint-text">Tu rol es asignado por un administrador</p>
                            </div>
                        </div>

                        <div class="p-section-title">
                            <i class="fas fa-key"></i> Cambiar Contraseña (Opcional)
                        </div>

                        <div class="p-grid-3">
                            <div class="p-form-group">
                                <label>Contraseña Actual</label>
                                <input type="password" placeholder="••••••••">
                                <p class="hint-text">Requerida solo si cambias la contraseña</p>
                            </div>
                            <div class="p-form-group">
                                <label>Nueva Contraseña</label>
                                <input type="password" placeholder="••••••••">
                            </div>
                            <div class="p-form-group">
                                <label>Confirmar Nueva Contraseña</label>
                                <input type="password" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="p-footer">
                            <a href="<?php echo $root; ?>index.php" class="btn btn-secondary"
                                style="padding: 10px 20px; border-radius: 8px;">
                                <i class="fas fa-arrow-left"></i> Volver al Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary"
                                style="padding: 10px 25px; border-radius: 8px; background: #2563eb;">
                                <i class="fas fa-save" style="margin-right: 8px;"></i> Actualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-bottom-info">
                    <h4><i class="fas fa-info-circle"></i> Información de la Cuenta</h4>
                    <div class="p-bottom-grid">
                        <div>
                            <strong>Fecha de Creación:</strong> <span>No disponible</span>
                        </div>
                        <div>
                            <strong>Último Acceso:</strong> <span>No disponible</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>