<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/logifact_api.php';

$clave = $_GET['clave'] ?? '';

if (strlen($clave) !== 49) {
    echo json_encode(['success' => false, 'error' => 'La clave de acceso debe tener 49 dígitos']);
    exit;
}

// Log para debugging
error_log("=== Iniciando consulta SRI ===");
error_log("Clave: " . $clave);

$response = LogifactAPI::consultaSRI($clave);

error_log("Respuesta recibida: " . json_encode($response));

// Verificar si hubo un error en la respuesta
if (!$response) {
    error_log("Error: Respuesta nula del servidor");
    echo json_encode([
        'success' => false, 
        'error' => 'No se recibió respuesta del servidor. Verifica tu conexión.'
    ]);
    exit;
}

// Si la respuesta tiene un campo 'error', es un error
if (isset($response['error'])) {
    error_log("Error en respuesta: " . $response['error']);
    echo json_encode([
        'success' => false,
        'error' => $response['error'],
        'estado' => $response['estado'] ?? 'ERROR'
    ]);
    exit;
}

// Si tiene el campo 'estado', es una respuesta válida
if (isset($response['estado'])) {
    error_log("Consulta exitosa - Estado: " . $response['estado']);
    echo json_encode(['success' => true, 'data' => $response]);
} else {
    // Respuesta inesperada
    error_log("Respuesta inesperada del servidor");
    echo json_encode([
        'success' => false,
        'error' => 'Respuesta inesperada del servidor.',
        'debug' => $response
    ]);
}

error_log("=== Fin consulta SRI ===");
