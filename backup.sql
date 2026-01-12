-- --------------------------------------------------------
-- Host:                         136.111.149.225
-- Versión del servidor:         8.0.41-google - (Google)
-- SO del servidor:              Linux
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para SistemaPosDB
CREATE DATABASE IF NOT EXISTS `SistemaPosDB` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `SistemaPosDB`;

-- Volcando estructura para tabla SistemaPosDB.ajustes_inventario
CREATE TABLE IF NOT EXISTS `ajustes_inventario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numeroDocumento` varchar(50) NOT NULL,
  `idTipoAjuste` int unsigned NOT NULL,
  `fecha` datetime NOT NULL,
  `observaciones` text,
  `totalCosto` decimal(12,4) NOT NULL,
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_ajustes_tipo` (`idTipoAjuste`),
  KEY `fk_ajustes_usuario` (`creadoPor`),
  CONSTRAINT `fk_ajustes_tipo` FOREIGN KEY (`idTipoAjuste`) REFERENCES `tipos_ajuste` (`id`),
  CONSTRAINT `fk_ajustes_usuario` FOREIGN KEY (`creadoPor`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ajustes_inventario_detalle
CREATE TABLE IF NOT EXISTS `ajustes_inventario_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idAjuste` bigint unsigned NOT NULL,
  `idProducto` bigint unsigned NOT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `costoUnitario` decimal(12,4) NOT NULL,
  `total` decimal(12,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detalle_ajuste` (`idAjuste`),
  KEY `fk_detalle_ajuste_producto` (`idProducto`),
  CONSTRAINT `fk_detalle_ajuste` FOREIGN KEY (`idAjuste`) REFERENCES `ajustes_inventario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_ajuste_producto` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.aperturascaja
CREATE TABLE IF NOT EXISTS `aperturascaja` (
  `AperturaID` int NOT NULL AUTO_INCREMENT,
  `FechaApertura` datetime NOT NULL,
  `MontoInicial` decimal(18,2) NOT NULL,
  `UsuarioApertura` varchar(50) NOT NULL,
  `Caja` varchar(50) NOT NULL,
  `FechaCierre` datetime DEFAULT NULL,
  `MontoFinal` decimal(18,2) DEFAULT NULL,
  `UsuarioCierre` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`AperturaID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.arqueos_caja
CREATE TABLE IF NOT EXISTS `arqueos_caja` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `idCierreCaja` bigint NOT NULL,
  `billete_100` int DEFAULT '0',
  `billete_50` int DEFAULT '0',
  `billete_20` int DEFAULT '0',
  `billete_10` int DEFAULT '0',
  `billete_5` int DEFAULT '0',
  `moneda_1` int DEFAULT '0',
  `moneda_050` int DEFAULT '0',
  `moneda_025` int DEFAULT '0',
  `moneda_010` int DEFAULT '0',
  `moneda_005` int DEFAULT '0',
  `moneda_001` int DEFAULT '0',
  `total_billetes` decimal(12,4) DEFAULT '0.0000',
  `total_monedas` decimal(12,4) DEFAULT '0.0000',
  `total_general` decimal(12,4) DEFAULT '0.0000',
  `notas_arqueo` text COLLATE utf8mb4_unicode_ci,
  `creadoPor` int DEFAULT NULL,
  `creadoDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_idCierreCaja` (`idCierreCaja`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auditoria
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idUsuario` int NOT NULL,
  `usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `modulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `entidad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idEntidad` bigint DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `host` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_auditoria_fecha` (`fecha`),
  KEY `idx_auditoria_usuario_fecha` (`idUsuario`,`fecha`),
  KEY `idx_auditoria_modulo_accion_fecha` (`modulo`,`accion`,`fecha`),
  KEY `idx_auditoria_entidad` (`entidad`,`idEntidad`)
) ENGINE=InnoDB AUTO_INCREMENT=305 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auth_group
CREATE TABLE IF NOT EXISTS `auth_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auth_group_permissions
CREATE TABLE IF NOT EXISTS `auth_group_permissions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_group_permissions_group_id_permission_id_0cd325b0_uniq` (`group_id`,`permission_id`),
  KEY `auth_group_permissio_permission_id_84c5c92e_fk_auth_perm` (`permission_id`),
  CONSTRAINT `auth_group_permissio_permission_id_84c5c92e_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `auth_group_permissions_group_id_b120cbf9_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auth_permission
CREATE TABLE IF NOT EXISTS `auth_permission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content_type_id` int NOT NULL,
  `codename` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_permission_content_type_id_codename_01ab375a_uniq` (`content_type_id`,`codename`),
  CONSTRAINT `auth_permission_content_type_id_2f476e4b_fk_django_co` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auth_user
CREATE TABLE IF NOT EXISTS `auth_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `password` varchar(128) NOT NULL,
  `last_login` datetime(6) DEFAULT NULL,
  `is_superuser` tinyint(1) NOT NULL,
  `username` varchar(150) NOT NULL,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) NOT NULL,
  `email` varchar(254) NOT NULL,
  `is_staff` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `date_joined` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auth_user_groups
CREATE TABLE IF NOT EXISTS `auth_user_groups` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_user_groups_user_id_group_id_94350c0c_uniq` (`user_id`,`group_id`),
  KEY `auth_user_groups_group_id_97559544_fk_auth_group_id` (`group_id`),
  CONSTRAINT `auth_user_groups_group_id_97559544_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `auth_user_groups_user_id_6a12ed8b_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.auth_user_user_permissions
CREATE TABLE IF NOT EXISTS `auth_user_user_permissions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_user_user_permissions_user_id_permission_id_14a6b632_uniq` (`user_id`,`permission_id`),
  KEY `auth_user_user_permi_permission_id_1fbb5f2c_fk_auth_perm` (`permission_id`),
  CONSTRAINT `auth_user_user_permi_permission_id_1fbb5f2c_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `auth_user_user_permissions_user_id_a95ead1b_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.cajas
CREATE TABLE IF NOT EXISTS `cajas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `activa` tinyint(1) DEFAULT '1',
  `idUbicacion` int DEFAULT NULL COMMENT 'ID de la ubicación/sucursal donde se abrió la caja',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cajas_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.caja_aperturacaja
CREATE TABLE IF NOT EXISTS `caja_aperturacaja` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha_apertura` datetime(6) NOT NULL,
  `monto_inicial` decimal(10,2) NOT NULL,
  `observaciones` longtext NOT NULL,
  `activa` tinyint(1) NOT NULL,
  `usuario_id` int NOT NULL,
  `caja_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `caja_aperturacaja_usuario_id_569c6df0_fk_auth_user_id` (`usuario_id`),
  KEY `caja_aperturacaja_caja_id_279584b9_fk_caja_caja_id` (`caja_id`),
  CONSTRAINT `caja_aperturacaja_caja_id_279584b9_fk_caja_caja_id` FOREIGN KEY (`caja_id`) REFERENCES `caja_caja` (`id`),
  CONSTRAINT `caja_aperturacaja_usuario_id_569c6df0_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.caja_caja
CREATE TABLE IF NOT EXISTS `caja_caja` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` longtext NOT NULL,
  `activa` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.caja_cierrecaja
CREATE TABLE IF NOT EXISTS `caja_cierrecaja` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha_cierre` datetime(6) NOT NULL,
  `total_ventas` decimal(10,2) NOT NULL,
  `total_efectivo` decimal(10,2) NOT NULL,
  `total_tarjeta` decimal(10,2) NOT NULL,
  `total_transferencia` decimal(10,2) NOT NULL,
  `efectivo_contado` decimal(10,2) NOT NULL,
  `diferencia_efectivo` decimal(10,2) NOT NULL,
  `observaciones` longtext NOT NULL,
  `apertura_id` bigint NOT NULL,
  `usuario_cierre_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apertura_id` (`apertura_id`),
  KEY `caja_cierrecaja_usuario_cierre_id_91b4cf06_fk_auth_user_id` (`usuario_cierre_id`),
  CONSTRAINT `caja_cierrecaja_apertura_id_efa1220e_fk_caja_aperturacaja_id` FOREIGN KEY (`apertura_id`) REFERENCES `caja_aperturacaja` (`id`),
  CONSTRAINT `caja_cierrecaja_usuario_cierre_id_91b4cf06_fk_auth_user_id` FOREIGN KEY (`usuario_cierre_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.caja_movimientocaja
