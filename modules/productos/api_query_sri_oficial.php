<?php
header('Content-Type: application/json');

$clave = $_GET['clave'] ?? '';
$clave = trim($clave);

if (strlen($clave) !== 49 || !ctype_digit($clave)) {
    echo json_encode(['success' => false, 'error' => 'La clave de acceso debe tener 49 dígitos numéricos']);
    exit;
}

if (!class_exists('SoapClient')) {
    echo json_encode([
        'success' => false,
        'error' => 'La extensión SOAP de PHP no está habilitada (SoapClient).',
        'hint' => 'En XAMPP/WAMP habilita extension=soap en php.ini y reinicia Apache.'
    ]);
    exit;
}

// Endpoint oficial SRI (producción)
$wsdl = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';

try {
    // Algunos entornos Windows/XAMPP fallan validando CA; por eso stream_context con verify_peer=false.
    // Si tu entorno tiene CA correctamente, puedes cambiar a true.
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ]);

    $client = new SoapClient($wsdl, [
        'trace' => false,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'connection_timeout' => 15,
        'stream_context' => $context,
    ]);

    // Según WSDL: autorizacionComprobante(claveAccesoComprobante)
    $result = $client->__soapCall('autorizacionComprobante', [
        ['claveAccesoComprobante' => $clave]
    ]);

    // Normalizar a array para navegar sin pelear con objetos SOAP
    $arr = json_decode(json_encode($result), true);

    // La respuesta suele venir como RespuestaAutorizacionComprobante
    $resp = $arr['RespuestaAutorizacionComprobante'] ?? $arr;

    $autorizacion = null;
    if (isset($resp['autorizaciones']['autorizacion'])) {
        $aut = $resp['autorizaciones']['autorizacion'];
        // A veces es lista, a veces es objeto
        $autorizacion = isset($aut[0]) ? $aut[0] : $aut;
    }

    if (!$autorizacion) {
        echo json_encode([
            'success' => false,
            'error' => 'No se encontraron autorizaciones para esta clave en el SRI.',
            'debug' => $resp
        ]);
        exit;
    }

    $estado = $autorizacion['estado'] ?? null;
    $comprobante = $autorizacion['comprobante'] ?? null;

    if (is_string($comprobante) && strpos($comprobante, '&lt;') !== false) {
        $comprobante = html_entity_decode($comprobante, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'estado' => $estado,
            'comprobante' => $comprobante,
            // datos extra por si quieres mostrar mensajes
            'numeroAutorizacion' => $autorizacion['numeroAutorizacion'] ?? null,
            'fechaAutorizacion' => $autorizacion['fechaAutorizacion'] ?? null,
            'ambiente' => $autorizacion['ambiente'] ?? null,
            'mensajes' => $autorizacion['mensajes'] ?? null,
        ]
    ]);
} catch (Throwable $e) {
    // SoapFault y otros
    echo json_encode([
        'success' => false,
        'error' => 'Error consultando SRI oficial: ' . $e->getMessage(),
    ]);
}
