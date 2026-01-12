<?php
/**
 * Edit User - Editar Usuario
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
    <title>Editar Usuario | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .eu-header {
            margin-bottom: 25px;
        }

        .eu-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .eu-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 0;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .section-header {
            background: #f8fafc;
            padding: 12px 25px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-body {
            padding: 25px;
        }

        .eu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .eu-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .eu-form-group label span {
            color: #dc2626;
        }

        .hint-text {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        .section-divider {
            border-top: 1px solid #f1f5f9;
        }

        .check-container {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
        }

        .check-container input {
            width: 18px;
            height: 18px;
        }

        .eu-footer {
            padding: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
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
                <div class="eu-header">
                    <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
                </div>

                <div class="eu-card">
                    <form action="#">
                        <!-- Personal Info -->
                        <div class="section-header">
                            <i class="fas fa-user"></i> Información Personal
                        </div>
                        <div class="section-body">
                            <div class="eu-grid">
                                <div class="eu-form-group">
                                    <label>Nombre de usuario <span>*</span></label>
                                    <input type="text" class="form-control" value="<?php echo $user; ?>">
                                    <p class="hint-text">Usuario único para iniciar sesión</p>
                                </div>
                                <div class="eu-form-group">
                                    <label>Nombre completo <span>*</span></label>
                                    <input type="text" class="form-control" value="Usuario Admin1">
                                </div>
                                <div class="eu-form-group">
                                    <label>Correo electrónico <span>*</span></label>
                                    <input type="email" class="form-control" value="admin1@gmial.com">
                                </div>
                                <div class="eu-form-group">
                                    <label>Rol <span>*</span></label>
                                    <select class="form-control">
                                        <option selected>Administrador</option>
                                        <option>Vendedor</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Security -->
                        <div class="section-header">
                            <i class="fas fa-lock"></i> Seguridad
                        </div>
                        <div class="section-body">
                            <div class="eu-grid">
                                <div class="eu-form-group">
                                    <label>Contraseña</label>
                                    <input type="password" class="form-control" placeholder="Ingrese la contraseña">
                                    <p class="hint-text">Dejar en blanco para mantener la contraseña actual</p>
                                </div>
                                <div class="eu-form-group">
                                    <label>Confirmar contraseña</label>
                                    <input type="password" class="form-control" placeholder="Confirme la contraseña">
                                </div>
                            </div>
                        </div>

                        <!-- Status & Preferences -->
                        <div class="section-header">
                            <i class="fas fa-cog"></i> Estado y Preferencias
                        </div>
                        <div class="section-body">
                            <div class="eu-grid">
                                <div>
                                    <label class="check-container">
                                        <input type="checkbox" checked>
                                        Usuario activo
                                    </label>
                                    <p class="hint-text" style="margin-left: 28px;">Usuario habilitado para iniciar
                                        sesión</p>
                                </div>
                                <div class="eu-form-group">
                                    <label><i class="fas fa-bars"></i> Tipo de Menú</label>
                                    <select class="form-control">
                                        <option selected>Horizontal</option>
                                        <option>Vertical (Sidebar)</option>
                                    </select>
                                    <p class="hint-text">Estilo de navegación preferido</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info (Placeholder per screenshot) -->
                        <div class="section-header"
                            style="color: #64748b; background: white; border-top: 1px solid #f1f5f9;">
                            <i class="fas fa-info-circle"></i> Información Adicional
                        </div>

                        <div class="eu-footer">
                            <a href="index.php" class="btn btn-secondary" style="padding: 10px 25px;">Cancelar</a>
                            <button type="submit" class="btn btn-primary"
                                style="padding: 10px 30px; background: #2563eb;">Actualizar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>