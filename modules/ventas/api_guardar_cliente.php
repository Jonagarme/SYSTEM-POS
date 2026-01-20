<?php
require_once '../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            throw new Exception("Datos no recibidos correctamente.");
        }

        // Validación básica
        if (empty($data['nombres']) || empty($data['cedula_ruc'])) {
            throw new Exception("Nombres e Identificación son obligatorios.");
        }

        $stmt = $pdo->prepare("INSERT INTO clientes (nombres, apellidos, cedula_ruc, direccion, celular, email, tipo_cliente, creadoPor, creadoDate) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");

        $stmt->execute([
            $data['nombres'],
            $data['apellidos'] ?? '',
            $data['cedula_ruc'],
            $data['direccion'] ?? '',
            $data['celular'] ?? '',
            $data['email'] ?? '',
            $data['tipo_cliente'] ?? 'Natural'
        ]);

        $newId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'id' => $newId,
            'message' => 'Cliente guardado correctamente.'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
