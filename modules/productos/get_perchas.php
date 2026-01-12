<?php
/**
 * AJAX Handler to fetch perchas by section
 */
require_once '../../includes/db.php';

$seccion_id = $_GET['seccion_id'] ?? null;

if ($seccion_id) {
    $stmt = $pdo->prepare("SELECT id, nombre, filas, columnas FROM perchas WHERE seccion_id = ? ORDER BY nombre");
    $stmt->execute([$seccion_id]);
    $perchas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($perchas);
} else {
    echo json_encode([]);
}
