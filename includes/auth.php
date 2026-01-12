<?php
/**
 * Authentication check - Secure Version
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Buscar login.php subiendo niveles de forma dinámica
    $prefix = '';
    $max_depth = 5;
    $login_found = false;

    for ($i = 0; $i < $max_depth; $i++) {
        if (file_exists($prefix . 'login.php')) {
            $login_found = true;
            break;
        }
        $prefix .= '../';
    }

    $redirect_url = $login_found ? $prefix . 'login.php' : '/login.php';

    // Debug (opcional, comentar en producción)
    // echo "Redirigiendo a: $redirect_url"; exit;

    header("Location: $redirect_url");
    exit();
}
