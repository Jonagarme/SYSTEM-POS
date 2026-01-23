<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if (empty($q)) {
        // Mostrar los 15 más recientes
        $sql = "SELECT id, ruc, razonSocial, nombreComercial, telefono 
                FROM proveedores 
                WHERE anulado = 0 
                ORDER BY creadoDate DESC 
                LIMIT 15";
        $stmt = $pdo->query($sql);
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Búsqueda por palabras
        $words = explode(' ', $q);
        $whereClauses = [];
        $params = [];

        foreach ($words as $idx => $word) {
            if (empty($word))
                continue;
            $whereClauses[] = "(ruc LIKE :r$idx OR razonSocial LIKE :rs$idx OR nombreComercial LIKE :nc$idx)";
            $params[":r$idx"] = "%$word%";
            $params[":rs$idx"] = "%$word%";
            $params[":nc$idx"] = "%$word%";
        }

        $whereSql = implode(' AND ', $whereClauses);
        $sql = "SELECT id, ruc, razonSocial, nombreComercial, telefono 
                FROM proveedores 
                WHERE ($whereSql) AND anulado = 0 
                LIMIT 25";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($proveedores);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
