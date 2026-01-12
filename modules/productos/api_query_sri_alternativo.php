<?php
/**
 * Consulta SRI - Método alternativo sin autenticación
 * Intenta consultar directamente el endpoint sin pasar por el sistema de login
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$clave = $_GET['clave'] ?? '';

if (strlen($clave) !== 49) {
    echo json_encode(['success' => false, 'error' => 'La clave de acceso debe tener 49 dígitos']);
    exit;
}

error_log("=== Consulta SRI Alternativa ===");
error_log("Clave: " . $clave);

// Intentar diferentes endpoints posibles
$endpoints = [
    "https://logifact.fwh.is/consulta_sri.php?clave=" . $clave . "&public=1",
    "https://logifact.fwh.is/api/sri/consulta?clave=" . $clave,
    "https://logifact.fwh.is/public/sri?clave=" . $clave,
];

foreach ($endpoints as $url) {
    error_log("Probando endpoint: " . $url);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$error && $http_code == 200) {
        $data = json_decode($response, true);
        if ($data && !isset($data['error'])) {
            error_log("✓ Endpoint funcionó: " . $url);
            echo json_encode(['success' => true, 'data' => $data, 'source' => $url]);
            exit;
        }
    }
    
    error_log("✗ Endpoint falló - HTTP: $http_code - Error: $error");
}

// Si ninguno funcionó, dar mensaje de error
error_log("Todos los endpoints fallaron");
echo json_encode([
    'success' => false,
    'error' => 'No se pudo acceder al servidor SRI. El servicio puede requerir configuración adicional o no estar disponible públicamente.',
    'info' => 'El servidor logifact.fwh.is tiene protección anti-bot que requiere autenticación especial.'
]);
