<?php
/**
 * Logifact API Client - Helper for SRI and Electronic Invoicing
 */

class LogifactAPI
{
    private static $login_url = "https://logifact.fwh.is/?login=1";
    private static $api_url = "http://logifact.fwh.is/";
    private static $sri_url = "https://logifact.fwh.is/consulta_sri.php?clave=";

    private static $username = "admin";
    private static $password = "admin123";

    /**
     * Performs login and returns the token
     */
    public static function login()
    {
        // Crear archivo temporal para cookies
        $cookieFile = sys_get_temp_dir() . '/logifact_cookies.txt';

        // Primera petición: obtener la cookie de protección anti-bot
        $ch = curl_init("https://logifact.fwh.is/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        // Parsear la cookie del JavaScript si existe
        if (preg_match('/document\.cookie="([^"]+)"/', $response, $matches)) {
            $cookieValue = $matches[1];
            error_log("Cookie encontrada: " . $cookieValue);

            // Agregar la cookie manualmente al archivo
            file_put_contents($cookieFile, "logifact.fwh.is\tFALSE\t/\tTRUE\t0\t" . str_replace("; ", "\t", $cookieValue) . "\n", FILE_APPEND);
        }

        // Esperar un poco (simular navegador)
        sleep(2);

        // Segunda petición: intentar el login con las cookies
        $ch = curl_init(self::$login_url);
        $payload = json_encode([
            "username" => self::$username,
            "password" => self::$password
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Referer: https://logifact.fwh.is/'
        ]);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Error en login: " . $error);
            return null;
        }

        if ($http_code !== 200) {
            error_log("Login HTTP error: " . $http_code . " Response: " . substr($response, 0, 200));
            return null;
        }

        error_log("Respuesta de login: " . substr($response, 0, 500));
        $data = json_decode($response, true);

        if ($data && isset($data['success']) && $data['success'] && isset($data['token'])) {
            return $data['token'];
        }

        error_log("Login falló - Respuesta no es JSON válido o falta token");
        return null;
    }

    /**
     * Sends an invoice JSON to the main API
     */
    public static function sendInvoice($json_data, $token)
    {
        $cookieFile = sys_get_temp_dir() . '/logifact_cookies.txt';
        $ch = curl_init(self::$api_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Error enviando factura: " . $error);
            return ['error' => $error];
        }

        return json_decode($response, true);
    }

    /**
     * Queries SRI database via Logifact endpoint
     * Intenta primero sin autenticación, si falla intenta con token
     */
    public static function consultaSRI($clave)
    {
        // Intentar primero sin autenticación (endpoint público)
        $url = self::$sri_url . trim($clave);
        error_log("Intentando consulta SRI sin autenticación: " . $url);

        $cookieFile = sys_get_temp_dir() . '/logifact_cookies.txt';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Accept-Language: es-ES,es;q=0.9',
            'Referer: http://logifact.fwh.is/'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("CURL Error (sin auth): " . $error);
            return ['error' => 'Error de conexión: ' . $error, 'estado' => 'ERROR'];
        }

        error_log("HTTP Code: $http_code - Response: " . substr($response, 0, 200));

        // Si retorna HTML con JavaScript (protección anti-bot), intentar con token
        if (stripos($response, '<script') !== false && stripos($response, 'slowAES') !== false) {
            error_log("Detectada protección anti-bot en consulta SRI, reintentando con token...");
            return self::consultaSRIConToken($clave);
        }

        if ($http_code !== 200) {
            error_log("HTTP Code Error: " . $http_code);

            // Si es 401 o 403, podría necesitar autenticación
            if ($http_code == 401 || $http_code == 403) {
                error_log("Requiere autenticación, intentando con token...");
                return self::consultaSRIConToken($clave);
            }

            return ['error' => "HTTP $http_code", 'estado' => 'ERROR'];
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("No es JSON válido: " . json_last_error_msg());
            return ['error' => 'Respuesta inválida del servidor', 'estado' => 'ERROR'];
        }

        // Si tiene error de autenticación, intentar con token
        if (isset($decoded['error']) && stripos($decoded['error'], 'token') !== false) {
            error_log("Error de token, intentando autenticación...");
            return self::consultaSRIConToken($clave);
        }

        error_log("Consulta SRI exitosa (sin auth)");
        return $decoded;
    }

    private static function consultaSRIConToken($clave)
    {
        error_log("Intentando hacer login...");
        $token = self::login();

        if (!$token) {
            error_log("No se pudo obtener token");
            return ['error' => 'No se pudo autenticar. Verifica las credenciales en logifact_api.php', 'estado' => 'ERROR'];
        }

        error_log("Token obtenido: " . substr($token, 0, 20) . "...");

        $cookieFile = sys_get_temp_dir() . '/logifact_cookies.txt';
        $url = self::$sri_url . trim($clave);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("CURL Error (con token): " . $error);
            return ['error' => $error, 'estado' => 'ERROR'];
        }

        if ($http_code !== 200) {
            error_log("HTTP Code Error (con token): " . $http_code);
            return ['error' => "HTTP $http_code", 'estado' => 'ERROR'];
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Error (con token): " . json_last_error_msg());
            return ['error' => 'Respuesta inválida', 'estado' => 'ERROR'];
        }

        error_log("Consulta SRI exitosa (con token)");
        return $decoded;
    }

    /**
     * Tries to find the correct Access Key for a given invoice number
     * by querying the Logifact system for authorized records.
     */
    public static function findKeyBySequence($full_number, $token)
    {
        $cookieFile = sys_get_temp_dir() . '/logifact_cookies.txt';

        // Extraer secuencial para búsqueda flexible (por si el API usa 8 o 9 dígitos)
        $parts = explode('-', $full_number);
        $sec = end($parts);

        // Intentamos buscar por numero completo y por secuencial solo
        $attempts = [trim($full_number), trim($sec), ltrim($sec, '0')];

        foreach ($attempts as $term) {
            $url = self::$api_url . "?numero=" . urlencode($term);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            if (!$data)
                continue;

            // Si es una lista de resultados (como devuelve tu API en el hosting)
            if (is_array($data)) {
                // Buscamos en toda la lista el que tenga autorización
                foreach ($data as $item) {
                    $authNum = $item['numero_autorizacion'] ?? $item['numeroAutorizacion'] ?? $item['claveAcceso'] ?? $item['autorizacion'] ?? null;
                    $hasAuth = !empty($authNum) && strlen($authNum) > 10;

                    if ($hasAuth)
                        return $authNum;
                }

                // Si no hay ninguno con autorización, devolvemos el primer ID/Clave que encontremos
                if (isset($data[0])) {
                    $item = $data[0];
                    return $item['claveAcceso'] ?? $item['autorizacion'] ?? $item['numero_autorizacion'] ?? null;
                }
            }

            // Si es un objeto único
            $auth = $data['claveAcceso'] ?? $data['autorizacion'] ?? $data['numeroAutorizacion'] ?? ($data['numero_autorizacion'] ?? ($data['data']['claveAcceso'] ?? null));
            if ($auth)
                return $auth;
        }

        return null;
    }
}