CREATE TABLE IF NOT EXISTS `caja_movimientocaja` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) NOT NULL,
  `concepto` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion` longtext NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `apertura_id` bigint NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `caja_movimientocaja_apertura_id_5b1020e0_fk_caja_aperturacaja_id` (`apertura_id`),
  KEY `caja_movimientocaja_usuario_id_788e149a_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `caja_movimientocaja_apertura_id_5b1020e0_fk_caja_aperturacaja_id` FOREIGN KEY (`apertura_id`) REFERENCES `caja_aperturacaja` (`id`),
  CONSTRAINT `caja_movimientocaja_usuario_id_788e149a_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `activa` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.cierres_caja
CREATE TABLE IF NOT EXISTS `cierres_caja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idCaja` int unsigned NOT NULL,
  `idUsuarioApertura` int unsigned NOT NULL,
  `idUsuarioCierre` int unsigned DEFAULT NULL,
  `fechaApertura` datetime NOT NULL,
  `fechaCierre` datetime DEFAULT NULL,
  `saldoInicial` decimal(12,4) NOT NULL,
  `totalIngresosSistema` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `totalEgresosSistema` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `saldoTeoricoSistema` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `totalContadoFisico` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `diferencia` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `estado` enum('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `anuladoPor` int unsigned DEFAULT NULL,
  `anuladoDate` datetime DEFAULT NULL,
  `idUbicacion` int DEFAULT NULL COMMENT 'ID de la ubicación/sucursal donde se abrió la caja',
  PRIMARY KEY (`id`),
  KEY `fk_cierre_caja` (`idCaja`),
  KEY `fk_cierre_usuario_apertura` (`idUsuarioApertura`),
  KEY `fk_cierre_usuario_cierre` (`idUsuarioCierre`),
  CONSTRAINT `fk_cierre_caja` FOREIGN KEY (`idCaja`) REFERENCES `cajas` (`id`),
  CONSTRAINT `fk_cierre_usuario_apertura` FOREIGN KEY (`idUsuarioApertura`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_cierre_usuario_cierre` FOREIGN KEY (`idUsuarioCierre`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.clases_producto
CREATE TABLE IF NOT EXISTS `clases_producto` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tipo_identificacion` enum('CEDULA','RUC','PASAPORTE') NOT NULL,
  `cedula_ruc` varchar(20) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `razonSocial` varchar(200) DEFAULT NULL,
  `direccion` text,
  `telefono` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `tipo_cliente` varchar(50) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `editadoPor` int unsigned DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clientes_identificacion` (`cedula_ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=356 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.clientes_cliente
CREATE TABLE IF NOT EXISTS `clientes_cliente` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `tipo_cliente` varchar(20) NOT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `nombre_comercial` varchar(200) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(254) NOT NULL,
  `direccion` longtext NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `limite_credito` decimal(10,2) NOT NULL,
  `dias_credito` int unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_modificacion` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `numero_documento` (`numero_documento`),
  CONSTRAINT `clientes_cliente_chk_1` CHECK ((`dias_credito` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.codigos_alternativos
CREATE TABLE IF NOT EXISTS `codigos_alternativos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idProducto` bigint unsigned NOT NULL,
  `codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código del proveedor',
  `nombreProveedor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del producto según el proveedor',
  `idProveedor` int DEFAULT NULL COMMENT 'ID del proveedor de origen',
  `activo` tinyint(1) DEFAULT '1',
  `fechaCreacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_codigo` (`codigo`),
  KEY `idx_producto_activo` (`idProducto`,`activo`),
  CONSTRAINT `fk_codigo_alt_producto` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_asientocontable
CREATE TABLE IF NOT EXISTS `contabilidad_asientocontable` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `total_debe` decimal(15,2) NOT NULL,
  `total_haber` decimal(15,2) NOT NULL,
  `cuadrado` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `contabilidad_asientocontable_usuario_id_ae5edb75_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `contabilidad_asientocontable_usuario_id_ae5edb75_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_categoriagasto
CREATE TABLE IF NOT EXISTS `contabilidad_categoriagasto` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` longtext NOT NULL,
  `presupuesto_mensual` decimal(12,2) NOT NULL,
  `activa` tinyint(1) NOT NULL,
  `cuenta_contable_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `contabilidad_categor_cuenta_contable_id_57b91c94_fk_contabili` (`cuenta_contable_id`),
  CONSTRAINT `contabilidad_categor_cuenta_contable_id_57b91c94_fk_contabili` FOREIGN KEY (`cuenta_contable_id`) REFERENCES `contabilidad_cuentacontable` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_cuentabancaria
CREATE TABLE IF NOT EXISTS `contabilidad_cuentabancaria` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `banco` varchar(100) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `saldo_inicial` decimal(15,2) NOT NULL,
  `fecha_apertura` date NOT NULL,
  `activa` tinyint(1) NOT NULL,
  `cuenta_contable_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_cuenta` (`numero_cuenta`),
  KEY `contabilidad_cuentab_cuenta_contable_id_bcbad9e9_fk_contabili` (`cuenta_contable_id`),
  CONSTRAINT `contabilidad_cuentab_cuenta_contable_id_bcbad9e9_fk_contabili` FOREIGN KEY (`cuenta_contable_id`) REFERENCES `contabilidad_cuentacontable` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_cuentacontable
CREATE TABLE IF NOT EXISTS `contabilidad_cuentacontable` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nivel` int NOT NULL,
  `acepta_movimiento` tinyint(1) NOT NULL,
  `saldo_inicial` decimal(15,2) NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `cuenta_padre_id` bigint DEFAULT NULL,
  `tipo_cuenta_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `contabilidad_cuentac_cuenta_padre_id_fd5f38af_fk_contabili` (`cuenta_padre_id`),
  KEY `contabilidad_cuentac_tipo_cuenta_id_0b533b3b_fk_contabili` (`tipo_cuenta_id`),
  CONSTRAINT `contabilidad_cuentac_cuenta_padre_id_fd5f38af_fk_contabili` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `contabilidad_cuentacontable` (`id`),
  CONSTRAINT `contabilidad_cuentac_tipo_cuenta_id_0b533b3b_fk_contabili` FOREIGN KEY (`tipo_cuenta_id`) REFERENCES `contabilidad_tipocuenta` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_cuentaporcobrar
CREATE TABLE IF NOT EXISTS `contabilidad_cuentaporcobrar` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto_original` decimal(12,2) NOT NULL,
  `monto_pendiente` decimal(12,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observaciones` longtext NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `cliente_id` bigint NOT NULL,
  `factura_relacionada_id` bigint DEFAULT NULL,
  `usuario_creacion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `contabilidad_cuentap_cliente_id_02031fdb_fk_clientes_` (`cliente_id`),
  KEY `contabilidad_cuentap_factura_relacionada__75fd04e5_fk_ventas_ve` (`factura_relacionada_id`),
  KEY `contabilidad_cuentap_usuario_creacion_id_7a266f19_fk_auth_user` (`usuario_creacion_id`),
  CONSTRAINT `contabilidad_cuentap_cliente_id_02031fdb_fk_clientes_` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_cliente` (`id`),
  CONSTRAINT `contabilidad_cuentap_factura_relacionada__75fd04e5_fk_ventas_ve` FOREIGN KEY (`factura_relacionada_id`) REFERENCES `ventas_venta` (`id`),
  CONSTRAINT `contabilidad_cuentap_usuario_creacion_id_7a266f19_fk_auth_user` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_cuentaporpagar
CREATE TABLE IF NOT EXISTS `contabilidad_cuentaporpagar` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `factura_proveedor` varchar(50) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto_original` decimal(12,2) NOT NULL,
  `monto_pendiente` decimal(12,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `categoria_gasto` varchar(100) NOT NULL,
  `observaciones` longtext NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `proveedor_id` bigint NOT NULL,
  `usuario_creacion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `contabilidad_cuentap_proveedor_id_b25694c3_fk_proveedor` (`proveedor_id`),
  KEY `contabilidad_cuentap_usuario_creacion_id_c5708547_fk_auth_user` (`usuario_creacion_id`),
  CONSTRAINT `contabilidad_cuentap_proveedor_id_b25694c3_fk_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores_proveedor` (`id`),
  CONSTRAINT `contabilidad_cuentap_usuario_creacion_id_c5708547_fk_auth_user` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_flujocaja
CREATE TABLE IF NOT EXISTS `contabilidad_flujocaja` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `ingreso_proyectado` decimal(15,2) NOT NULL,
  `egreso_proyectado` decimal(15,2) NOT NULL,
  `ingreso_real` decimal(15,2) NOT NULL,
  `egreso_real` decimal(15,2) NOT NULL,
  `observaciones` longtext NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contabilidad_flujocaja_fecha_concepto_802a0200_uniq` (`fecha`,`concepto`),
  KEY `contabilidad_flujocaja_usuario_id_9f0e9a7a_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `contabilidad_flujocaja_usuario_id_9f0e9a7a_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_gasto
CREATE TABLE IF NOT EXISTS `contabilidad_gasto` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `factura_numero` varchar(50) NOT NULL,
  `observaciones` longtext NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_aprobacion` datetime(6) DEFAULT NULL,
  `categoria_id` bigint NOT NULL,
  `cuenta_pagar_id` bigint DEFAULT NULL,
  `proveedor_id` bigint DEFAULT NULL,
  `usuario_aprueba_id` int DEFAULT NULL,
  `usuario_solicita_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `contabilidad_gasto_categoria_id_69d67461_fk_contabili` (`categoria_id`),
  KEY `contabilidad_gasto_cuenta_pagar_id_d054c672_fk_contabili` (`cuenta_pagar_id`),
  KEY `contabilidad_gasto_proveedor_id_127d72ce_fk_proveedor` (`proveedor_id`),
  KEY `contabilidad_gasto_usuario_aprueba_id_27a0655b_fk_auth_user_id` (`usuario_aprueba_id`),
  KEY `contabilidad_gasto_usuario_solicita_id_a0431865_fk_auth_user_id` (`usuario_solicita_id`),
  CONSTRAINT `contabilidad_gasto_categoria_id_69d67461_fk_contabili` FOREIGN KEY (`categoria_id`) REFERENCES `contabilidad_categoriagasto` (`id`),
  CONSTRAINT `contabilidad_gasto_cuenta_pagar_id_d054c672_fk_contabili` FOREIGN KEY (`cuenta_pagar_id`) REFERENCES `contabilidad_cuentaporpagar` (`id`),
  CONSTRAINT `contabilidad_gasto_proveedor_id_127d72ce_fk_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores_proveedor` (`id`),
  CONSTRAINT `contabilidad_gasto_usuario_aprueba_id_27a0655b_fk_auth_user_id` FOREIGN KEY (`usuario_aprueba_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `contabilidad_gasto_usuario_solicita_id_a0431865_fk_auth_user_id` FOREIGN KEY (`usuario_solicita_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_movimientobancario
CREATE TABLE IF NOT EXISTS `contabilidad_movimientobancario` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `conciliado` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `asiento_contable_id` bigint DEFAULT NULL,
  `cuenta_bancaria_id` bigint NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contabilidad_movimie_asiento_contable_id_a61449e2_fk_contabili` (`asiento_contable_id`),
  KEY `contabilidad_movimie_cuenta_bancaria_id_0df17bcb_fk_contabili` (`cuenta_bancaria_id`),
  KEY `contabilidad_movimie_usuario_id_ff4b04b8_fk_auth_user` (`usuario_id`),
  CONSTRAINT `contabilidad_movimie_asiento_contable_id_a61449e2_fk_contabili` FOREIGN KEY (`asiento_contable_id`) REFERENCES `contabilidad_asientocontable` (`id`),
  CONSTRAINT `contabilidad_movimie_cuenta_bancaria_id_0df17bcb_fk_contabili` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `contabilidad_cuentabancaria` (`id`),
  CONSTRAINT `contabilidad_movimie_usuario_id_ff4b04b8_fk_auth_user` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_movimientocontable
CREATE TABLE IF NOT EXISTS `contabilidad_movimientocontable` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `debe` decimal(15,2) NOT NULL,
  `haber` decimal(15,2) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `asiento_id` bigint NOT NULL,
  `cuenta_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contabilidad_movimie_asiento_id_3e1bfd85_fk_contabili` (`asiento_id`),
  KEY `contabilidad_movimie_cuenta_id_12d7e26d_fk_contabili` (`cuenta_id`),
  CONSTRAINT `contabilidad_movimie_asiento_id_3e1bfd85_fk_contabili` FOREIGN KEY (`asiento_id`) REFERENCES `contabilidad_asientocontable` (`id`),
  CONSTRAINT `contabilidad_movimie_cuenta_id_12d7e26d_fk_contabili` FOREIGN KEY (`cuenta_id`) REFERENCES `contabilidad_cuentacontable` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_pagocuentaporcobrar
CREATE TABLE IF NOT EXISTS `contabilidad_pagocuentaporcobrar` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha_pago` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `forma_pago` varchar(50) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `observaciones` longtext NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `cuenta_cobrar_id` bigint NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contabilidad_pagocue_cuenta_cobrar_id_f59b7ee5_fk_contabili` (`cuenta_cobrar_id`),
  KEY `contabilidad_pagocue_usuario_id_00231495_fk_auth_user` (`usuario_id`),
  CONSTRAINT `contabilidad_pagocue_cuenta_cobrar_id_f59b7ee5_fk_contabili` FOREIGN KEY (`cuenta_cobrar_id`) REFERENCES `contabilidad_cuentaporcobrar` (`id`),
  CONSTRAINT `contabilidad_pagocue_usuario_id_00231495_fk_auth_user` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_pagocuentaporpagar
CREATE TABLE IF NOT EXISTS `contabilidad_pagocuentaporpagar` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha_pago` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `forma_pago` varchar(50) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `observaciones` longtext NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `cuenta_pagar_id` bigint NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contabilidad_pagocue_cuenta_pagar_id_1988e260_fk_contabili` (`cuenta_pagar_id`),
  KEY `contabilidad_pagocue_usuario_id_98b9a0b0_fk_auth_user` (`usuario_id`),
  CONSTRAINT `contabilidad_pagocue_cuenta_pagar_id_1988e260_fk_contabili` FOREIGN KEY (`cuenta_pagar_id`) REFERENCES `contabilidad_cuentaporpagar` (`id`),
  CONSTRAINT `contabilidad_pagocue_usuario_id_98b9a0b0_fk_auth_user` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.contabilidad_tipocuenta
CREATE TABLE IF NOT EXISTS `contabilidad_tipocuenta` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descripcion` longtext NOT NULL,
  `activo` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.cotizaciones
CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero` int NOT NULL,
  `fecha` date NOT NULL,
  `validezDias` int NOT NULL DEFAULT '15',
  `idCliente` int NOT NULL,
  `observaciones` varchar(500) DEFAULT NULL,
  `subtotal` decimal(18,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(18,2) NOT NULL DEFAULT '0.00',
  `iva` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(20) NOT NULL DEFAULT 'VIGENTE',
  `creadoPor` int NOT NULL,
  `creadoDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `anuladoPor` int DEFAULT NULL,
  `anuladoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.cotizaciones_cotizacion
CREATE TABLE IF NOT EXISTS `cotizaciones_cotizacion` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `validez_dias` int NOT NULL,
  `estado` varchar(20) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `impuesto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `observaciones` longtext,
  `condiciones` longtext,
  `fecha_actualizacion` datetime(6) NOT NULL,
  `cliente_id` bigint NOT NULL,
  `usuario_creacion_id` int DEFAULT NULL,
  `venta_relacionada_id` bigint DEFAULT NULL,
  `fecha` date NOT NULL,
  `descuento_global` decimal(5,2) NOT NULL,
  `referencia_cliente` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `cotizaciones_cotizac_cliente_id_c6e3d6c6_fk_clientes_` (`cliente_id`),
  KEY `cotizaciones_cotizac_usuario_creacion_id_e3063380_fk_auth_user` (`usuario_creacion_id`),
  KEY `cotizaciones_cotizac_venta_relacionada_id_4e5a3764_fk_ventas_ve` (`venta_relacionada_id`),
  CONSTRAINT `cotizaciones_cotizac_cliente_id_c6e3d6c6_fk_clientes_` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_cliente` (`id`),
  CONSTRAINT `cotizaciones_cotizac_usuario_creacion_id_e3063380_fk_auth_user` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `cotizaciones_cotizac_venta_relacionada_id_4e5a3764_fk_ventas_ve` FOREIGN KEY (`venta_relacionada_id`) REFERENCES `ventas_venta` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.cotizaciones_detalle
CREATE TABLE IF NOT EXISTS `cotizaciones_detalle` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `idCotizacion` bigint NOT NULL,
  `idProducto` int NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `productoNombre` varchar(300) NOT NULL,
  `cantidad` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `precioUnitario` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `precioFinal` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `descuentoPorc` decimal(9,4) NOT NULL DEFAULT '0.0000',
  `descuentoValor` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `ivaValor` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `subtotal` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `total` decimal(18,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `fk_cotizaciones_det_enc` (`idCotizacion`),
  CONSTRAINT `fk_cotizaciones_det_enc` FOREIGN KEY (`idCotizacion`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.cotizaciones_detallecotizacion
CREATE TABLE IF NOT EXISTS `cotizaciones_detallecotizacion` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento_linea` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `descripcion_producto` longtext NOT NULL,
  `cotizacion_id` bigint NOT NULL,
  `producto_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cotizaciones_detallecoti_cotizacion_id_producto_i_0af4053f_uniq` (`cotizacion_id`,`producto_id`),
  KEY `cotizaciones_detalle_producto_id_3b92b18b_fk_productos` (`producto_id`),
  CONSTRAINT `cotizaciones_detalle_cotizacion_id_f5669320_fk_cotizacio` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones_cotizacion` (`id`),
  CONSTRAINT `cotizaciones_detalle_producto_id_3b92b18b_fk_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos_producto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.detalles_orden_proveedor
CREATE TABLE IF NOT EXISTS `detalles_orden_proveedor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `orden_id` int NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observaciones` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_orden_producto` (`orden_id`,`producto_id`),
  KEY `idx_producto` (`producto_id`),
  CONSTRAINT `detalles_orden_proveedor_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_compra_proveedor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalles_orden_proveedor_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Detalle de productos en órdenes';

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.django_admin_log
CREATE TABLE IF NOT EXISTS `django_admin_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action_time` datetime(6) NOT NULL,
  `object_id` longtext,
  `object_repr` varchar(200) NOT NULL,
  `action_flag` smallint unsigned NOT NULL,
  `change_message` longtext NOT NULL,
  `content_type_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `django_admin_log_content_type_id_c4bce8eb_fk_django_co` (`content_type_id`),
  KEY `django_admin_log_user_id_c564eba6_fk_auth_user_id` (`user_id`),
  CONSTRAINT `django_admin_log_content_type_id_c4bce8eb_fk_django_co` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `django_admin_log_user_id_c564eba6_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `django_admin_log_chk_1` CHECK ((`action_flag` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.django_content_type
CREATE TABLE IF NOT EXISTS `django_content_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `django_content_type_app_label_model_76bd3d3b_uniq` (`app_label`,`model`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.django_migrations
CREATE TABLE IF NOT EXISTS `django_migrations` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `app` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `applied` datetime(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.django_session
CREATE TABLE IF NOT EXISTS `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime(6) NOT NULL,
  PRIMARY KEY (`session_key`),
  KEY `django_session_expire_date_a5c62663` (`expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.empresas
CREATE TABLE IF NOT EXISTS `empresas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC de la empresa emisora',
  `razon_social` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_matriz` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contribuyente_especial` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `obligado_contabilidad` tinyint(1) NOT NULL DEFAULT '0',
  `logo` longblob COMMENT 'Logo de la empresa en formato binario',
  `certificado_p12_path` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificado_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificado_fecha_expiracion` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tipo_menu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'horizontal' COMMENT 'Tipo de menú: horizontal o vertical',
  `sri_ambiente` tinyint DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.facturas_compra
CREATE TABLE IF NOT EXISTS `facturas_compra` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idProveedor` int unsigned NOT NULL,
  `idUsuario` int unsigned NOT NULL,
  `numeroFactura` varchar(50) NOT NULL,
  `autorizacion` varchar(49) DEFAULT NULL,
  `fechaRecepcion` datetime NOT NULL,
  `subtotal` decimal(12,4) NOT NULL,
  `descuento` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `iva` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(12,4) NOT NULL,
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_facturas_compra_autorizacion` (`autorizacion`),
  KEY `fk_facturas_compra_proveedores` (`idProveedor`),
  KEY `fk_facturas_compra_usuarios` (`idUsuario`),
  CONSTRAINT `fk_facturas_compra_proveedores` FOREIGN KEY (`idProveedor`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `fk_facturas_compra_usuarios` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.facturas_compra_detalle
CREATE TABLE IF NOT EXISTS `facturas_compra_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idFacturaCompra` bigint unsigned NOT NULL,
  `idProducto` bigint unsigned NOT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `costoUnitario` decimal(12,4) NOT NULL,
  `total` decimal(12,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detalle_compra_factura` (`idFacturaCompra`),
  KEY `fk_detalle_compra_producto` (`idProducto`),
  CONSTRAINT `fk_detalle_compra_factura` FOREIGN KEY (`idFacturaCompra`) REFERENCES `facturas_compra` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_compra_producto` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.facturas_venta
CREATE TABLE IF NOT EXISTS `facturas_venta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idCliente` int unsigned NOT NULL,
  `idUsuario` int unsigned NOT NULL,
  `idCierreCaja` bigint unsigned DEFAULT NULL,
  `numeroFactura` varchar(50) NOT NULL,
  `fechaEmision` datetime NOT NULL,
  `subtotal` decimal(12,4) NOT NULL,
  `descuento` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `iva` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(12,4) NOT NULL,
  `formaPago` varchar(20) DEFAULT 'EFECTIVO',
  `estado` enum('EMITIDA','PAGADA','ANULADA') NOT NULL DEFAULT 'EMITIDA',
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `numeroAutorizacion` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `estadoFactura` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `numComprobante` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_facturas_venta_numero` (`numeroFactura`),
  KEY `fk_facturas_venta_clientes` (`idCliente`),
  KEY `fk_facturas_venta_usuarios` (`idUsuario`),
  KEY `fk_facturas_venta_cierre` (`idCierreCaja`),
  KEY `idx_estadoFactura` (`estadoFactura`),
  CONSTRAINT `fk_facturas_venta_cierre` FOREIGN KEY (`idCierreCaja`) REFERENCES `cierres_caja` (`id`),
  CONSTRAINT `fk_facturas_venta_clientes` FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_facturas_venta_usuarios` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.facturas_venta_detalle
CREATE TABLE IF NOT EXISTS `facturas_venta_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idFacturaVenta` bigint unsigned NOT NULL,
  `idProducto` bigint unsigned NOT NULL,
  `cantidad` decimal(12,2) NOT NULL,
  `precioUnitario` decimal(12,4) NOT NULL,
  `descuentoValor` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `ivaValor` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(12,4) NOT NULL,
  `productoNombre` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detalle_venta_factura` (`idFacturaVenta`),
  KEY `fk_detalle_venta_producto` (`idProducto`),
  CONSTRAINT `fk_detalle_venta_factura` FOREIGN KEY (`idFacturaVenta`) REFERENCES `facturas_venta` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_venta_producto` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.impuestos
CREATE TABLE IF NOT EXISTS `impuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `porcentaje` decimal(6,4) NOT NULL,
  `vigenteDesde` date DEFAULT NULL,
  `vigenteHasta` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_codigo_vigencia` (`codigo`,`activo`,`vigenteDesde`,`vigenteHasta`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_ajusteinventario
CREATE TABLE IF NOT EXISTS `inventario_ajusteinventario` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_ajuste` varchar(50) NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `tipo_ajuste` varchar(10) NOT NULL,
  `motivo` varchar(20) NOT NULL,
  `observaciones` longtext NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_ajuste` (`numero_ajuste`),
  KEY `inventario_ajusteinventario_usuario_id_ff88fff9_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `inventario_ajusteinventario_usuario_id_ff88fff9_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_compra
CREATE TABLE IF NOT EXISTS `inventario_compra` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_compra` varchar(50) NOT NULL,
  `numero_factura_proveedor` varchar(100) NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `fecha_factura` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL,
  `impuesto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observaciones` longtext NOT NULL,
  `proveedor_id` bigint NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_compra` (`numero_compra`),
  KEY `inventario_compra_proveedor_id_085e6477_fk_proveedor` (`proveedor_id`),
  KEY `inventario_compra_usuario_id_c3062858_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `inventario_compra_proveedor_id_085e6477_fk_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores_proveedor` (`id`),
  CONSTRAINT `inventario_compra_usuario_id_c3062858_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_configuracionstock
CREATE TABLE IF NOT EXISTS `inventario_configuracionstock` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `stock_minimo` int unsigned NOT NULL,
  `stock_maximo` int unsigned NOT NULL,
  `punto_reorden` int unsigned NOT NULL,
  `cantidad_reorden` int unsigned NOT NULL,
  `generar_orden_automatica` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_modificacion` datetime(6) NOT NULL,
  `creadoDate` datetime(6) NOT NULL,
  `editadoDate` datetime(6) NOT NULL,
  `anulado` tinyint(1) NOT NULL,
  `creadoPor_id` int DEFAULT NULL,
  `editadoPor_id` int DEFAULT NULL,
  `producto_id` bigint NOT NULL,
  `proveedor_preferido_id` bigint DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `ubicacion_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventario_configuracion_producto_id_ubicacion_id_f963ad9d_uniq` (`producto_id`,`ubicacion_id`),
  KEY `inventario_configura_creadoPor_id_e8e974cf_fk_auth_user` (`creadoPor_id`),
  KEY `inventario_configura_editadoPor_id_1d161164_fk_auth_user` (`editadoPor_id`),
  KEY `inventario_configura_proveedor_preferido__4b8da352_fk_proveedor` (`proveedor_preferido_id`),
  KEY `inventario_configura_usuario_id_b1a384f0_fk_auth_user` (`usuario_id`),
  KEY `inventario_configura_ubicacion_id_41702e3e_fk_inventari` (`ubicacion_id`),
  CONSTRAINT `inventario_configura_creadoPor_id_e8e974cf_fk_auth_user` FOREIGN KEY (`creadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_configura_editadoPor_id_1d161164_fk_auth_user` FOREIGN KEY (`editadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_configura_producto_id_bbc5bbe5_fk_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos_producto` (`id`),
  CONSTRAINT `inventario_configura_proveedor_preferido__4b8da352_fk_proveedor` FOREIGN KEY (`proveedor_preferido_id`) REFERENCES `proveedores_proveedor` (`id`),
  CONSTRAINT `inventario_configura_ubicacion_id_41702e3e_fk_inventari` FOREIGN KEY (`ubicacion_id`) REFERENCES `inventario_ubicacion` (`id`),
  CONSTRAINT `inventario_configura_usuario_id_b1a384f0_fk_auth_user` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_configuracionstock_chk_1` CHECK ((`stock_minimo` >= 0)),
  CONSTRAINT `inventario_configuracionstock_chk_2` CHECK ((`stock_maximo` >= 0)),
  CONSTRAINT `inventario_configuracionstock_chk_3` CHECK ((`punto_reorden` >= 0)),
  CONSTRAINT `inventario_configuracionstock_chk_4` CHECK ((`cantidad_reorden` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_detalleajuste
CREATE TABLE IF NOT EXISTS `inventario_detalleajuste` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad_anterior` int unsigned NOT NULL,
  `cantidad_nueva` int unsigned NOT NULL,
  `observaciones` longtext NOT NULL,
  `ajuste_id` bigint NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `precio_nuevo` decimal(12,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventario_detalleajuste_ajuste_id_producto_id_0b7f9486_uniq` (`ajuste_id`,`producto_id`),
  KEY `inventario_detalleaj_producto_id_63f542b9_fk_productos` (`producto_id`),
  CONSTRAINT `fk_inv_det_ajuste_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `inventario_detalleaj_ajuste_id_150af47b_fk_inventari` FOREIGN KEY (`ajuste_id`) REFERENCES `inventario_ajusteinventario` (`id`),
  CONSTRAINT `inventario_detalleajuste_chk_1` CHECK ((`cantidad_anterior` >= 0)),
  CONSTRAINT `inventario_detalleajuste_chk_2` CHECK ((`cantidad_nueva` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_detallecompra
CREATE TABLE IF NOT EXISTS `inventario_detallecompra` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad` int unsigned NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento_linea` decimal(10,2) NOT NULL,
  `compra_id` bigint NOT NULL,
  `producto_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventario_detallecompra_compra_id_producto_id_f3b71404_uniq` (`compra_id`,`producto_id`),
  KEY `inventario_detalleco_producto_id_c4a35c80_fk_productos` (`producto_id`),
  CONSTRAINT `inventario_detalleco_compra_id_bf08cac8_fk_inventari` FOREIGN KEY (`compra_id`) REFERENCES `inventario_compra` (`id`),
  CONSTRAINT `inventario_detalleco_producto_id_c4a35c80_fk_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos_producto` (`id`),
  CONSTRAINT `inventario_detallecompra_chk_1` CHECK ((`cantidad` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_detalleordencompra
CREATE TABLE IF NOT EXISTS `inventario_detalleordencompra` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad_solicitada` int unsigned NOT NULL,
  `cantidad_recibida` int unsigned NOT NULL,
  `precio_unitario` decimal(12,4) NOT NULL,
  `descuento_linea` decimal(12,2) NOT NULL,
  `stock_actual` int unsigned NOT NULL,
  `stock_minimo` int unsigned NOT NULL,
  `motivo_solicitud` longtext NOT NULL,
  `producto_id` bigint NOT NULL,
  `orden_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventario_detalleordencompra_orden_id_producto_id_823b1e8f_uniq` (`orden_id`,`producto_id`),
  KEY `inventario_detalleor_producto_id_efe44830_fk_productos` (`producto_id`),
  CONSTRAINT `inventario_detalleor_orden_id_c374f37f_fk_inventari` FOREIGN KEY (`orden_id`) REFERENCES `inventario_ordencompra` (`id`),
  CONSTRAINT `inventario_detalleor_producto_id_efe44830_fk_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos_producto` (`id`),
  CONSTRAINT `inventario_detalleordencompra_chk_1` CHECK ((`cantidad_solicitada` >= 0)),
  CONSTRAINT `inventario_detalleordencompra_chk_2` CHECK ((`cantidad_recibida` >= 0)),
  CONSTRAINT `inventario_detalleordencompra_chk_3` CHECK ((`stock_actual` >= 0)),
  CONSTRAINT `inventario_detalleordencompra_chk_4` CHECK ((`stock_minimo` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_detalletransferencia
CREATE TABLE IF NOT EXISTS `inventario_detalletransferencia` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad` int unsigned NOT NULL,
  `cantidad_recibida` int unsigned NOT NULL,
  `stock_origen_antes` int unsigned NOT NULL,
  `stock_destino_antes` int unsigned NOT NULL,
  `observaciones` longtext NOT NULL,
  `producto_id` bigint NOT NULL,
  `transferencia_id` bigint NOT NULL,
  `lote_id` bigint DEFAULT NULL,
  `precio_origen` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_destino` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cambio_precio` tinyint(1) NOT NULL DEFAULT '0',
  `cantidad_cajas` int unsigned NOT NULL DEFAULT '0',
  `cantidad_fracciones` int unsigned NOT NULL DEFAULT '0',
  `unidades_por_caja` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventario_detalletransf_transferencia_id_product_e639f343_uniq` (`transferencia_id`,`producto_id`),
  KEY `inventario_detalletr_producto_id_df91b3bd_fk_productos` (`producto_id`),
  KEY `idx_detalle_lote` (`lote_id`),
  CONSTRAINT `inventario_detalletr_producto_id_df91b3bd_fk_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos_producto` (`id`),
  CONSTRAINT `inventario_detalletr_transferencia_id_b4864fc6_fk_inventari` FOREIGN KEY (`transferencia_id`) REFERENCES `inventario_transferenciastock` (`id`),
  CONSTRAINT `inventario_detalletransferencia_chk_1` CHECK ((`cantidad` >= 0)),
  CONSTRAINT `inventario_detalletransferencia_chk_2` CHECK ((`cantidad_recibida` >= 0)),
  CONSTRAINT `inventario_detalletransferencia_chk_3` CHECK ((`stock_origen_antes` >= 0)),
  CONSTRAINT `inventario_detalletransferencia_chk_4` CHECK ((`stock_destino_antes` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_kardex
CREATE TABLE IF NOT EXISTS `inventario_kardex` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fecha` datetime(6) NOT NULL,
  `tipo_movimiento` varchar(25) NOT NULL,
  `concepto` varchar(25) NOT NULL,
  `cantidad` int unsigned NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `saldo_cantidad` int unsigned NOT NULL,
  `saldo_valor` decimal(12,2) NOT NULL,
  `numero_documento` varchar(100) NOT NULL,
  `observaciones` longtext NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `inventario_kardex_producto_id_b15b456f_fk_productos_producto_id` (`producto_id`),
  KEY `inventario_kardex_usuario_id_c85c7473_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `inventario_kardex_producto_id_fk_productos_id` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `inventario_kardex_usuario_id_c85c7473_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_kardex_chk_1` CHECK ((`cantidad` >= 0)),
  CONSTRAINT `inventario_kardex_chk_2` CHECK ((`saldo_cantidad` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_loteproducto
CREATE TABLE IF NOT EXISTS `inventario_loteproducto` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_lote` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `fecha_fabricacion` date DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `cantidad_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_disponible` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_reservada` decimal(10,2) NOT NULL DEFAULT '0.00',
  `costo_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `numero_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_actualizacion` datetime(6) NOT NULL,
  `producto_id` bigint NOT NULL,
  `ubicacion_id` bigint NOT NULL,
  `proveedor_id` bigint DEFAULT NULL,
  `creadoPor_id` bigint DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  `editadoPor_id` bigint DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fk_lote_producto` (`producto_id`),
  KEY `idx_fk_lote_ubicacion` (`ubicacion_id`),
  KEY `idx_fk_lote_proveedor` (`proveedor_id`),
  KEY `idx_fk_lote_usuario` (`creadoPor_id`),
  KEY `idx_lote_producto` (`producto_id`),
  KEY `idx_lote_ubicacion` (`ubicacion_id`),
  KEY `idx_lote_caducidad` (`fecha_caducidad`),
  KEY `idx_lote_activo` (`activo`),
  KEY `idx_lote_numero` (`numero_lote`),
  KEY `idx_lote_fefo` (`producto_id`,`ubicacion_id`,`fecha_caducidad`,`fecha_ingreso`),
  CONSTRAINT `chk_cantidad_disponible_positiva` CHECK ((`cantidad_disponible` >= 0)),
  CONSTRAINT `chk_cantidad_inicial_positiva` CHECK ((`cantidad_inicial` >= 0)),
  CONSTRAINT `chk_cantidad_reservada_positiva` CHECK ((`cantidad_reservada` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=2048 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_ordencompra
CREATE TABLE IF NOT EXISTS `inventario_ordencompra` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_orden` varchar(50) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_envio` datetime(6) DEFAULT NULL,
  `fecha_entrega_esperada` date DEFAULT NULL,
  `fecha_entrega_real` date DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `prioridad` varchar(10) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL,
  `impuesto` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `observaciones` longtext NOT NULL,
  `generada_automaticamente` tinyint(1) NOT NULL,
  `creadoDate` datetime(6) NOT NULL,
  `editadoDate` datetime(6) NOT NULL,
  `anulado` tinyint(1) NOT NULL,
  `creadoPor_id` int DEFAULT NULL,
  `editadoPor_id` int DEFAULT NULL,
  `proveedor_id` bigint NOT NULL,
  `usuario_creacion_id` int NOT NULL,
  `usuario_envio_id` int DEFAULT NULL,
  `ubicacion_destino_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_orden` (`numero_orden`),
  KEY `inventario_ordencompra_creadoPor_id_ca3b30fe_fk_auth_user_id` (`creadoPor_id`),
  KEY `inventario_ordencompra_editadoPor_id_682f5cb5_fk_auth_user_id` (`editadoPor_id`),
  KEY `inventario_ordencomp_proveedor_id_eb72ae94_fk_proveedor` (`proveedor_id`),
  KEY `inventario_ordencomp_usuario_creacion_id_e0b0aec8_fk_auth_user` (`usuario_creacion_id`),
  KEY `inventario_ordencompra_usuario_envio_id_6dbf29fc_fk_auth_user_id` (`usuario_envio_id`),
  KEY `inventario_ordencomp_ubicacion_destino_id_7d52c342_fk_inventari` (`ubicacion_destino_id`),
  CONSTRAINT `inventario_ordencomp_proveedor_id_eb72ae94_fk_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores_proveedor` (`id`),
  CONSTRAINT `inventario_ordencomp_ubicacion_destino_id_7d52c342_fk_inventari` FOREIGN KEY (`ubicacion_destino_id`) REFERENCES `inventario_ubicacion` (`id`),
  CONSTRAINT `inventario_ordencomp_usuario_creacion_id_e0b0aec8_fk_auth_user` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_ordencompra_creadoPor_id_ca3b30fe_fk_auth_user_id` FOREIGN KEY (`creadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_ordencompra_editadoPor_id_682f5cb5_fk_auth_user_id` FOREIGN KEY (`editadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_ordencompra_usuario_envio_id_6dbf29fc_fk_auth_user_id` FOREIGN KEY (`usuario_envio_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_pagocompra
CREATE TABLE IF NOT EXISTS `inventario_pagocompra` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `metodo_pago` varchar(20) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `compra_id` bigint NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `inventario_pagocompra_compra_id_b846b493_fk_inventario_compra_id` (`compra_id`),
  KEY `inventario_pagocompra_usuario_id_d9b73374_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `inventario_pagocompra_compra_id_b846b493_fk_inventario_compra_id` FOREIGN KEY (`compra_id`) REFERENCES `inventario_compra` (`id`),
  CONSTRAINT `inventario_pagocompra_usuario_id_d9b73374_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_stockubicacion
CREATE TABLE IF NOT EXISTS `inventario_stockubicacion` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_maximo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `punto_reorden` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ultima_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creadoDate` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `editadoDate` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  `producto_id` bigint NOT NULL,
  `ubicacion_id` bigint NOT NULL,
  `creadoPor_id` bigint DEFAULT NULL,
  `editadoPor_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_producto_ubicacion` (`producto_id`,`ubicacion_id`),
  KEY `idx_stock_producto` (`producto_id`),
  KEY `idx_stock_ubicacion` (`ubicacion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2048 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_transferenciastock
CREATE TABLE IF NOT EXISTS `inventario_transferenciastock` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_transferencia` varchar(50) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_envio` datetime(6) DEFAULT NULL,
  `fecha_recepcion` datetime(6) DEFAULT NULL,
  `estado` varchar(15) NOT NULL,
  `tipo` varchar(15) NOT NULL,
  `observaciones` longtext NOT NULL,
  `motivo` varchar(200) NOT NULL,
  `creadoDate` datetime(6) NOT NULL,
  `editadoDate` datetime(6) NOT NULL,
  `anulado` tinyint(1) NOT NULL,
  `creadoPor_id` int DEFAULT NULL,
  `editadoPor_id` int DEFAULT NULL,
  `usuario_creacion_id` int NOT NULL,
  `usuario_envio_id` int DEFAULT NULL,
  `usuario_recepcion_id` int DEFAULT NULL,
  `ubicacion_destino_id` bigint NOT NULL,
  `ubicacion_origen_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_transferencia` (`numero_transferencia`),
  KEY `inventario_transfere_creadoPor_id_c9d80f45_fk_auth_user` (`creadoPor_id`),
  KEY `inventario_transfere_editadoPor_id_b5d344b7_fk_auth_user` (`editadoPor_id`),
  KEY `inventario_transfere_usuario_creacion_id_e7776e87_fk_auth_user` (`usuario_creacion_id`),
  KEY `inventario_transfere_usuario_envio_id_f8c86d96_fk_auth_user` (`usuario_envio_id`),
  KEY `inventario_transfere_usuario_recepcion_id_4283fbce_fk_auth_user` (`usuario_recepcion_id`),
  KEY `inventario_transfere_ubicacion_destino_id_8a206f6c_fk_inventari` (`ubicacion_destino_id`),
  KEY `inventario_transfere_ubicacion_origen_id_50e517b1_fk_inventari` (`ubicacion_origen_id`),
  CONSTRAINT `inventario_transfere_creadoPor_id_c9d80f45_fk_auth_user` FOREIGN KEY (`creadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_transfere_editadoPor_id_b5d344b7_fk_auth_user` FOREIGN KEY (`editadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_transfere_ubicacion_destino_id_8a206f6c_fk_inventari` FOREIGN KEY (`ubicacion_destino_id`) REFERENCES `inventario_ubicacion` (`id`),
  CONSTRAINT `inventario_transfere_ubicacion_origen_id_50e517b1_fk_inventari` FOREIGN KEY (`ubicacion_origen_id`) REFERENCES `inventario_ubicacion` (`id`),
  CONSTRAINT `inventario_transfere_usuario_creacion_id_e7776e87_fk_auth_user` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_transfere_usuario_envio_id_f8c86d96_fk_auth_user` FOREIGN KEY (`usuario_envio_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_transfere_usuario_recepcion_id_4283fbce_fk_auth_user` FOREIGN KEY (`usuario_recepcion_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.inventario_ubicacion
CREATE TABLE IF NOT EXISTS `inventario_ubicacion` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(15) NOT NULL,
  `direccion` longtext NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `responsable` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `es_principal` tinyint(1) NOT NULL,
  `creadoDate` datetime(6) NOT NULL,
  `editadoDate` datetime(6) NOT NULL,
  `anulado` tinyint(1) NOT NULL,
  `creadoPor_id` int NOT NULL,
  `editadoPor_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `inventario_ubicacion_creadoPor_id_b55c74dc_fk_auth_user_id` (`creadoPor_id`),
  KEY `inventario_ubicacion_editadoPor_id_7b1a0b23_fk_auth_user_id` (`editadoPor_id`),
  CONSTRAINT `inventario_ubicacion_creadoPor_id_b55c74dc_fk_auth_user_id` FOREIGN KEY (`creadoPor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `inventario_ubicacion_editadoPor_id_7b1a0b23_fk_auth_user_id` FOREIGN KEY (`editadoPor_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.kardex_movimientos
CREATE TABLE IF NOT EXISTS `kardex_movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `idProducto` int unsigned NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipoMovimiento` varchar(100) NOT NULL COMMENT 'Ej: VENTA, COMPRA, AJUSTE INGRESO, AJUSTE EGRESO, DEVOLUCIÓN',
  `detalle` varchar(300) NOT NULL COMMENT 'Ej: Factura Venta N° 001-001-12345',
  `ingreso` decimal(12,2) NOT NULL DEFAULT '0.00',
  `egreso` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo` decimal(12,2) NOT NULL COMMENT 'Saldo del producto DESPUÉS de este movimiento',
  `idUbicacion` int DEFAULT NULL COMMENT 'ID de la ubicación donde ocurrió el movimiento',
  PRIMARY KEY (`id`),
  KEY `fk_kardex_producto_idx` (`idProducto`),
  CONSTRAINT `fk_kardex_producto` FOREIGN KEY (`id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.laboratorios
CREATE TABLE IF NOT EXISTS `laboratorios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_laboratorios_codigo` (`codigo`),
  KEY `idx_laboratorios_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.lab_parametros
CREATE TABLE IF NOT EXISTS `lab_parametros` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `proceso_id` int unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `ref_min` varchar(50) DEFAULT NULL,
  `ref_max` varchar(50) DEFAULT NULL,
  `orden` int NOT NULL DEFAULT '0',
  `notas` text,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_parametro_proceso_idx` (`proceso_id`),
  CONSTRAINT `fk_parametro_proceso` FOREIGN KEY (`proceso_id`) REFERENCES `lab_procesos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.lab_procesos
CREATE TABLE IF NOT EXISTS `lab_procesos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `laboratorio_id` int unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `metodo` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_proceso_laboratorio_idx` (`laboratorio_id`),
  CONSTRAINT `fk_proceso_laboratorio` FOREIGN KEY (`laboratorio_id`) REFERENCES `laboratorios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.lab_resultados
CREATE TABLE IF NOT EXISTS `lab_resultados` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `proceso_id` int unsigned NOT NULL,
  `fecha_emision` datetime NOT NULL,
  `paciente_nombre` varchar(255) NOT NULL,
  `paciente_id` varchar(100) DEFAULT NULL,
  `medico_solicitante` varchar(255) DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id`),
  KEY `fk_resultado_proceso_idx` (`proceso_id`),
  CONSTRAINT `fk_resultado_proceso` FOREIGN KEY (`proceso_id`) REFERENCES `lab_procesos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.lab_resultado_detalles
CREATE TABLE IF NOT EXISTS `lab_resultado_detalles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `resultado_id` int unsigned NOT NULL,
  `parametro_nombre` varchar(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `unidad` varchar(50) DEFAULT NULL,
  `rango_referencia` varchar(100) DEFAULT NULL,
  `fuera_de_rango` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_detalle_resultado_idx` (`resultado_id`),
  CONSTRAINT `fk_detalle_resultado` FOREIGN KEY (`resultado_id`) REFERENCES `lab_resultados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.marcas
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ordenes_compra_proveedor
CREATE TABLE IF NOT EXISTS `ordenes_compra_proveedor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_orden` varchar(20) NOT NULL COMMENT 'Número único de orden (ej: ORD-2025-001)',
  `proveedor_id` int unsigned NOT NULL,
  `fecha_orden` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `iva` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` enum('BORRADOR','ENVIADA','CONFIRMADA','RECIBIDA','CANCELADA') NOT NULL DEFAULT 'BORRADOR',
  `observaciones` text,
  `enviada_whatsapp` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_envio_whatsapp` datetime DEFAULT NULL,
  `creado_por` int NOT NULL,
  `creado_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editado_por` int DEFAULT NULL,
  `editado_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_orden` (`numero_orden`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_orden` (`fecha_orden`),
  CONSTRAINT `ordenes_compra_proveedor_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Órdenes de compra a proveedores';

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.pacientes
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `documento` varchar(100) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.permisos
CREATE TABLE IF NOT EXISTS `permisos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombreMenu` varchar(100) NOT NULL,
  `claveMenu` varchar(100) NOT NULL,
  `idPadre` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permisos_claveMenu` (`claveMenu`),
  KEY `fk_permisos_padre` (`idPadre`),
  CONSTRAINT `fk_permisos_padre` FOREIGN KEY (`idPadre`) REFERENCES `permisos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `codigoPrincipal` varchar(50) NOT NULL,
  `codigoAuxiliar` varchar(50) DEFAULT NULL,
  `descripcion` text,
  `observaciones` text,
  `registroSanitario` varchar(50) DEFAULT NULL,
  `fechaCaducidad` date DEFAULT NULL,
  `idTipoProducto` int unsigned NOT NULL DEFAULT '1',
  `idClaseProducto` int unsigned NOT NULL DEFAULT '1',
  `idCategoria` int unsigned NOT NULL DEFAULT '1',
  `idSubcategoria` int unsigned NOT NULL DEFAULT '1',
  `idSubnivel` int unsigned DEFAULT NULL,
  `idMarca` int unsigned NOT NULL DEFAULT '1',
  `idLaboratorio` int unsigned DEFAULT '1',
  `stock` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stockMinimo` decimal(12,2) DEFAULT '0.00',
  `stockMaximo` decimal(12,2) DEFAULT '0.00',
  `costoUnidad` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `costoCaja` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `pvpUnidad` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `esDivisible` tinyint(1) NOT NULL DEFAULT '0',
  `esPsicotropico` tinyint(1) NOT NULL DEFAULT '0',
  `requiereCadenaFrio` tinyint(1) NOT NULL DEFAULT '0',
  `requiereSeguimiento` tinyint(1) NOT NULL DEFAULT '0',
  `calculoABCManual` tinyint(1) NOT NULL DEFAULT '0',
  `clasificacionABC` char(1) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `editadoPor` int unsigned DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `anuladoPor` int unsigned DEFAULT NULL,
  `anuladoDate` datetime DEFAULT NULL,
  `precioVenta` decimal(12,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_productos_codigoPrincipal` (`codigoPrincipal`),
  KEY `fk_prod_tipo` (`idTipoProducto`),
  KEY `fk_prod_clase` (`idClaseProducto`),
  KEY `fk_prod_categoria` (`idCategoria`),
  KEY `fk_prod_subcategoria` (`idSubcategoria`),
  KEY `fk_prod_subnivel` (`idSubnivel`),
  KEY `fk_prod_marca` (`idMarca`),
  KEY `fk_prod_laboratorio` (`idLaboratorio`),
  KEY `idx_fecha_caducidad` (`fechaCaducidad`),
  CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`id`),
  CONSTRAINT `fk_prod_clase` FOREIGN KEY (`idClaseProducto`) REFERENCES `clases_producto` (`id`),
  CONSTRAINT `fk_prod_laboratorio` FOREIGN KEY (`idLaboratorio`) REFERENCES `laboratorios` (`id`),
  CONSTRAINT `fk_prod_marca` FOREIGN KEY (`idMarca`) REFERENCES `marcas` (`id`),
  CONSTRAINT `fk_prod_subcategoria` FOREIGN KEY (`idSubcategoria`) REFERENCES `subcategorias` (`id`),
  CONSTRAINT `fk_prod_subnivel` FOREIGN KEY (`idSubnivel`) REFERENCES `subniveles_producto` (`id`),
  CONSTRAINT `fk_prod_tipo` FOREIGN KEY (`idTipoProducto`) REFERENCES `tipos_producto` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1804 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_categoria
CREATE TABLE IF NOT EXISTS `productos_categoria` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` longtext NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_marca
CREATE TABLE IF NOT EXISTS `productos_marca` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` longtext NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_percha
CREATE TABLE IF NOT EXISTS `productos_percha` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` longtext,
  `filas` int unsigned NOT NULL,
  `columnas` int unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `seccion_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productos_percha_seccion_id_nombre_8eb03cb8_uniq` (`seccion_id`,`nombre`),
  KEY `idx_percha_seccion` (`seccion_id`),
  KEY `idx_percha_activo` (`activo`),
  CONSTRAINT `productos_percha_seccion_id_f3caa53d_fk_productos_seccion_id` FOREIGN KEY (`seccion_id`) REFERENCES `productos_seccion` (`id`),
  CONSTRAINT `productos_percha_chk_1` CHECK ((`filas` >= 0)),
  CONSTRAINT `productos_percha_chk_2` CHECK ((`columnas` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_producto
CREATE TABLE IF NOT EXISTS `productos_producto` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` longtext NOT NULL,
  `tipo_producto` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_mayoreo` decimal(10,2) DEFAULT NULL,
  `stock_minimo` int unsigned NOT NULL,
  `stock_maximo` int unsigned NOT NULL,
  `stock_actual` int unsigned NOT NULL,
  `aplica_iva` tinyint(1) NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_modificacion` datetime(6) NOT NULL,
  `categoria_id` bigint NOT NULL,
  `marca_id` bigint NOT NULL,
  `unidad_medida_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `productos_producto_categoria_id_1fef506a_fk_productos` (`categoria_id`),
  KEY `productos_producto_marca_id_fc6a9dea_fk_productos_marca_id` (`marca_id`),
  KEY `productos_producto_unidad_medida_id_a39b68f5_fk_productos` (`unidad_medida_id`),
  CONSTRAINT `productos_producto_categoria_id_1fef506a_fk_productos` FOREIGN KEY (`categoria_id`) REFERENCES `productos_categoria` (`id`),
  CONSTRAINT `productos_producto_marca_id_fc6a9dea_fk_productos_marca_id` FOREIGN KEY (`marca_id`) REFERENCES `productos_marca` (`id`),
  CONSTRAINT `productos_producto_unidad_medida_id_a39b68f5_fk_productos` FOREIGN KEY (`unidad_medida_id`) REFERENCES `productos_unidadmedida` (`id`),
  CONSTRAINT `productos_producto_chk_1` CHECK ((`stock_minimo` >= 0)),
  CONSTRAINT `productos_producto_chk_2` CHECK ((`stock_maximo` >= 0)),
  CONSTRAINT `productos_producto_chk_3` CHECK ((`stock_actual` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_seccion
CREATE TABLE IF NOT EXISTS `productos_seccion` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` longtext,
  `color` varchar(7) NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `orden` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seccion_activo` (`activo`),
  CONSTRAINT `productos_seccion_chk_1` CHECK ((`orden` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_ubicacionproducto
CREATE TABLE IF NOT EXISTS `productos_ubicacionproducto` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `fila` int unsigned NOT NULL,
  `columna` int unsigned NOT NULL,
  `observaciones` longtext,
  `activo` tinyint(1) NOT NULL,
  `fecha_ubicacion` datetime(6) NOT NULL,
  `percha_id` bigint NOT NULL,
  `producto_id` bigint NOT NULL,
  `usuario_ubicacion_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_id` (`producto_id`),
  UNIQUE KEY `productos_ubicacionproducto_percha_id_fila_columna_e39940ce_uniq` (`percha_id`,`fila`,`columna`),
  KEY `idx_ubicacion_producto` (`producto_id`),
  KEY `idx_ubicacion_percha` (`percha_id`),
  KEY `idx_ubicacion_activo` (`activo`),
  CONSTRAINT `productos_ubicacionp_percha_id_35a29568_fk_productos` FOREIGN KEY (`percha_id`) REFERENCES `productos_percha` (`id`),
  CONSTRAINT `productos_ubicacionproducto_chk_1` CHECK ((`fila` >= 0)),
  CONSTRAINT `productos_ubicacionproducto_chk_2` CHECK ((`columna` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.productos_unidadmedida
CREATE TABLE IF NOT EXISTS `productos_unidadmedida` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `abreviacion` varchar(10) NOT NULL,
  `activo` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  UNIQUE KEY `abreviacion` (`abreviacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ruc` varchar(13) NOT NULL,
  `razonSocial` varchar(200) NOT NULL,
  `nombreComercial` varchar(200) DEFAULT NULL,
  `direccion` text,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `creadoPor` int unsigned NOT NULL,
  `creadoDate` datetime NOT NULL,
  `editadoPor` int unsigned DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proveedores_ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.proveedores_proveedor
CREATE TABLE IF NOT EXISTS `proveedores_proveedor` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `nombre_comercial` varchar(200) NOT NULL,
  `razon_social` varchar(200) NOT NULL,
  `contacto_principal` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(254) NOT NULL,
  `direccion` longtext NOT NULL,
  `limite_credito` decimal(10,2) NOT NULL,
  `dias_credito` int unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_modificacion` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `numero_documento` (`numero_documento`),
  CONSTRAINT `proveedores_proveedor_chk_1` CHECK ((`dias_credito` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime NOT NULL,
  `editadoPor` int unsigned DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `anuladoPor` int unsigned DEFAULT NULL,
  `anuladoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.rol_permisos
CREATE TABLE IF NOT EXISTS `rol_permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idRol` int unsigned NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permiso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `puede_crear` tinyint(1) DEFAULT '0',
  `puede_editar` tinyint(1) DEFAULT '0',
  `puede_eliminar` tinyint(1) DEFAULT '0',
  `puede_ver` tinyint(1) DEFAULT '1',
  `creadoPor` int DEFAULT NULL,
  `creadoDate` datetime NOT NULL,
  `editadoPor` int DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rol_permiso` (`idRol`,`modulo`,`permiso`),
  CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`idRol`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.secuenciales
CREATE TABLE IF NOT EXISTS `secuenciales` (
  `Id` int NOT NULL,
  `Estab` varchar(3) DEFAULT NULL,
  `PtoEmi` varchar(3) DEFAULT NULL,
  `UltimoSecuencial` int DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.secuencias
CREATE TABLE IF NOT EXISTS `secuencias` (
  `nombre` varchar(50) NOT NULL,
  `valor` int NOT NULL,
  `prefijo` varchar(20) DEFAULT NULL,
  `longitud` int NOT NULL DEFAULT '6',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para procedimiento SistemaPosDB.sp_consultar_auditoria
DELIMITER //
CREATE PROCEDURE `sp_consultar_auditoria`(
  IN pFechaDesde DATETIME,
  IN pFechaHasta DATETIME,
  IN pUsuario    VARCHAR(100),
  IN pAccion     VARCHAR(30)
)
BEGIN
  SELECT 
    a.fecha      AS `Fecha`,
    a.usuario    AS `Usuario`,
    a.modulo     AS `Modulo`,
    a.accion     AS `Accion`,
    a.descripcion AS `Detalle`,
    a.entidad,
    a.idEntidad,
    a.ip,
    a.host,
    a.origen
  FROM auditoria a
  WHERE a.fecha >= pFechaDesde
    AND a.fecha <  DATE_ADD(DATE(pFechaHasta), INTERVAL 1 DAY)
    AND (IFNULL(pUsuario, '') = '' OR UPPER(pUsuario) = 'TODOS' OR a.usuario = pUsuario)
    AND (IFNULL(pAccion,  '') = '' OR UPPER(pAccion)  = 'TODAS' OR a.accion  = UPPER(pAccion))
    AND (
      COALESCE(pUsuario, '') = ''
      OR UPPER(pUsuario COLLATE utf8mb4_unicode_ci) = 'TODOS' COLLATE utf8mb4_unicode_ci
      OR a.usuario COLLATE utf8mb4_unicode_ci = pUsuario COLLATE utf8mb4_unicode_ci
    )
    AND (
      COALESCE(pAccion, '') = ''
      OR UPPER(pAccion COLLATE utf8mb4_unicode_ci) = 'TODAS' COLLATE utf8mb4_unicode_ci
      OR a.accion COLLATE utf8mb4_unicode_ci = UPPER(pAccion) COLLATE utf8mb4_unicode_ci
    )
  ORDER BY a.fecha DESC;
END//
DELIMITER ;

-- Volcando estructura para procedimiento SistemaPosDB.sp_listar_usuarios_auditoria
DELIMITER //
CREATE PROCEDURE `sp_listar_usuarios_auditoria`()
BEGIN
  SELECT DISTINCT a.idUsuario AS `id`, a.usuario AS `nombreUsuario`
  FROM auditoria a
  ORDER BY a.usuario;
END//
DELIMITER ;

-- Volcando estructura para procedimiento SistemaPosDB.sp_registrar_auditoria
DELIMITER //
CREATE PROCEDURE `sp_registrar_auditoria`(
  IN pIdUsuario   INT,
  IN pUsuario     VARCHAR(100),
  IN pModulo      VARCHAR(100),
  IN pAccion      VARCHAR(30),
  IN pEntidad     VARCHAR(100),
  IN pIdEntidad   BIGINT,
  IN pDescripcion TEXT,
  IN pIp          VARCHAR(45),
  IN pHost        VARCHAR(100),
  IN pOrigen      VARCHAR(50),
  IN pExtra       TEXT
)
BEGIN
  INSERT INTO auditoria
    (fecha, idUsuario, usuario, modulo, accion, entidad, idEntidad, descripcion, ip, host, origen, extra)
  VALUES
    (NOW(), pIdUsuario, pUsuario, pModulo, UPPER(pAccion), pEntidad, pIdEntidad, pDescripcion, pIp, pHost, pOrigen, pExtra);
END//
DELIMITER ;

-- Volcando estructura para tabla SistemaPosDB.subcategorias
CREATE TABLE IF NOT EXISTS `subcategorias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `idCategoria` int unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_subcat_cat` (`idCategoria`),
  CONSTRAINT `fk_subcat_cat` FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.subniveles_producto
CREATE TABLE IF NOT EXISTS `subniveles_producto` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.talonario
CREATE TABLE IF NOT EXISTS `talonario` (
  `Establecimiento` varchar(3) NOT NULL,
  `PuntoEmision` varchar(3) NOT NULL,
  `UltimoNumero` int DEFAULT NULL,
  `Descripcion` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`Establecimiento`,`PuntoEmision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.tipos_ajuste
CREATE TABLE IF NOT EXISTS `tipos_ajuste` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipoOperacion` enum('INGRESO','EGRESO') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.tipos_producto
CREATE TABLE IF NOT EXISTS `tipos_producto` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `idRol` int unsigned NOT NULL,
  `nombreUsuario` varchar(50) NOT NULL,
  `contrasenaHash` varchar(255) NOT NULL,
  `nombreCompleto` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `tipoMenu` varchar(20) DEFAULT 'horizontal',
  `creadoPor` int unsigned DEFAULT NULL,
  `creadoDate` datetime NOT NULL,
  `editadoPor` int unsigned DEFAULT NULL,
  `editadoDate` datetime DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT '0',
  `anuladoPor` int unsigned DEFAULT NULL,
  `anuladoDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_nombreUsuario` (`nombreUsuario`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  KEY `fk_usuarios_roles` (`idRol`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`idRol`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.usuarios_configuracionempresa
CREATE TABLE IF NOT EXISTS `usuarios_configuracionempresa` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `rtn` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(254) NOT NULL,
  `direccion` longtext NOT NULL,
  `sitio_web` varchar(200) NOT NULL,
  `cai` varchar(50) NOT NULL,
  `rango_inicial` varchar(20) NOT NULL,
  `rango_final` varchar(20) NOT NULL,
  `fecha_limite_emision` date DEFAULT NULL,
  `moneda` varchar(50) NOT NULL,
  `simbolo_moneda` varchar(5) NOT NULL,
  `impuesto_defecto` decimal(5,2) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_modificacion` datetime(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.usuarios_perfilusuario
CREATE TABLE IF NOT EXISTS `usuarios_perfilusuario` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `direccion` longtext NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL,
  `fecha_creacion` datetime(6) NOT NULL,
  `fecha_modificacion` datetime(6) NOT NULL,
  `usuario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `usuarios_perfilusuario_usuario_id_70ec3749_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ventas_detalledevolucion
CREATE TABLE IF NOT EXISTS `ventas_detalledevolucion` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad_devuelta` int unsigned NOT NULL,
  `detalle_venta_id` bigint NOT NULL,
  `devolucion_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ventas_detalledevolu_detalle_venta_id_19323348_fk_ventas_de` (`detalle_venta_id`),
  KEY `ventas_detalledevolu_devolucion_id_4ad0921a_fk_ventas_de` (`devolucion_id`),
  CONSTRAINT `ventas_detalledevolu_detalle_venta_id_19323348_fk_ventas_de` FOREIGN KEY (`detalle_venta_id`) REFERENCES `ventas_detalleventa` (`id`),
  CONSTRAINT `ventas_detalledevolu_devolucion_id_4ad0921a_fk_ventas_de` FOREIGN KEY (`devolucion_id`) REFERENCES `ventas_devolucionventa` (`id`),
  CONSTRAINT `ventas_detalledevolucion_chk_1` CHECK ((`cantidad_devuelta` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ventas_detalleventa
CREATE TABLE IF NOT EXISTS `ventas_detalleventa` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `cantidad` int unsigned NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento_linea` decimal(10,2) NOT NULL,
  `producto_id` bigint NOT NULL,
  `venta_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_detalleventa_venta_id_producto_id_285803a9_uniq` (`venta_id`,`producto_id`),
  KEY `ventas_detalleventa_producto_id_a820c807_fk_productos` (`producto_id`),
  CONSTRAINT `ventas_detalleventa_producto_id_a820c807_fk_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos_producto` (`id`),
  CONSTRAINT `ventas_detalleventa_venta_id_c370bcd7_fk_ventas_venta_id` FOREIGN KEY (`venta_id`) REFERENCES `ventas_venta` (`id`),
  CONSTRAINT `ventas_detalleventa_chk_1` CHECK ((`cantidad` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ventas_devolucionventa
CREATE TABLE IF NOT EXISTS `ventas_devolucionventa` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_devolucion` varchar(50) NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `motivo` varchar(20) NOT NULL,
  `observaciones` longtext NOT NULL,
  `total_devolucion` decimal(10,2) NOT NULL,
  `usuario_id` int NOT NULL,
  `venta_original_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_devolucion` (`numero_devolucion`),
  KEY `ventas_devolucionven_venta_original_id_58dda425_fk_ventas_ve` (`venta_original_id`),
  KEY `ventas_devolucionventa_usuario_id_4464ab08_fk_auth_user_id` (`usuario_id`),
  CONSTRAINT `ventas_devolucionven_venta_original_id_58dda425_fk_ventas_ve` FOREIGN KEY (`venta_original_id`) REFERENCES `ventas_venta` (`id`),
  CONSTRAINT `ventas_devolucionventa_usuario_id_4464ab08_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ventas_pagoventa
CREATE TABLE IF NOT EXISTS `ventas_pagoventa` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `metodo_pago` varchar(20) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `usuario_id` int NOT NULL,
  `venta_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ventas_pagoventa_usuario_id_d3df9697_fk_auth_user_id` (`usuario_id`),
  KEY `ventas_pagoventa_venta_id_d59f2534_fk_ventas_venta_id` (`venta_id`),
  CONSTRAINT `ventas_pagoventa_usuario_id_d3df9697_fk_auth_user_id` FOREIGN KEY (`usuario_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `ventas_pagoventa_venta_id_d59f2534_fk_ventas_venta_id` FOREIGN KEY (`venta_id`) REFERENCES `ventas_venta` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla SistemaPosDB.ventas_venta
CREATE TABLE IF NOT EXISTS `ventas_venta` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_factura` varchar(50) NOT NULL,
  `fecha` datetime(6) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL,
  `impuesto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tipo_pago` varchar(20) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `observaciones` longtext NOT NULL,
  `apertura_caja_id` bigint NOT NULL,
  `cliente_id` bigint NOT NULL,
  `vendedor_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_factura` (`numero_factura`),
  KEY `ventas_venta_apertura_caja_id_cbd232a8_fk_caja_aperturacaja_id` (`apertura_caja_id`),
  KEY `ventas_venta_cliente_id_85f33a80_fk_clientes_cliente_id` (`cliente_id`),
  KEY `ventas_venta_vendedor_id_2f6b0d76_fk_auth_user_id` (`vendedor_id`),
  CONSTRAINT `ventas_venta_apertura_caja_id_cbd232a8_fk_caja_aperturacaja_id` FOREIGN KEY (`apertura_caja_id`) REFERENCES `caja_aperturacaja` (`id`),
  CONSTRAINT `ventas_venta_cliente_id_85f33a80_fk_clientes_cliente_id` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_cliente` (`id`),
  CONSTRAINT `ventas_venta_vendedor_id_2f6b0d76_fk_auth_user_id` FOREIGN KEY (`vendedor_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
