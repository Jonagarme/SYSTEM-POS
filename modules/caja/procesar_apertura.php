<?php
session_start();
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCaja = $_POST['caja_id'] ?? null;
    $saldoInicial = $_POST['monto_inicial'] ?? 0;
    $observaciones = $_POST['observaciones'] ?? '';
    $idUsuario = $_SESSION['user_id'] ?? 1; // Default to 1 if not logged in

    if (!$idCaja) {
        die("Error: No se seleccionó una caja.");
    }

    try {
        // Verificar si ya hay una caja abierta
        $stmt_check = $pdo->prepare("SELECT id FROM cierres_caja WHERE idCaja = ? AND estado = 'ABIERTA'");
        $stmt_check->execute([$idCaja]);
        if ($stmt_check->fetch()) {
            die("Error: Esta caja ya se encuentra abierta.");
        }

        $now = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO cierres_caja (idCaja, idUsuarioApertura, fechaApertura, saldoInicial, estado, creadoPor, creadoDate) 
                               VALUES (?, ?, ?, ?, 'ABIERTA', ?, ?)");
        $stmt->execute([
            $idCaja,
            $idUsuario,
            $now,
            $saldoInicial,
            $idUsuario,
            $now
        ]);

        header("Location: estado.php?success=apertura");
    } catch (PDOException $e) {
        die("Error al abrir caja: " . $e->getMessage());
    }
} else {
    header("Location: aperturas.php");
}
