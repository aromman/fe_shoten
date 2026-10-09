ALTER TABLE `cuentas_movimientos`
  ADD COLUMN `origen_tipo` varchar(30) NULL AFTER `id_causal`,
  ADD COLUMN `origen_id` int(11) NULL AFTER `origen_tipo`;

