<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

$producto_id = isset($_GET['producto_id']) ? (int) $_GET['producto_id'] : 0;
$ubicacion_id = isset($_GET['ubicacion_id']) ? (int) $_GET['ubicacion_id'] : 0;

if (!$producto_id || !$ubicacion_id) {
    echo json_encode([]);
    exit;
}

try {
    // Fetch lotes from inventario_loteproducto
    $sql = "SELECT id, numero_lote, fecha_caducidad, cantidad_disponible as stock
            FROM inventario_loteproducto 
            WHERE producto_id = :prod_id 
              AND ubicacion_id = :ubic_id 
              AND activo = 1 
              AND cantidad_disponible > 0
            ORDER BY fecha_caducidad ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':prod_id' => $producto_id, ':ubic_id' => $ubicacion_id]);
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $today = new DateTime();
    $formatted_lotes = array_map(function ($l) use ($today) {
        $expiry = new DateTime($l['fecha_caducidad']);
        $diff = $today->diff($expiry);
        $days = (int) $diff->format("%r%a");

        $status = 'Vigente';
        $status_class = 'badge-success';
        if ($days <= 0) {
            $status = 'Vencido';
            $status_class = 'badge-danger';
        } elseif ($days <= 30) {
            $status = 'Próximo';
            $status_class = 'badge-warning';
        }

        return [
            'id' => $l['id'],
            'lote' => $l['numero_lote'],
            'caducidad' => $l['fecha_caducidad'],
            'caducidad_fmt' => $expiry->format('d/m/Y'),
            'dias' => $days,
            'stock' => (float) $l['stock'],
            'estado' => $status,
            'estado_class' => $status_class
        ];
    }, $lotes);

    echo json_encode($formatted_lotes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>