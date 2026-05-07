-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
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


-- Volcando estructura de base de datos para finsight_db
CREATE DATABASE IF NOT EXISTS `finsight_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `finsight_db`;

-- Volcando estructura para tabla finsight_db.alertas
CREATE TABLE IF NOT EXISTS `alertas` (
  `id_alerta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci,
  `prioridad` enum('Baja','Media','Alta') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `fecha_alerta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alerta`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.alertas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.auditoria_movimientos
CREATE TABLE IF NOT EXISTS `auditoria_movimientos` (
  `id_auditoria` int NOT NULL AUTO_INCREMENT,
  `id_movimiento` int DEFAULT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `fecha_accion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.auditoria_movimientos: ~4 rows (aproximadamente)
INSERT INTO `auditoria_movimientos` (`id_auditoria`, `id_movimiento`, `accion`, `usuario_id`, `fecha_accion`) VALUES
	(1, 1, 'INSERT', 1, '2026-05-07 05:07:00'),
	(2, 2, 'INSERT', 1, '2026-05-07 05:07:00'),
	(3, 3, 'INSERT', 1, '2026-05-07 05:07:00'),
	(4, 4, 'INSERT', 1, '2026-05-07 05:07:00');

-- Volcando estructura para tabla finsight_db.bitacora_accesos
CREATE TABLE IF NOT EXISTS `bitacora_accesos` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `navegador` text COLLATE utf8mb4_unicode_ci,
  `fecha_ingreso` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `bitacora_accesos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.bitacora_accesos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `nombre_categoria` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_movimiento` enum('Ingreso','Gasto') COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_hex` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.categorias: ~4 rows (aproximadamente)
INSERT INTO `categorias` (`id_categoria`, `id_usuario`, `nombre_categoria`, `tipo_movimiento`, `color_hex`) VALUES
	(1, NULL, 'Alimentación', 'Gasto', '#f46060'),
	(2, NULL, 'Transporte', 'Gasto', '#f4a24a'),
	(3, NULL, 'Sueldo', 'Ingreso', '#4af4c2'),
	(4, NULL, 'Educación', 'Gasto', '#4ab4f4');

-- Volcando estructura para tabla finsight_db.config_usuario
CREATE TABLE IF NOT EXISTS `config_usuario` (
  `id_config` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `tema_oscuro` tinyint(1) DEFAULT '1',
  `notificaciones_email` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_config`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `config_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.config_usuario: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.cuentas
CREATE TABLE IF NOT EXISTS `cuentas` (
  `id_cuenta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `nombre_cuenta` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_moneda` int DEFAULT NULL,
  `saldo_actual` decimal(15,2) DEFAULT '0.00',
  PRIMARY KEY (`id_cuenta`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_moneda` (`id_moneda`),
  CONSTRAINT `cuentas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `cuentas_ibfk_2` FOREIGN KEY (`id_moneda`) REFERENCES `monedas` (`id_moneda`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.cuentas: ~2 rows (aproximadamente)
INSERT INTO `cuentas` (`id_cuenta`, `id_usuario`, `nombre_cuenta`, `id_moneda`, `saldo_actual`) VALUES
	(1, 1, 'Efectivo Personal', 1, 1454.50),
	(2, 1, 'Banco Unión', 1, 5700.00);

-- Volcando estructura para tabla finsight_db.gastos_programados
CREATE TABLE IF NOT EXISTS `gastos_programados` (
  `id_gasto_fijo` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `nombre_servicio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_fijo` decimal(15,2) DEFAULT NULL,
  `dia_pago` int DEFAULT NULL,
  `estado` enum('Pendiente','Pagado','Vencido') COLLATE utf8mb4_unicode_ci DEFAULT 'Pendiente',
  PRIMARY KEY (`id_gasto_fijo`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `gastos_programados_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.gastos_programados: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.historial_saldos
CREATE TABLE IF NOT EXISTS `historial_saldos` (
  `id_historial` int NOT NULL AUTO_INCREMENT,
  `id_cuenta` int DEFAULT NULL,
  `saldo_dia` decimal(15,2) DEFAULT NULL,
  `fecha_corte` date DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `id_cuenta` (`id_cuenta`),
  CONSTRAINT `historial_saldos_ibfk_1` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.historial_saldos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.metas_de_ahorro
CREATE TABLE IF NOT EXISTS `metas_de_ahorro` (
  `id_meta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `nombre_meta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_objetivo` decimal(15,2) DEFAULT NULL,
  `monto_actual` decimal(15,2) DEFAULT '0.00',
  `fecha_limite` date DEFAULT NULL,
  PRIMARY KEY (`id_meta`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `metas_de_ahorro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.metas_de_ahorro: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.monedas
CREATE TABLE IF NOT EXISTS `monedas` (
  `id_moneda` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_moneda` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_cambio_bs` decimal(10,4) DEFAULT '1.0000',
  PRIMARY KEY (`id_moneda`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.monedas: ~2 rows (aproximadamente)
INSERT INTO `monedas` (`id_moneda`, `codigo`, `nombre_moneda`, `tipo_cambio_bs`) VALUES
	(1, 'BOB', 'Bolivianos', 1.0000),
	(2, 'USD', 'Dólares', 6.9600);

-- Volcando estructura para tabla finsight_db.movimientos_diarios
CREATE TABLE IF NOT EXISTS `movimientos_diarios` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `id_cuenta` int DEFAULT NULL,
  `id_categoria` int DEFAULT NULL,
  `monto_real` decimal(15,2) NOT NULL,
  `tipo_movimiento` enum('Ingreso','Gasto') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `es_ocasional` tinyint(1) DEFAULT '0',
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_cuenta` (`id_cuenta`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `movimientos_diarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `movimientos_diarios_ibfk_2` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`),
  CONSTRAINT `movimientos_diarios_ibfk_3` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.movimientos_diarios: ~0 rows (aproximadamente)
INSERT INTO `movimientos_diarios` (`id_movimiento`, `id_usuario`, `id_cuenta`, `id_categoria`, `monto_real`, `tipo_movimiento`, `notas`, `es_ocasional`, `fecha_hora`) VALUES
	(1, 1, 2, 3, 2500.00, 'Ingreso', 'Sueldo Mayo', 0, '2026-05-07 05:07:00'),
	(2, 1, 1, 1, 35.50, 'Gasto', 'Almuerzo Salteñas', 0, '2026-05-07 05:07:00'),
	(3, 1, 1, 2, 10.00, 'Gasto', 'Trufi El Alto', 0, '2026-05-07 05:07:00'),
	(4, 1, 2, 4, 1800.00, 'Gasto', 'Pensión Unifranz', 0, '2026-05-07 05:07:00');

-- Volcando estructura para tabla finsight_db.presupuestos
CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id_presupuesto` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `mes` int DEFAULT NULL,
  `anio` int DEFAULT NULL,
  `limite_maximo` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id_presupuesto`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.presupuestos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre_proveedor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rubro` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.proveedores: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.roles: ~3 rows (aproximadamente)
INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
	(1, 'Super Usuario', 'Control total del sistema y DB'),
	(2, 'Administrador', 'Gestión de categorías y reportes'),
	(3, 'Usuario Final', 'Gestión de finanzas personales');

-- Volcando estructura para tabla finsight_db.sugerencias_dss
CREATE TABLE IF NOT EXISTS `sugerencias_dss` (
  `id_sugerencia` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `recomendacion` text COLLATE utf8mb4_unicode_ci,
  `puntuacion_ahorro` int DEFAULT NULL,
  PRIMARY KEY (`id_sugerencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `sugerencias_dss_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.sugerencias_dss: ~0 rows (aproximadamente)

-- Volcando estructura para tabla finsight_db.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_rol` int DEFAULT '3',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla finsight_db.usuarios: ~4 rows (aproximadamente)
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `id_rol`, `fecha_registro`) VALUES
	(1, 'Brandon Tuco', 'tucobrandon@gmail.com', 'hash_secure_1', 1, '2026-05-07 05:07:00'),
	(2, 'Mel', 'mel@estudiante.bo', 'hash_secure_2', 3, '2026-05-07 05:07:00'),
	(3, 'Sebastian', 'sebastian@estudiante.bo', 'hash_secure_3', 3, '2026-05-07 05:07:00'),
	(4, 'Orlando', 'orlando@estudiante.bo', 'hash_secure_4', 3, '2026-05-07 05:07:00');

-- Volcando estructura para vista finsight_db.vista_accesos_recientes
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_accesos_recientes` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`ip_origen` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`fecha_ingreso` TIMESTAMP NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_alertas_pendientes
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_alertas_pendientes` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total_alertas` BIGINT NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_control_presupuesto
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_control_presupuesto` (
	`nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`limite_maximo` DECIMAL(15,2) NULL,
	`gasto_real` DECIMAL(37,2) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_dashboard_kpis
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_dashboard_kpis` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total_ingresos` DECIMAL(37,2) NULL,
	`total_gastos` DECIMAL(37,2) NULL,
	`saldo_actual` DECIMAL(15,2) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_gastos_hormiga
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_gastos_hormiga` (
	`id_movimiento` INT NOT NULL,
	`id_usuario` INT NULL,
	`id_cuenta` INT NULL,
	`id_categoria` INT NULL,
	`monto_real` DECIMAL(15,2) NOT NULL,
	`tipo_movimiento` ENUM('Ingreso','Gasto') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`notas` TEXT NULL COLLATE 'utf8mb4_unicode_ci',
	`es_ocasional` TINYINT(1) NULL,
	`fecha_hora` TIMESTAMP NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_gastos_vencidos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_gastos_vencidos` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nombre_servicio` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`monto_fijo` DECIMAL(15,2) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_log_auditoria
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_log_auditoria` (
	`fecha_accion` TIMESTAMP NULL,
	`accion` ENUM('INSERT','UPDATE','DELETE') NULL COLLATE 'utf8mb4_unicode_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`notas` TEXT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_movimientos_detallados
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_movimientos_detallados` (
	`fecha_hora` TIMESTAMP NULL,
	`notas` TEXT NULL COLLATE 'utf8mb4_unicode_ci',
	`monto_real` DECIMAL(15,2) NOT NULL,
	`tipo_movimiento` ENUM('Ingreso','Gasto') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nombre_categoria` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`usuario` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_progreso_metas
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_progreso_metas` (
	`nombre_meta` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`monto_objetivo` DECIMAL(15,2) NULL,
	`monto_actual` DECIMAL(15,2) NULL,
	`porcentaje_progreso` DECIMAL(24,6) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_resumen_monedas
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_resumen_monedas` (
	`nombre_moneda` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`total_cuentas` BIGINT NOT NULL,
	`acumulado` DECIMAL(37,2) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_saldos_dolares
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_saldos_dolares` (
	`nombre_cuenta` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`saldo_actual` DECIMAL(15,2) NULL,
	`saldo_usd` DECIMAL(21,6) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_sugerencias_criticas
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_sugerencias_criticas` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`recomendacion` TEXT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_top_gastos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_top_gastos` (
	`nombre_categoria` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`total` DECIMAL(37,2) NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_usuarios_inactivos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_usuarios_inactivos` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista finsight_db.vista_usuarios_roles
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_usuarios_roles` (
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nombre_rol` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para disparador finsight_db.tg_actualizar_saldo_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tg_actualizar_saldo_insert` AFTER INSERT ON `movimientos_diarios` FOR EACH ROW BEGIN
    IF NEW.tipo_movimiento = 'Gasto' THEN
        UPDATE cuentas SET saldo_actual = saldo_actual - NEW.monto_real WHERE id_cuenta = NEW.id_cuenta;
    ELSE
        UPDATE cuentas SET saldo_actual = saldo_actual + NEW.monto_real WHERE id_cuenta = NEW.id_cuenta;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador finsight_db.tg_auditoria_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tg_auditoria_insert` AFTER INSERT ON `movimientos_diarios` FOR EACH ROW BEGIN
    INSERT INTO auditoria_movimientos (id_movimiento, accion, usuario_id)
    VALUES (NEW.id_movimiento, 'INSERT', NEW.id_usuario);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_accesos_recientes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_accesos_recientes` AS select `u`.`nombre` AS `nombre`,`b`.`ip_origen` AS `ip_origen`,`b`.`fecha_ingreso` AS `fecha_ingreso` from (`bitacora_accesos` `b` join `usuarios` `u` on((`b`.`id_usuario` = `u`.`id_usuario`))) where (cast(`b`.`fecha_ingreso` as date) = curdate());

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_alertas_pendientes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_alertas_pendientes` AS select `u`.`nombre` AS `nombre`,count(`a`.`id_alerta`) AS `total_alertas` from (`alertas` `a` join `usuarios` `u` on((`a`.`id_usuario` = `u`.`id_usuario`))) where (`a`.`leido` = false) group by `u`.`nombre`;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_control_presupuesto`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_control_presupuesto` AS select `u`.`nombre` AS `nombre`,`p`.`limite_maximo` AS `limite_maximo`,(select sum(`movimientos_diarios`.`monto_real`) from `movimientos_diarios` where ((`movimientos_diarios`.`id_usuario` = `u`.`id_usuario`) and (`movimientos_diarios`.`tipo_movimiento` = 'Gasto'))) AS `gasto_real` from (`usuarios` `u` join `presupuestos` `p` on((`u`.`id_usuario` = `p`.`id_usuario`)));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_dashboard_kpis`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_dashboard_kpis` AS select `u`.`nombre` AS `nombre`,sum((case when (`m`.`tipo_movimiento` = 'Ingreso') then `m`.`monto_real` else 0 end)) AS `total_ingresos`,sum((case when (`m`.`tipo_movimiento` = 'Gasto') then `m`.`monto_real` else 0 end)) AS `total_gastos`,`c`.`saldo_actual` AS `saldo_actual` from ((`usuarios` `u` join `movimientos_diarios` `m` on((`u`.`id_usuario` = `m`.`id_usuario`))) join `cuentas` `c` on((`u`.`id_usuario` = `c`.`id_usuario`))) group by `u`.`id_usuario`;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_gastos_hormiga`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_gastos_hormiga` AS select `movimientos_diarios`.`id_movimiento` AS `id_movimiento`,`movimientos_diarios`.`id_usuario` AS `id_usuario`,`movimientos_diarios`.`id_cuenta` AS `id_cuenta`,`movimientos_diarios`.`id_categoria` AS `id_categoria`,`movimientos_diarios`.`monto_real` AS `monto_real`,`movimientos_diarios`.`tipo_movimiento` AS `tipo_movimiento`,`movimientos_diarios`.`notas` AS `notas`,`movimientos_diarios`.`es_ocasional` AS `es_ocasional`,`movimientos_diarios`.`fecha_hora` AS `fecha_hora` from `movimientos_diarios` where ((`movimientos_diarios`.`es_ocasional` = true) and (month(`movimientos_diarios`.`fecha_hora`) = month(curdate())));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_gastos_vencidos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_gastos_vencidos` AS select `u`.`nombre` AS `nombre`,`g`.`nombre_servicio` AS `nombre_servicio`,`g`.`monto_fijo` AS `monto_fijo` from (`gastos_programados` `g` join `usuarios` `u` on((`g`.`id_usuario` = `u`.`id_usuario`))) where (`g`.`estado` = 'Vencido');

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_log_auditoria`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_log_auditoria` AS select `au`.`fecha_accion` AS `fecha_accion`,`au`.`accion` AS `accion`,`u`.`nombre` AS `nombre`,`m`.`notas` AS `notas` from ((`auditoria_movimientos` `au` join `usuarios` `u` on((`au`.`usuario_id` = `u`.`id_usuario`))) join `movimientos_diarios` `m` on((`au`.`id_movimiento` = `m`.`id_movimiento`)));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_movimientos_detallados`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_movimientos_detallados` AS select `m`.`fecha_hora` AS `fecha_hora`,`m`.`notas` AS `notas`,`m`.`monto_real` AS `monto_real`,`m`.`tipo_movimiento` AS `tipo_movimiento`,`cat`.`nombre_categoria` AS `nombre_categoria`,`u`.`nombre` AS `usuario` from ((`movimientos_diarios` `m` join `categorias` `cat` on((`m`.`id_categoria` = `cat`.`id_categoria`))) join `usuarios` `u` on((`m`.`id_usuario` = `u`.`id_usuario`)));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_progreso_metas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_progreso_metas` AS select `metas_de_ahorro`.`nombre_meta` AS `nombre_meta`,`metas_de_ahorro`.`monto_objetivo` AS `monto_objetivo`,`metas_de_ahorro`.`monto_actual` AS `monto_actual`,((`metas_de_ahorro`.`monto_actual` / `metas_de_ahorro`.`monto_objetivo`) * 100) AS `porcentaje_progreso` from `metas_de_ahorro`;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_resumen_monedas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_resumen_monedas` AS select `mo`.`nombre_moneda` AS `nombre_moneda`,count(`cu`.`id_cuenta`) AS `total_cuentas`,sum(`cu`.`saldo_actual`) AS `acumulado` from (`cuentas` `cu` join `monedas` `mo` on((`cu`.`id_moneda` = `mo`.`id_moneda`))) group by `mo`.`nombre_moneda`;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_saldos_dolares`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_saldos_dolares` AS select `cuentas`.`nombre_cuenta` AS `nombre_cuenta`,`cuentas`.`saldo_actual` AS `saldo_actual`,(`cuentas`.`saldo_actual` / 6.96) AS `saldo_usd` from `cuentas`;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_sugerencias_criticas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_sugerencias_criticas` AS select `u`.`nombre` AS `nombre`,`s`.`recomendacion` AS `recomendacion` from (`sugerencias_dss` `s` join `usuarios` `u` on((`s`.`id_usuario` = `u`.`id_usuario`))) where (`s`.`puntuacion_ahorro` < 50);

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_top_gastos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_top_gastos` AS select `c`.`nombre_categoria` AS `nombre_categoria`,sum(`m`.`monto_real`) AS `total` from (`movimientos_diarios` `m` join `categorias` `c` on((`m`.`id_categoria` = `c`.`id_categoria`))) where (`m`.`tipo_movimiento` = 'Gasto') group by `c`.`nombre_categoria` order by `total` desc;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_usuarios_inactivos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_usuarios_inactivos` AS select `usuarios`.`nombre` AS `nombre` from `usuarios` where `usuarios`.`id_usuario` in (select distinct `movimientos_diarios`.`id_usuario` from `movimientos_diarios`) is false;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_usuarios_roles`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_usuarios_roles` AS select `u`.`nombre` AS `nombre`,`u`.`email` AS `email`,`r`.`nombre_rol` AS `nombre_rol` from (`usuarios` `u` join `roles` `r` on((`u`.`id_rol` = `r`.`id_rol`)));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
