<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

try {
    $stmt = $pdo->query("SELECT p.*, e.codigo as cod_est, e.nombre_comercial as est_nombre 
                         FROM puntos_emision p 
                         JOIN establecimientos e ON p.id_establecimiento = e.id 
                         WHERE p.activo = 1 
                         ORDER BY e.codigo, p.codigo");
    $puntos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'puntos' => $puntos
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
