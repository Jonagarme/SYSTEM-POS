<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if (empty($q)) {
        // Si no hay búsqueda, mostrar los 15 productos más recientes o con stock
        $sql = "SELECT id, nombre, codigoPrincipal as barcode, stock, precioVenta as price 
                FROM productos 
                WHERE anulado = 0 
                ORDER BY creadoDate DESC 
                LIMIT 15";
        $stmt = $pdo->query($sql);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Búsqueda por palabras
        $words = explode(' ', $q);
        $whereClauses = [];
        $params = [];

        foreach ($words as $idx => $word) {
            if (empty($word))
                continue;
            // Usamos nombres de parámetros únicos para evitar el error "Invalid parameter number"
            $whereClauses[] = "(nombre LIKE :n$idx OR codigoPrincipal LIKE :c$idx)";
            $params[":n$idx"] = "%$word%";
            $params[":c$idx"] = "%$word%";
        }

        $whereSql = implode(' AND ', $whereClauses);
        $sql = "SELECT id, nombre, codigoPrincipal as barcode, stock, precioVenta as price 
                FROM productos 
                WHERE ($whereSql) AND anulado = 0 
                LIMIT 25";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($productos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
