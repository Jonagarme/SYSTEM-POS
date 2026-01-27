<?php
/**
 * Login Page - Sistema POS
 */
session_start();
require_once 'includes/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            // First try SHA256 (as used in the database for 'admin')
            $hashedPassword = hash('sha256', $password);

            $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre 
                                   FROM usuarios u 
                                   LEFT JOIN roles r ON u.idRol = r.id 
                                   WHERE u.nombreUsuario = ? AND u.activo = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // Check SHA256 first
                if ($user['contrasenaHash'] === $hashedPassword) {
                    $authenticated = true;
                } else {
                    // Try password_verify (for standard PHP hashing)
                    $authenticated = password_verify($password, $user['contrasenaHash']);
                }

                if ($authenticated) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombreCompleto'];
                    $_SESSION['username'] = $user['nombreUsuario'];
                    $_SESSION['role'] = $user['rol_nombre'] ?? 'Usuario';
                    $_SESSION['idRol'] = $user['idRol'];

                    // Log audit
                    require_once 'includes/audit.php';
                    registrarAuditoria('Login', 'LOGIN', 'usuarios', $user['id'], 'Inicio de sesión exitoso');

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'El usuario no existe o está inactivo.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el sistema: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema POS</title>
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
            font-size: 1.75rem;
            color: #1e293b;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #64748b;
            font-size: 0.95rem;
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

        .btn-login {
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

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }

        .btn-login:active {
            transform: translateY(0);
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
                    <i class="fas fa-prescription-bottle-alt"></i>
                </div>
                <h1>Bienvenido</h1>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="username" class="form-control"
                            placeholder="nombre.usuario" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label for="password" style="margin-bottom: 0;">Contraseña</label>
                        <a href="restablecer_password.php"
                            style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 600;">¿Olvidaste
                            tu contraseña?</a>
                    </div>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Iniciar Sesión <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
        <div class="footer-text">
            &copy;
            <?php echo date('Y'); ?> Logipharm | Warehouse POS System
        </div>
    </div>
</body>

</html>