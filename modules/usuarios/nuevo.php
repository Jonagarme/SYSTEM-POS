<?php
/**
 * Create New User - Crear Nuevo Usuario
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'usuarios_nuevo';

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
    $creadoPor = $_SESSION['user_id'] ?? 1;

    try {
        if (empty($nombreUsuario) || empty($password)) {
            throw new Exception("Usuario y contraseña son obligatorios.");
        }

        // Validate unique username
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE nombreUsuario = ? AND anulado = 0");
        $stmtCheck->execute([$nombreUsuario]);
        if ($stmtCheck->fetch()) {
            throw new Exception("El nombre de usuario already exists.");
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, nombreCompleto, email, contrasenaHash, idRol, activo, creadoPor, creadoDate, anulado) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)");
        $stmt->execute([$nombreUsuario, $nombreCompleto, $email, $passwordHash, $idRol, $activo, $creadoPor]);

        $newId = $pdo->lastInsertId();

        // Log Audit
        require_once '../../includes/audit.php';
        registrarAuditoria('Usuarios', 'CREAR', 'usuarios', $newId, "Nuevo usuario creado: $nombreUsuario ($nombreCompleto)");

        $message = "Usuario creado correctamente.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
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
    <title>Crear Nuevo Usuario | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nu-header {
            margin-bottom: 25px;
        }

        .nu-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nu-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 30px;
            border: 1px solid #f1f5f9;
        }

        .nu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .nu-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .nu-form-group label span {
            color: #dc2626;
        }

        .hint-text {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        .nu-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .nu-grid {
                grid-template-columns: 1fr;
            }

            .nu-footer {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .nu-footer a,
            .nu-footer button {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .nu-card {
                padding: 20px;
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
                <div class="nu-header">
                    <h1><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
                </div>

                <div class="nu-card">
                    <form action="#">
                        <div class="nu-grid">
                            <div class="nu-form-group">
                                <label>Nombre de Usuario <span>*</span></label>
                                <input type="text" name="nombreUsuario" class="form-control"
                                    placeholder="Ingrese el usuario" required>
                                <p class="hint-text">Usuario único para iniciar sesión</p>
                            </div>
                            <div class="nu-form-group">
                                <label>Nombre Completo <span>*</span></label>
                                <input type="text" name="nombreCompleto" class="form-control"
                                    placeholder="Ingrese el nombre completo" required>
                            </div>

                            <div class="nu-form-group">
                                <label>Email <span>*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com"
                                    required>
                            </div>
                            <div class="nu-form-group">
                                <label>Contraseña <span>*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••"
                                    required>
                            </div>

                            <div class="nu-form-group">
                                <label>Rol</label>
                                <select class="form-control" name="idRol">
                                    <option value="">Sin rol asignado</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id']; ?>">
                                            <?php echo htmlspecialchars($rol['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="nu-form-group">
                                <label><i class="fas fa-bars"></i> Tipo de Menú</label>
                                <select class="form-control">
                                    <option>Horizontal</option>
                                    <option>Vertical (Sidebar)</option>
                                </select>
                                <p class="hint-text">Estilo de navegación preferido</p>
                            </div>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label class="check-container">
                                <input type="checkbox" name="activo" value="1" checked>
                                Usuario Activo
                            </label>
                            <p class="hint-text" style="margin-left: 28px;">Usuario habilitado para iniciar sesión</p>
                        </div>

                        <div class="nu-footer">
                            <a href="index.php" class="btn btn-secondary" style="padding: 10px 25px;"><i
                                    class="fas fa-arrow-left"></i> Cancelar</a>
                            <button type="submit" class="btn btn-primary"
                                style="padding: 10px 30px; background: #2563eb;"><i class="fas fa-save"></i> Crear
                                Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>