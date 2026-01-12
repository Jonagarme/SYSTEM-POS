// Solución JavaScript - Consulta SRI desde el navegador
async function consultarSRIDesdeNavegador(clave) {
    try {
        // Intento 1: Consulta directa
        const url = `https://logifact.fwh.is/consulta_sri.php?clave=${clave}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'include' // Incluir cookies
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        return { success: true, data: data };
        
    } catch (error) {
        console.error('Error consultando SRI:', error);
        return { 
            success: false, 
            error: error.message,
            info: 'No se pudo consultar desde el navegador. El servidor puede requerir autenticación especial.'
        };
    }
}

// Uso:
// const resultado = await consultarSRIDesdeNavegador('301220250...');
