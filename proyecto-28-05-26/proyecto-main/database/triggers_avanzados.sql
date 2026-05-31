-- 10. PRESUPUESTOS MENSUALES
CREATE TABLE `presupuestos_mensuales` (
  `id_presupuesto` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `mes` int NOT NULL,
  `anio` int NOT NULL,
  `monto_limite` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id_presupuesto`),
  UNIQUE KEY `idx_user_period` (`id_usuario`, `mes`, `anio`),
  CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ... (rest of tables)

-- ========================================================
-- LÓGICA DEL LADO DEL SERVIDOR: TRIGGERS AVANZADOS
-- ========================================================

DELIMITER //

-- TRIGGER 1: AUDITORÍA Y ALERTAS DE PRESUPUESTO / SALDO
DROP TRIGGER IF EXISTS `tr_auditoria_insert` //
DROP TRIGGER IF EXISTS `tr_actualizar_saldo_insert` //

CREATE TRIGGER `tr_after_insert_movimiento` AFTER INSERT ON `movimientos_diarios`
FOR EACH ROW
BEGIN
    DECLARE total_gastado DECIMAL(15,2);
    DECLARE limite_presupuesto DECIMAL(15,2);
    DECLARE saldo_restante DECIMAL(15,2);
    DECLARE nombre_cta VARCHAR(100);

    -- 1. Auditoría
    INSERT INTO `auditoria_movimientos` (id_movimiento, accion, usuario_id)
    VALUES (NEW.id_movimiento, 'INSERT', NEW.id_usuario);

    -- 2. ALERTA: Saldo Mínimo Crítico (< 100 Bs)
    SELECT saldo_actual, nombre_cuenta INTO saldo_restante, nombre_cta FROM `cuentas` WHERE id_cuenta = NEW.id_cuenta;
    IF saldo_restante < 100 THEN
        INSERT INTO `alertas` (id_usuario, mensaje, prioridad)
        VALUES (NEW.id_usuario, CONCAT('¡Saldo Crítico! En ', nombre_cta, ' te quedan menos de 100 Bs.'), 'Alta');
    END IF;

    -- 3. ALERTA: Presupuesto Excedido
    IF NEW.tipo_movimiento = 'Gasto' THEN
        SELECT monto_limite INTO limite_presupuesto FROM `presupuestos_mensuales` 
        WHERE id_usuario = NEW.id_usuario AND mes = MONTH(NEW.fecha_hora) AND anio = YEAR(NEW.fecha_hora);

        SELECT SUM(monto_real) INTO total_gastado FROM `movimientos_diarios`
        WHERE id_usuario = NEW.id_usuario AND tipo_movimiento = 'Gasto' 
        AND MONTH(fecha_hora) = MONTH(NEW.fecha_hora) AND YEAR(fecha_hora) = YEAR(NEW.fecha_hora);

        IF limite_presupuesto IS NOT NULL AND total_gastado > limite_presupuesto THEN
            INSERT INTO `alertas` (id_usuario, mensaje, prioridad)
            VALUES (NEW.id_usuario, CONCAT('¡Atención! Has superado tu presupuesto mensual por Bs. ', FORMAT(total_gastado - limite_presupuesto, 2)), 'Alta');
        END IF;
    END IF;
END //

-- TRIGGER 2: ALERTA META LOGRADA
CREATE TRIGGER `tr_after_update_meta` AFTER UPDATE ON `metas_de_ahorro`
FOR EACH ROW
BEGIN
    IF OLD.monto_actual < OLD.monto_objetivo AND NEW.monto_actual >= NEW.monto_objetivo THEN
        INSERT INTO `alertas` (id_usuario, mensaje, prioridad)
        VALUES (NEW.id_usuario, CONCAT('🎯 ¡Meta Alcanzada! Felicidades, has completado tu objetivo: ', NEW.nombre_meta), 'Baja');
    END IF;
END //

DELIMITER ;
