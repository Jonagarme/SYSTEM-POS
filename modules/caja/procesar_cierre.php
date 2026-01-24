<?php
session_start();
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSesion = $_POST['id_sesion'] ?? null;
    $totalFisico = $_POST['total_fisico'] ?? 0;
    $totalSistema = $_POST['total_sistema'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? '';
    $idUsuario = $_SESSION['user_id'] ?? 1;

    if (!$idSesion) {
        die("Error: No se identificó la sesión de caja.");
    }

    try {
        $now = date('Y-m-d H:i:s');
        $diferencia = $totalFisico - $totalSistema;

        // 1. Actualizar el registro en cierres_caja
        $stmt = $pdo->prepare("UPDATE cierres_caja 
                               SET fechaCierre = ?, 
                                   idUsuarioCierre = ?,
                                   saldoTeoricoSistema = ?,
                                   totalContadoFisico = ?, 
                                   diferencia = ?,
                                   estado = 'CERRADA',
                                   anulado = 0
                               WHERE id = ?");
        $stmt->execute([
            $now,
            $idUsuario,
            $totalSistema,
            $totalFisico,
            $diferencia,
            $idSesion
        ]);

        header("Location: cierres.php?success=cierre&id_print=" . $idSesion);
    } catch (PDOException $e) {
        die("Error al cerrar caja: " . $e->getMessage());
    }
} else {
    header("Location: estado.php");
}
