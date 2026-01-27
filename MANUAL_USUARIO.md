# 📔 Manual de Usuario - SYSTEM-POS
## Sistema de Gestión Comercial y Facturación Electrónica

¡Bienvenido al **SYSTEM-POS**! Este manual ha sido diseñado para ayudarte a navegar y utilizar todas las funciones de tu sistema de punto de venta, inventario y contabilidad de manera eficiente.

---

## 📑 Tabla de Contenidos
1. [Introducción](#1-introducción)
2. [Acceso al Sistema](#2-acceso-al-sistema)
3. [Módulo de Inventario y Productos](#3-módulo-de-inventario-y-productos)
4. [Módulo de Ventas y Facturación](#4-módulo-de-ventas-y-facturación)
5. [Módulo de Caja (Gestión de Efectivo)](#5-módulo-de-caja)
6. [Módulo de Clientes](#6-módulo-de-clientes)
7. [Módulo de Contabilidad](#7-módulo-de-contabilidad)
8. [Configuración y Usuarios](#8-configuración-y-usuarios)
9. [Soporte Técnico](#9-soporte-técnico)

---

## 1. Introducción
**SYSTEM-POS** es una solución integral para la gestión de negocios que combina la potencia de un punto de venta (POS) con herramientas avanzadas de inventario, contabilidad y cumplimiento tributario (SRI Ecuador).

**Características principales:**
- Facturación Electrónica automatizada con el SRI.
- Control de inventario en tiempo real (Kardex).
- Gestión multi-ventana en el punto de venta.
- Reportes detallados de ventas y cierres de caja.
- Administración de cuentas por cobrar y pagar.

---

## 2. Acceso al Sistema
Para ingresar al sistema:
1. Abra su navegador web e ingrese la URL del sistema.
2. Ingrese su **Nombre de Usuario** y **Contraseña**.
3. Haga clic en **"Iniciar Sesión"**.

*Nota: Si olvida su contraseña, contacte al administrador del sistema para restablecerla.*

---

## 3. Módulo de Inventario y Productos
Este módulo permite gestionar todo el catálogo de productos y el movimiento de mercancía.

### 3.1 Gestión de Productos
- **Nuevo Producto:** Permite registrar códigos de barras, nombres, categorías, precios de venta (PVP) y niveles de stock mínimo.
- **Categorías:** Organice sus productos para facilitar la búsqueda.
- **Ubicaciones:** Gestione diferentes bodegas o estantes.

### 3.2 Movimientos de Inventario
- **Kardex:** Visualice el historial detallado de entradas y salidas de cada producto.
- **Ajustes:** Realice correcciones manuales de stock por rotura, pérdida o inventario físico.
- **Transferencias:** Mueva mercancía entre diferentes ubicaciones o bodegas.
- **Compras:** Registre las facturas de sus proveedores para aumentar el stock de forma automática.

---

## 4. Módulo de Ventas y Facturación
Es el corazón del sistema, diseñado para ser rápido y eficiente.

### 4.1 Punto de Venta (POS)
- **Multi-ventana:** Puede abrir varias pestañas de venta simultáneamente (ideal para cuando un cliente olvida algo).
- **Búsqueda Inteligente:** Busque productos por código de barras o nombre.
- **Métodos de Pago:** Acepta efectivo, tarjetas de crédito/débito, transferencias y créditos personales.

### 4.2 Facturación Electrónica (SRI)
- **Emisión:** Al finalizar una venta, el sistema genera automáticamente el XML y lo envía al SRI.
- **Estado SRI:** En la sección de "Facturas Electrónicas" puede ver si una factura está "AUTORIZADA", "PENDIENTE" o "RECHAZADA".
- **Anulaciones:** Permite anular facturas y emitir Notas de Crédito si el cliente devuelve la mercancía.

---

## 5. Módulo de Caja
Control estricto del flujo de efectivo en el local.

- **Apertura de Caja:** Ingrese el monto inicial con el que comienza el turno.
- **Movimientos:** Registre entradas y salidas de efectivo que no sean ventas (ej. pago de servicios).
- **Cierre de Caja:** Al finalizar el turno, el sistema genera un reporte de ventas totales vs. efectivo real, detectando posibles sobrantes o faltantes.

---

## 6. Módulo de Clientes
Gestione la información de sus compradores frecuentes.
- Registre RUC/Cédula, nombre, teléfono, dirección y correo electrónico.
- El sistema permite buscar clientes por identificación para facturación rápida.
- Gestión de cupos de crédito para clientes de confianza.

---

## 7. Módulo de Contabilidad
Control financiero básico para mantener el negocio saludable.

- **Cuentas por Cobrar:** Listado de clientes que deben dinero por ventas a crédito.
- **Cuentas por Pagar:** Registro de deudas con proveedores por compras de inventario.
- **Reportes:** Balances básicos y resúmenes de egresos/ingresos.

---

## 8. Configuración y Usuarios
Solo accesible para usuarios con rol de **Administrador**.

### 8.1 Usuarios y Permisos
- **Creación de Usuarios:** Asigne nombres de usuario y contraseñas.
- **Roles:** Defina qué puede hacer cada empleado (vendedor, cajero, administrador).
- **Auditoría:** El sistema registra quién hizo qué y a qué hora (logs).

### 8.2 Configuración del Sistema
- **Datos de la Empresa:** Nombre, RUC, dirección, logo y firma electrónica para el SRI.
- **Impuestos:** Configuración del IVA (15% u otros vigentes).
- **Secuenciales:** Control de los números de factura.

---

### 8.3 Mantenimiento del Sistema
Para asegurar el correcto funcionamiento, el administrador dispone de herramientas de diagnóstico:
- **Reparación de Base de Datos:** En caso de errores inesperados o lentitud, el archivo `REPARAR_DB.php` puede ayudar a sincronizar estructuras.
- **Vínculos de Ventas:** Si hay ventas que no aparecen correctamente vinculadas, la herramienta de vinculación temporal procesa registros pendientes.

---

## 9. Soporte Técnico
Si encuentra algún problema o necesita asistencia:
1. Verifique su conexión a internet (necesaria para el SRI).
2. Asegúrese de que su firma electrónica no esté caducada.
3. Contacte al equipo de soporte a través de los canales oficiales.

---

## 🚀 Procedimientos Comunes (Paso a Paso)

### ¿Cómo realizar una venta POS?
1. Diríjase a **Ventas > POS**.
2. Seleccione la pestaña de venta (usualmente "Venta 1").
3. Escanee el producto o búsquelo por nombre en el buscador superior.
4. Ajuste la cantidad si es necesario.
5. Haga clic en el botón verde **"Pagar"** (o presione F12).
6. Seleccione el método de pago e ingrese el monto recibido.
7. Haga clic en **"Finalizar Venta"**. El sistema imprimirá el ticket y enviará la factura al SRI.

### ¿Cómo ingresar mercadería nueva?
1. Vaya a **Inventario > Compras**.
2. Seleccione **"Nueva Compra"**.
3. Elija el proveedor.
4. Busque los productos que está recibiendo e ingrese las cantidades y el costo de compra.
5. Guarde la compra. El stock se actualizará automáticamente en el sistema.

### ¿Cómo hacer el Cierre de Caja?
1. Al final del día, vaya a **Caja > Cierre de Caja**.
2. El sistema le mostrará el resumen de ventas del día por método de pago.
3. Cuente el dinero físico en su gaveta.
4. Ingrese el total contado en el campo correspondiente.
5. Haga clic en **"Cerrar Turno"**. Se generará un comprobante de cierre.

### ¿Qué hacer si una factura es RECHAZADA por el SRI?
1. Vaya a **Ventas > Facturas Electrónicas**.
2. Busque las facturas con estado **"RECHAZADO"**.
3. Haga clic en el icono de información para ver el error (ej: "RUC inválido", "Error en secuencia").
4. Corrija el dato necesario (ej: edite el cliente si el RUC estaba mal).
5. Seleccione la factura y haga clic en **"Re-enviar al SRI"**.

---
*© 2026 SYSTEM-POS - Gestión Inteligente para tu Negocio*
