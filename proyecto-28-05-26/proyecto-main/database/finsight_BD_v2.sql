-- ========================================================
-- ESPECIFICACIÓN TÉCNICA: FINSIGHT v2.0 (14 TABLAS) - BLINDADO
-- CON TRIGGERS DE AUDITORÍA Y COMPATIBILIDAD ABSOLUTA
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Limpieza de estructura previa
DROP TABLE IF EXISTS `bitacora_accesos`;
DROP TABLE IF EXISTS `auditoria_movimientos`;
DROP TABLE IF EXISTS `alertas`;
DROP TABLE IF EXISTS `presupuestos_mensuales`;
DROP TABLE IF EXISTS `gastos_programados`;
DROP TABLE IF EXISTS `ingresos_programados`;
DROP TABLE IF EXISTS `movimientos_diarios`;
DROP TABLE IF EXISTS `metas_de_ahorro`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `cuentas`;
DROP TABLE IF EXISTS `monedas`;
DROP TABLE IF EXISTS `proveedores`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `roles`;

-- 1. ROLES
CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB;

-- 2. USUARIOS
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `id_rol` int DEFAULT '3',
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB;

-- 3. MONEDAS
CREATE TABLE `monedas` (
  `id_moneda` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(5) NOT NULL,
  `nombre_moneda` varchar(50) NOT NULL,
  `tipo_cambio_bs` decimal(10,4) DEFAULT '1.0000',
  PRIMARY KEY (`id_moneda`)
) ENGINE=InnoDB;

-- 4. CUENTAS
CREATE TABLE `cuentas` (
  `id_cuenta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_cuenta` varchar(100) NOT NULL,
  `id_moneda` int NOT NULL,
  `saldo_actual` decimal(15,2) DEFAULT '0.00',
  PRIMARY KEY (`id_cuenta`),
  CONSTRAINT `fk_cuentas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_cuentas_monedas` FOREIGN KEY (`id_moneda`) REFERENCES `monedas` (`id_moneda`)
) ENGINE=InnoDB;

-- 5. CATEGORIAS
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `tipo_categoria` enum('Ingreso','Gasto') NOT NULL,
  `color_hex` varchar(7) DEFAULT '#c8f064',
  PRIMARY KEY (`id_categoria`),
  CONSTRAINT `fk_categorias_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. METAS DE AHORRO
CREATE TABLE `metas_de_ahorro` (
  `id_meta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_meta` varchar(100) NOT NULL,
  `monto_objetivo` decimal(15,2) NOT NULL,
  `monto_actual` decimal(15,2) DEFAULT '0.00',
  `fecha_limite` date DEFAULT NULL,
  `porcentaje_ingreso` decimal(5,2) DEFAULT '0.00',
  `ahorro_automatico` tinyint(1) DEFAULT '0',
  `porcentaje_ahorro` decimal(5,2) DEFAULT '0.00',
  `estado` enum('Activo','Completado','Cancelado') DEFAULT 'Activo',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_meta`),
  CONSTRAINT `fk_metas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. MOVIMIENTOS DIARIOS
CREATE TABLE `movimientos_diarios` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_cuenta` int NOT NULL,
  `tipo_movimiento` enum('Ingreso','Gasto') NOT NULL,
  `monto_real` decimal(15,2) NOT NULL,
  `id_categoria` int DEFAULT NULL,
  `es_ocasional` tinyint(1) DEFAULT '0',
  `notas` text,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  CONSTRAINT `fk_movimientos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_movimientos_cuentas` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id_cuenta`) ON DELETE CASCADE,
  CONSTRAINT `fk_movimientos_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 8. INGRESOS PROGRAMADOS
