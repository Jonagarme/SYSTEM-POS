<?php
/**
 * Audit System Helper
 */

/**
 * Registra un evento en la tabla de auditoría.
 * 
 * @param string $modulo El módulo donde ocurre la acción (ej: Ventas, Productos)
 * @param string $accion La acción realizada (LOGIN, CREAR, EDITAR, ELIMINAR, etc.)
 * @param string $entidad La tabla o entidad afectada (ej: usuarios, productos)
 * @param mixed $idEntidad El ID del registro afectado (puede ser null)
 * @param string $descripcion Una descripción amigable de lo que sucedió
 * @param string $extra Información adicional en formato JSON o texto (opcional)
 */
function registrarAuditoria($modulo, $accion, $entidad, $idEntidad, $descripcion, $extra = null)
{
    global $pdo;

    // Asegurar que session_start esté activo para obtener el usuario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $idUsuario = $_SESSION['user_id'] ?? 0;
    $usuario = $_SESSION['username'] ?? 'System';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Intentar obtener el host (opcional, puede ser lento en algunos servidores)
    $host = 'Unknown';
    if ($ip !== '127.0.0.1' && $ip !== '::1') {
        // En algunos entornos gethostbyaddr puede tardar segundos si no hay DNS.
        // Lo dejamos como Unknown o IP para velocidad si prefieres.
        $host = $ip;
    } else {
        $host = 'localhost';
    }

    $origen = 'UI'; // Web Interface

    try {
        $stmt = $pdo->prepare("INSERT INTO auditoria (fecha, idUsuario, usuario, modulo, accion, entidad, idEntidad, descripcion, ip, host, origen, extra) 
                               VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $idUsuario,
            $usuario,
            $modulo,
            $accion,
            $entidad,
            $idEntidad,
            $descripcion,
            $ip,
            $host,
            $origen,
            $extra
        ]);
    } catch (Exception $e) {
        // En auditoría, usualmente no queremos que un error de log detenga el sistema principal
        error_log("Error de auditoría: " . $e->getMessage());
    }
}
