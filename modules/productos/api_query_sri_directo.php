<?php
/**
 * Consulta directa al SRI de Ecuador (alternativa)
 * Este endpoint consulta directamente a los servidores oficiales del SRI
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$clave = $_GET['clave'] ?? '';

if (strlen($clave) !== 49) {
    echo json_encode(['success' => false, 'error' => 'La clave de acceso debe tener 49 dígitos']);
    exit;
}

// URLs oficiales del SRI para consulta de comprobantes
$sri_urls = [
    'https://celphone.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
    'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl'
];

// Intentar con servicio alternativo (API pública si existe)
$url = "https://srienlinea.sri.gob.ec/servicios-internet/consultas/autorizacion/consultaClaveAcceso?claveAcceso=" . $clave;

error_log("Consultando SRI directo con clave: " . $clave);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    error_log("Error CURL consultando SRI directo: " . $error);
    
    // Fallback: Intentar con logifact
    require_once '../../includes/logifact_api.php';
    $response_logifact = LogifactAPI::consultaSRI($clave);
    
    if ($response_logifact) {
        echo json_encode(['success' => true, 'data' => $response_logifact, 'source' => 'logifact']);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'No se pudo conectar ni al SRI oficial ni al servidor de respaldo. Verifica tu conexión a internet.',
            'details' => $error
        ]);
    }
    exit;
}

if ($http_code !== 200) {
    error_log("HTTP Code Error: " . $http_code);
    echo json_encode([
        'success' => false,
        'error' => "Error HTTP $http_code al consultar el SRI",
        'http_code' => $http_code
    ]);
    exit;
}

// Intentar parsear como JSON
$json_data = json_decode($response, true);
if ($json_data) {
    echo json_encode(['success' => true, 'data' => $json_data, 'source' => 'sri_oficial']);
} else {
    // Si no es JSON, puede ser XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($response);
    
    if ($xml) {
        $json_xml = json_encode($xml);
        $array_xml = json_decode($json_xml, true);
        echo json_encode(['success' => true, 'data' => $array_xml, 'source' => 'sri_oficial_xml']);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Respuesta del SRI no pudo ser procesada',
            'response_preview' => substr($response, 0, 200)
        ]);
    }
}