CREATE TABLE `ingresos_programados` (
  `id_ingreso_programado` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `monto` decimal(15,2) DEFAULT NULL,
  `intervalo` enum('Semanal','Quincenal','Mensual','Personalizado') DEFAULT 'Mensual',
  `fecha_inicio` date DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id_ingreso_programado`),
  CONSTRAINT `fk_ingresos_prog_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. GASTOS PROGRAMADOS (Fijos)
CREATE TABLE `gastos_programados` (
  `id_gasto_fijo` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_servicio` varchar(100) NOT NULL,
  `monto_fijo` decimal(15,2) NOT NULL,
  `dia_pago` int NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id_gasto_fijo`),
  CONSTRAINT `fk_gastos_prog_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. PRESUPUESTOS MENSUALES
CREATE TABLE `presupuestos_mensuales` (
  `id_presupuesto` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `mes` int NOT NULL,
  `anio` int NOT NULL,
  `monto_limite` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id_presupuesto`),
  CONSTRAINT `fk_presupuestos_mensuales_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. ALERTAS
CREATE TABLE `alertas` (
  `id_alerta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `mensaje` text NOT NULL,
  `prioridad` enum('Baja','Media','Alta') DEFAULT 'Baja',
  `leido` tinyint(1) DEFAULT '0',
  `fecha_alerta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alerta`),
  CONSTRAINT `fk_alertas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. AUDITORIA DE MOVIMIENTOS
CREATE TABLE `auditoria_movimientos` (
  `id_auditoria` int NOT NULL AUTO_INCREMENT,
  `id_movimiento` int NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha_accion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`)
) ENGINE=InnoDB;

-- 13. BITACORA DE ACCESOS
CREATE TABLE `bitacora_accesos` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `navegador` text,
  `fecha_ingreso` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  CONSTRAINT `fk_bitacora_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 14. PROVEEDORES
CREATE TABLE `proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre_proveedor` varchar(100) NOT NULL,
  `rubro` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB;

-- ========================================================
-- LÓGICA DEL LADO DEL SERVIDOR: TRIGGERS
-- ========================================================

DELIMITER //

-- TRIGGER 1: AUDITORÍA AUTOMÁTICA AL INSERTAR MOVIMIENTO
CREATE TRIGGER `tr_auditoria_insert` AFTER INSERT ON `movimientos_diarios`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria_movimientos` (id_movimiento, accion, usuario_id)
    VALUES (NEW.id_movimiento, 'INSERT', NEW.id_usuario);
END //

-- TRIGGER 2: ACTUALIZACIÓN DE SALDO EN CUENTA
CREATE TRIGGER `tr_actualizar_saldo_insert` AFTER INSERT ON `movimientos_diarios`
FOR EACH ROW
BEGIN
    IF NEW.tipo_movimiento = 'Ingreso' THEN
        UPDATE `cuentas` SET saldo_actual = saldo_actual + NEW.monto_real 
        WHERE id_cuenta = NEW.id_cuenta;
    ELSE
        UPDATE `cuentas` SET saldo_actual = saldo_actual - NEW.monto_real 
        WHERE id_cuenta = NEW.id_cuenta;
    END IF;
END //

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- SEMILLAS (DATA INICIAL)
-- ========================================================

INSERT INTO `roles` (`nombre_rol`, `descripcion`) VALUES 
('Super Admin', 'Control total del sistema'),
('Administrador', 'Gestión de usuarios y reportes'),
('Usuario Estándar', 'Gestión de finanzas personales');

INSERT INTO `monedas` (`codigo`, `nombre_moneda`, `tipo_cambio_bs`) VALUES 
('BOB', 'Boliviano', 1.0000),
('USD', 'Dólar Estadounidense', 6.9600);

INSERT INTO `categorias` (`nombre_categoria`, `tipo_categoria`, `color_hex`) VALUES 
('Ahorro', 'Gasto', '#4af4c2'),
('Sueldo', 'Ingreso', '#c8f064'),
('Alimentación', 'Gasto', '#f46060'),
('Ventas', 'Ingreso', '#4af4c2');

INSERT INTO `proveedores` (`nombre_proveedor`, `rubro`) VALUES 
('Entel', 'Telecomunicaciones'),
('Saguapac', 'Agua'),
('CRE', 'Electricidad'),
('Tigo', 'Internet');