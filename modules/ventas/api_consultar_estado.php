<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/logifact_api.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Falta el ID de la factura']);
    exit;
}

try {
    // 1. Obtener datos de la factura
    $stmt = $pdo->prepare("SELECT f.*, e.ruc, e.ambiente FROM facturas_venta f JOIN empresas e ON 1=1 WHERE f.id = ?");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$f) {
        throw new Exception("Factura no encontrada");
    }

    // Si ya está autorizada, no consultar más
    if ($f['estadoFactura'] === 'AUTORIZADA') {
        echo json_encode([
            'success' => true,
            'estado' => 'AUTORIZADA',
            'numeroAutorizacion' => $f['numeroAutorizacion']
        ]);
        exit;
    }

    // 2. Generar la clave de acceso si no la tenemos (o usar la que está en numeroAutorizacion si es el caso)
    // El formato de la clave de acceso es: 
    // fecha (8) + tipoDoc (2/01) + RUC (13) + ambiente (1) + serie (6/est+punto) + secuencial (9) + código (8) + tipoEmi (1) + digitoVerificador (1)

    // Lo más seguro es que Logifact ya generó la clave o podemos usar el número de factura para consultar
    // Logifact suele facilitar la consulta por clave de acceso.

    // Vamos a intentar obtener la clave de acceso que guardó Logifact o reconstruirla.
    // Pero si el proceso inicial falló, quizás aún no tenemos la clave guardada localmente.

    // Para simplificar, si numeroAutorizacion tiene 49 dígitos, es la clave de acceso.
    $claveAcceso = $f['numeroAutorizacion'];

    // Si no tenemos clave, no podemos consultar sin recrearla.
    // Como LogifactAPI ya tiene lógica para enviar, asumiremos que ya se intentó enviar y tenemos la clave o al menos el número de factura.

    // Si numeroAutorizacion está vacío, intentamos reconstruir la clave de acceso (esquema SRI)
    if (empty($claveAcceso) || strlen($claveAcceso) < 40) {
        // Reconstrucción básica (muy técnica, pero necesaria si no se guardó)
        $fechaAcceso = date('dmY', strtotime($f['fechaEmision']));
        $ruc = $f['ruc'];
        $ambiente = $f['ambiente'];
        $serie = str_replace('-', '', substr($f['numeroFactura'], 0, 7)); // 001001
        $secuencial = str_pad(substr($f['numeroFactura'], 8), 9, '0', STR_PAD_LEFT);
        $codigoNumerico = "12345678"; // Código por defecto usado en api_guardar_venta si no se generó uno aleatorio
        $tipoEmision = "1";

        $parcialClave = $fechaAcceso . "01" . $ruc . $ambiente . $serie . $secuencial . $codigoNumerico . $tipoEmision;

        // Módulo 11 para el dígito verificador
        function calcularDigitoM11($cadena)
        {
            $reversed = strrev($cadena);
            $sum = 0;
            $factor = 2;
            for ($i = 0; $i < strlen($reversed); $i++) {
                $sum += intval($reversed[$i]) * $factor;
                $factor = ($factor == 7) ? 2 : $factor + 1;
            }
            $digit = 11 - ($sum % 11);
            if ($digit == 11)
                return 0;
            if ($digit == 10)
                return 1;
            return $digit;
        }

        $dv = calcularDigitoM11($parcialClave);
        $claveAcceso = $parcialClave . $dv;
    }

    // 3. Consultar a Logifact
    $res = LogifactAPI::consultaSRI($claveAcceso);

    if (isset($res['estado'])) {
        $estadoSRI = strtoupper($res['estado']);
        if ($estadoSRI === 'AUTORIZADO' || $estadoSRI === 'AUTORIZADA') {
            $numAuth = $res['numeroAutorizacion'] ?? $res['autorizacion'] ?? $claveAcceso;

            $pdo->prepare("UPDATE facturas_venta SET estadoFactura = 'AUTORIZADA', numeroAutorizacion = ?, fechaAutorizacion = NOW() WHERE id = ?")
                ->execute([$numAuth, $id]);

            echo json_encode([
                'success' => true,
                'estado' => 'AUTORIZADA',
                'numeroAutorizacion' => $numAuth,
                'message' => 'Autorización obtenida con éxito'
            ]);
            exit;
        }
    }

    echo json_encode([
        'success' => true,
        'estado' => $f['estadoFactura'],
        'sri_status' => $res['estado'] ?? 'NO ENCONTRADO',
        'message' => 'Aún no autorizado o en proceso'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
