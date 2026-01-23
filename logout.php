<?php
/**
 * Logout script
 */
session_start();

if (isset($_SESSION['user_id'])) {
    require_once 'includes/db.php';
    require_once 'includes/audit.php';
    registrarAuditoria('Login', 'LOGOUT', 'usuarios', $_SESSION['user_id'], 'Cierre de sesión');
}

session_unset();
session_destroy();

header('Location: login.php');
exit;
