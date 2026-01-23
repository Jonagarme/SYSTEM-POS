<?php
/**
 * Edit User - Editar Usuario
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'usuarios_index';
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: index.php");
    exit;
}

// Handle form submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = $_POST['nombreUsuario'] ?? '';
    $nombreCompleto = $_POST['nombreCompleto'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $idRol = !empty($_POST['idRol']) ? (int) $_POST['idRol'] : null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $editadoPor = $_SESSION['user_id'] ?? 1;

    try {
        if (empty($nombreUsuario)) {
            throw new Exception("El nombre de usuario es obligatorio.");
        }

        // Validate unique username (excluding current user)
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE nombreUsuario = ? AND id != ? AND anulado = 0");
        $stmtCheck->execute([$nombreUsuario, $user_id]);
        if ($stmtCheck->fetch()) {
            throw new Exception("El nombre de usuario already exists.");
        }

        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nombreUsuario = ?, nombreCompleto = ?, email = ?, contrasenaHash = ?, idRol = ?, activo = ?, editadoPor = ?, editadoDate = NOW() WHERE id = ?");
            $stmt->execute([$nombreUsuario, $nombreCompleto, $email, $passwordHash, $idRol, $activo, $editadoPor, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombreUsuario = ?, nombreCompleto = ?, email = ?, idRol = ?, activo = ?, editadoPor = ?, editadoDate = NOW() WHERE id = ?");
            $stmt->execute([$nombreUsuario, $nombreCompleto, $email, $idRol, $activo, $editadoPor, $user_id]);
        }

        // Log Audit
        require_once '../../includes/audit.php';
        registrarAuditoria('Usuarios', 'EDITAR', 'usuarios', $user_id, "Usuario actualizado: $nombreUsuario ($nombreCompleto)");

        $message = "Usuario actualizado correctamente.";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch();

if (!$userData) {
    die("Usuario no encontrado.");
}

// Fetch roles from database
$stmtRoles = $pdo->query("SELECT id, nombre FROM roles WHERE anulado = 0 ORDER BY nombre ASC");
$roles = $stmtRoles->fetchAll();
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

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .eu-grid {
                grid-template-columns: 1fr;
            }

            .eu-footer {
                flex-direction: column-reverse;
                gap: 15px;
                padding: 20px;
            }

            .eu-footer a,
            .eu-footer button {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .section-body {
                padding: 20px;
            }

            .eu-header {
                padding: 0 10px;
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
                <div class="eu-header">
                    <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"
                        style="padding: 15px; background: #dcfce7; color: #15803d; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"
                        style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="eu-card">
                    <form action="" method="POST">
                        <!-- Personal Info -->
                        <div class="section-header">
                            <i class="fas fa-user"></i> Información Personal
                        </div>
                        <div class="section-body">
                            <div class="eu-grid">
                                <div class="eu-form-group">
                                    <label>Nombre de usuario <span>*</span></label>
                                    <input type="text" class="form-control" name="nombreUsuario"
                                        value="<?php echo htmlspecialchars($userData['nombreUsuario']); ?>">
                                    <p class="hint-text">Usuario único para iniciar sesión</p>
                                </div>
                                <div class="eu-form-group">
                                    <label>Nombre completo <span>*</span></label>
                                    <input type="text" class="form-control" name="nombreCompleto"
                                        value="<?php echo htmlspecialchars($userData['nombreCompleto']); ?>">
                                </div>
                                <div class="eu-form-group">
                                    <label>Correo electrónico <span>*</span></label>
                                    <input type="email" class="form-control" name="email"
                                        value="<?php echo htmlspecialchars($userData['email']); ?>">
                                </div>
                                <div class="eu-form-group">
                                    <label>Rol <span>*</span></label>
                                    <select class="form-control" name="idRol">
                                        <option value="">Sin rol asignado</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?php echo $rol['id']; ?>" <?php echo ($userData['idRol'] == $rol['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($rol['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                        <input type="checkbox" name="activo" value="1" <?php echo ($userData['activo'] ?? 1) ? 'checked' : ''; ?>>
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