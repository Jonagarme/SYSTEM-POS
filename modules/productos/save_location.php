<?php
/**
 * Save product location assignment
 */
session_start();
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'] ?? null;
    $percha_id = $_POST['percha_id'] ?? null;
    $fila = $_POST['fila'] ?? null;
    $columna = $_POST['columna'] ?? null;

    if ($producto_id && $percha_id && $fila && $columna) {
        try {
            // Update product with location coordinates
            $stmt = $pdo->prepare("UPDATE productos SET percha_id = ?, percha_fila = ?, percha_columna = ? WHERE id = ?");
            $stmt->execute([$percha_id, $fila, $columna, $producto_id]);

            // Redirect back to locations view with success message
            header("Location: ubicaciones.php?msg=location_saved");
            exit;
        } catch (PDOException $e) {
            header("Location: percha_mapa.php?id=$percha_id&product_id=$producto_id&error=db_error");
            exit;
        }
    }
}

header("Location: ubicaciones.php");
exit;
