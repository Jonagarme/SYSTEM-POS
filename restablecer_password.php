<?php
/**
 * Restablecer Contraseña - Sistema POS
 */
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';
$step = 1; // 1: Solicitar, 2: Verificar y cambiar

// Procesar el paso de CANCELAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reset'])) {
    $token = $_POST['token'] ?? '';
    if (!empty($token)) {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET restablecerContraseña = NULL, restablecerContraseñaDate = NULL WHERE restablecerContraseña = ?");
            $stmt->execute([$token]);

            if ($stmt->rowCount() > 0) {
                $success = "La solicitud de restablecimiento ha sido cancelada correctamente.";
                $step = 1;
            } else {
                $error = "El token ingresado no es válido.";
                $step = 2;
                $username_to_reset = $_POST['username'] ?? '';
            }
        } catch (PDOException $e) {
            $error = 'Error al cancelar: ' . $e->getMessage();
            $step = 2;
        }
    } else {
        $error = "Por favor, ingresa el token para poder cancelar la solicitud.";
        $step = 2;
        $username_to_reset = $_POST['username'] ?? '';
    }
}

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Procesar el paso 1: Solicitar restablecimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = $_POST['email'] ?? '';

    if (!empty($email)) {
        try {
            // Buscar usuario por email o nombreUsuario
            $stmt = $pdo->prepare("SELECT id, nombreUsuario, email FROM usuarios WHERE (email = ? OR nombreUsuario = ?) AND activo = 1");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generar un token único (como contraseña temporal o token de validación)
                $token = bin2hex(random_bytes(16));
                $tokenHash = password_hash($token, PASSWORD_DEFAULT);

                // Guardar en la base de datos (usamos el campo restablecerContraseña para el token hash)
                $stmt = $pdo->prepare("UPDATE usuarios SET restablecerContraseña = ?, restablecerContraseñaDate = NOW(), restablecerContraseñaCount = COALESCE(restablecerContraseñaCount, 0) + 1 WHERE id = ?");
                $stmt->execute([$token, $user['id']]);

                // En un sistema real, aquí se enviaría un correo con el token.
                // Para propósitos de desarrollo, mostraremos el token en pantalla.
                $success = "Se ha generado una solicitud de restablecimiento. <br><strong>TOKEN DE PRUEBA: $token</strong><br>Por favor, ingresa este token a continuación.";
                $step = 2;
                $username_to_reset = $user['nombreUsuario'];
            } else {
                $error = 'No se encontró ningún usuario activo con ese correo o nombre de usuario.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el sistema: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, ingresa tu correo electrónico o nombre de usuario.';
    }
}

// Procesar el paso 2: Cambiar la contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $username = $_POST['username'] ?? '';
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($username) && !empty($token) && !empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = 'Las contraseñas no coinciden.';
            $step = 2;
            $username_to_reset = $username;
        } else {
            try {
                // Verificar el token y que no haya pasado más de 1 hora
                $stmt = $pdo->prepare("SELECT id FROM usuarios 
                                       WHERE nombreUsuario = ? 
                                       AND restablecerContraseña = ? 
                                       AND restablecerContraseñaDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                                       AND activo = 1");
                $stmt->execute([$username, $token]);
                $user = $stmt->fetch();

                if ($user) {
                    // Actualizar la contraseña (usamos password_hash para seguridad moderna)
                    $newHash = password_hash($new_password, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("UPDATE usuarios 
                                           SET contrasenaHash = ?, 
                                               restablecerContraseña = NULL, 
                                               restablecerContraseñaDate = NULL 
                                           WHERE id = ?");
                    $stmt->execute([$newHash, $user['id']]);

                    // Log audit
                    require_once 'includes/audit.php';
                    registrarAuditoria('Usuarios', 'UPDATE', 'usuarios', $user['id'], 'Contraseña restablecida exitosamente');

                    $success = 'Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.';
                    $step = 3; // Éxito total
                } else {
                    $error = 'El token es inválido o ha expirado.';
                    $step = 2;
                    $username_to_reset = $username;
                }
            } catch (PDOException $e) {
                $error = 'Error en el sistema: ' . $e->getMessage();
                $step = 2;
            }
        }
    } else {
        $error = 'Por favor, completa todos los campos.';
        $step = 2;
        $username_to_reset = $username;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-overlay: rgba(15, 23, 42, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('assets/img/login-bg.png') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-overlay);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header .logo-icon {
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 20px;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .login-header h1 {
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.2s;
            outline: none;
            background: #fff;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-action {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-action:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #fee2e2;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message {
            background: #f0fdf4;
            color: #16a34a;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #dcfce7;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .back-to-login a:hover {
            color: var(--primary);
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Restablecer Contraseña</h1>
                <?php if ($step == 1): ?>
                    <p>Ingresa tu correo o usuario para recibir un token de recuperación.</p>
                <?php elseif ($step == 2): ?>
                    <p>Ingresa el token y tu nueva contraseña.</p>
                <?php else: ?>
                    <p>Proceso completado con éxito.</p>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Correo Electrónico o Usuario</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="text" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com"
                                required autofocus>
                        </div>
                    </div>

                    <button type="submit" name="request_reset" class="btn-action">
                        Enviar Token <i class="fas fa-paper-plane"></i>
                    </button>

                    <div class="back-to-login">
                        <a href="login.php"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a>
                    </div>
                </form>
            <?php elseif ($step == 2): ?>
                <form method="POST">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username_to_reset); ?>">

                    <div class="form-group">
                        <label for="token">Token de Recuperación</label>
                        <div class="input-wrapper">
                            <i class="fas fa-ticket-alt"></i>
                            <input type="text" name="token" id="token" class="form-control"
                                placeholder="Ingresa el token recibido" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="new_password" id="new_password" class="form-control"
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn-action">
                        Cambiar Contraseña <i class="fas fa-save"></i>
                    </button>

                    <button type="submit" name="cancel_reset" class="btn-action"
                        style="background: #ef4444; margin-top: 10px;">
                        Cancelar y Limpiar <i class="fas fa-times"></i>
                    </button>
                </form>
            <?php else: ?>
                <div class="back-to-login">
                    <a href="login.php" class="btn-action" style="color: white; text-decoration: none;">
                        Iniciar Sesión <i class="fas fa-sign-in-alt"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="footer-text">
            &copy;
            <?php echo date('Y'); ?> Logipharm | Warehouse POS System
        </div>
    </div>
</body>

</html>