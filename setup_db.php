<?php
/**
 * Initial database setup script
 */
// Temporary connection without dbname to create the DB first
$host = 'localhost';
$user = 'root';
$pass = '0801';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (\PDOException $e) {
    die("Error inicial: " . $e->getMessage());
}

$sql = "
CREATE DATABASE IF NOT EXISTS pos_system;
USE pos_system;

-- Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL,
password VARCHAR(255) NOT NULL,
rol ENUM('Administrador', 'Vendedor', 'Supervisor') DEFAULT 'Vendedor',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorías
CREATE TABLE IF NOT EXISTS categorias (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
descripcion TEXT
);

-- Secciones (Bodega, Vitrina, etc.)
CREATE TABLE IF NOT EXISTS secciones (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
descripcion TEXT,
color VARCHAR(20) DEFAULT '#007bff',
orden INT DEFAULT 0,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Perchas vinculadas a secciones
CREATE TABLE IF NOT EXISTS perchas (
id INT AUTO_INCREMENT PRIMARY KEY,
seccion_id INT NOT NULL,
nombre VARCHAR(100) NOT NULL,
descripcion TEXT,
filas INT DEFAULT 1,
columnas INT DEFAULT 1,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (seccion_id) REFERENCES secciones(id) ON DELETE CASCADE
);

-- Productos
CREATE TABLE IF NOT EXISTS productos (
id INT AUTO_INCREMENT PRIMARY KEY,
codigo VARCHAR(50) UNIQUE NOT NULL,
codigo_auxiliar VARCHAR(50),
nombre VARCHAR(255) NOT NULL,
registro_sanitario VARCHAR(50),
descripcion TEXT,
observaciones TEXT,
categoria_id INT,
percha_id INT NULL,
percha_fila INT NULL,
percha_columna INT NULL,
marca VARCHAR(100),
laboratorio VARCHAR(100),
precio_compra DECIMAL(10,4) DEFAULT 0,
precio_venta DECIMAL(10,4) NOT NULL,
pvp_unidad DECIMAL(10,4) DEFAULT 0,
stock_actual DECIMAL(10,2) DEFAULT 0,
stock_minimo DECIMAL(10,2) DEFAULT 5,
stock_maximo DECIMAL(10,2) DEFAULT 100,
fecha_caducidad DATE,
es_divisible BOOLEAN DEFAULT FALSE,
es_psicotropico BOOLEAN DEFAULT FALSE,
cadena_frio BOOLEAN DEFAULT FALSE,
estado BOOLEAN DEFAULT TRUE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (categoria_id) REFERENCES categorias(id),
FOREIGN KEY (percha_id) REFERENCES perchas(id) ON DELETE SET NULL
);

-- Clientes
CREATE TABLE IF NOT EXISTS clientes (
id INT AUTO_INCREMENT PRIMARY KEY,
documento VARCHAR(20) UNIQUE NOT NULL,
nombre VARCHAR(255) NOT NULL,
telefono VARCHAR(20),
email VARCHAR(100),
direccion TEXT
);

-- Proveedores
CREATE TABLE IF NOT EXISTS proveedores (
id INT AUTO_INCREMENT PRIMARY KEY,
ruc VARCHAR(20) UNIQUE NOT NULL,
nombre VARCHAR(255) NOT NULL,
telefono VARCHAR(20),
email VARCHAR(100)
);

-- Cajas
CREATE TABLE IF NOT EXISTS cajas (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(50) NOT NULL,
estado ENUM('Abierta', 'Cerrada') DEFAULT 'Cerrada',
monto_apertura DECIMAL(10,2) DEFAULT 0
);

-- Ventas
CREATE TABLE IF NOT EXISTS ventas (
id INT AUTO_INCREMENT PRIMARY KEY,
codigo_factura VARCHAR(50) UNIQUE,
cliente_id INT,
usuario_id INT,
caja_id INT,
total DECIMAL(10,2) NOT NULL,
impuesto DECIMAL(10,2) DEFAULT 0,
metodo_pago ENUM('Efectivo', 'Tarjeta', 'Transferencia') DEFAULT 'Efectivo',
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (cliente_id) REFERENCES clientes(id),
FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
FOREIGN KEY (caja_id) REFERENCES cajas(id)
);

-- Venta Detalles
CREATE TABLE IF NOT EXISTS venta_detalles (
id INT AUTO_INCREMENT PRIMARY KEY,
venta_id INT,
producto_id INT,
cantidad INT NOT NULL,
precio_unitario DECIMAL(10,2) NOT NULL,
subtotal DECIMAL(10,2) NOT NULL,
FOREIGN KEY (venta_id) REFERENCES ventas(id),
FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Movimientos Inventario (Kardex)
CREATE TABLE IF NOT EXISTS kardex (
id INT AUTO_INCREMENT PRIMARY KEY,
producto_id INT,
tipo ENUM('Entrada', 'Salida', 'Ajuste') NOT NULL,
cantidad INT NOT NULL,
motivo VARCHAR(255),
fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (producto_id) REFERENCES productos(id)
);
";

// Dividir el SQL por puntos y coma para ejecutar cada comando individualmente
$commands = array_filter(array_map('trim', explode(';', $sql)));

try {
    echo "<h1>Iniciando Configuración de Base de Datos</h1>";
    echo "<ul
    style='font-family: monospace; font-size: 14px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;'>
    ";

    foreach ($commands as $command) {
        if (empty($command))
            continue;

        // Extraer el nombre de la tabla para el log
        if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $command, $matches)) {
            $table = $matches[1];
            echo "<li>⚙️ Creando tabla <b>$table</b>... ";
        } elseif (preg_match('/CREATE DATABASE IF NOT EXISTS (\w+)/i', $command, $matches)) {
            echo "
    <li>🗄️ Creando base de datos <b>{$matches[1]}</b>... ";
        } elseif (preg_match('/USE (\w+)/i', $command, $matches)) {
            echo "
    <li>🔌 Conectando a <b>{$matches[1]}</b>... ";
        } else {
            echo "
    <li>📝 Ejecutando comando... ";
        }

        try {
            $pdo->exec($command);
            echo "<span style='color: #10b981;'>EXITO</span></li>";
        } catch (PDOException $e) {
            echo "<span style='color: #ef4444;'>ERROR: " . $e->getMessage() . "</span></li>";
            // No detenemos el proceso para intentar crear las demás si una falla
        }
    }

    echo "
</ul>";
    echo "<div style='margin-top: 20px; padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px;'>";
    echo "<b>Proceso finalizado.</b> Revisa la lista de arriba para confirmar que no hubo errores críticos.";
    echo "<br><a href='index.php' style='display: inline-block; margin-top: 10px; color: #059669; font-weight: bold;'>Ir
        al Dashboard →</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='padding: 20px; background: #fef2f2; color: #991b1b; border-radius: 8px;'>";
    echo "<b>Error Fatal:</b> " . $e->getMessage();
    echo "</div>";
}