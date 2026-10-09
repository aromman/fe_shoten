ALTER TABLE `cuentas`
  ADD COLUMN `ambito_uso` varchar(20) NOT NULL DEFAULT 'COMERCIAL' AFTER `tipo_saldo`,
  ADD COLUMN `id_empleado` int(11) NULL AFTER `ambito_uso`,
  ADD COLUMN `activa` tinyint(1) NOT NULL DEFAULT 1 AFTER `id_empleado`;

ALTER TABLE `cuentas`
  ADD CONSTRAINT `chk_cuentas_ambito_uso`
    CHECK (`ambito_uso` IN ('COMERCIAL', 'PERSONAL', 'EMPLEADO'));

ALTER TABLE `cuentas`
  ADD KEY `idx_cuentas_id_empleado` (`id_empleado`);

ALTER TABLE `cuentas`
  ADD CONSTRAINT `fk_cuentas_empleado`
    FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;
