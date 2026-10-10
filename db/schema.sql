-- Esquema completo de Shoten (fe_shoten): creación de todas las tablas.
-- Generado desde la base local shoten_demo (MariaDB 10.4) el 2026-10-10,
-- con las migraciones de db/migrations ya aplicadas.
--
-- Es idempotente: usa CREATE TABLE IF NOT EXISTS, así que se puede correr
-- sobre una base vacía o sobre una existente sin tocar tablas ni datos.
-- Ojo: no modifica tablas que ya existan; los cambios de estructura van
-- en db/migrations.
--
-- Orden sugerido para una base nueva: schema.sql -> seed_demo.sql

SET NAMES utf8;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE IF NOT EXISTS `aportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `jub` bigint(20) NOT NULL,
  `os` bigint(20) NOT NULL,
  `ley` bigint(20) NOT NULL,
  `fondo` bigint(20) NOT NULL,
  `seguro` bigint(20) NOT NULL,
  `art` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_id` (`empleado_id`),
  CONSTRAINT `aportes_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `billetera_turnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cuenta` int(11) NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `monto_apertura` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_esperado` decimal(12,2) DEFAULT NULL,
  `monto_cierre` decimal(12,2) DEFAULT NULL,
  `diferencia` decimal(12,2) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `usuario_apertura` varchar(100) DEFAULT NULL,
  `usuario_cierre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_billetera_turnos_cuenta` (`id_cuenta`),
  CONSTRAINT `fk_billetera_turnos_cuenta` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_billetera_turnos_estado` CHECK (`estado` in ('A','C'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `canal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `fechaInicio` varchar(50) DEFAULT NULL,
  `fechaFin` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `vende` tinyint(1) NOT NULL DEFAULT 1,
  `punto_venta` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apellido` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `dni` int(8) NOT NULL,
  `email` varchar(50) NOT NULL,
  `id_descuento` int(11) DEFAULT NULL,
  `solo_contacto` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `comandas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta_header` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `updateDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_venta` (`id_venta_header`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `comisiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `detalle` varchar(500) NOT NULL,
  `monto` double NOT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `id_liquidacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_lista` double NOT NULL,
  `precio_costo` double NOT NULL,
  `id_estado` int(11) DEFAULT 1,
  `id_orden_compra` int(11) DEFAULT NULL,
  `id_medio_pago` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `consignaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_estado` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `costos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `detalle` varchar(500) NOT NULL,
  `unidades` int(11) NOT NULL,
  `precio_unitario` double NOT NULL,
  `monto` double NOT NULL,
  `id_producto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cuentas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_consolidado` date NOT NULL,
  `saldo_inicial` double NOT NULL,
  `saldo_consolidado` double NOT NULL,
  `tipo` varchar(1) NOT NULL,
  `tipo_saldo` varchar(1) NOT NULL,
  `ambito_uso` varchar(20) NOT NULL DEFAULT 'COMERCIAL',
  `id_empleado` int(11) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_cuentas_id_empleado` (`id_empleado`),
  CONSTRAINT `fk_cuentas_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cuentas_causales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(5) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cuentas_movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cuenta` int(11) NOT NULL,
  `id_canal` int(11) NOT NULL DEFAULT 1,
  `fecha` datetime NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `tipo_movimiento` varchar(1) NOT NULL,
  `monto` double NOT NULL,
  `consolidado` tinyint(1) NOT NULL DEFAULT 0,
  `id_causal` int(11) NOT NULL,
  `origen_tipo` varchar(30) DEFAULT NULL,
  `origen_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cuentas_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` int(2) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `descuentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(1) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `valor` double NOT NULL,
  `porcentaje` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `editoriales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) NOT NULL,
  `porcentaje` bigint(20) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `consignacion` tinyint(1) NOT NULL DEFAULT 0,
  `monto_fijo` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apellido` varchar(500) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipo_empleado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_equipo` (`id_equipo`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `equipo_empleado_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`),
  CONSTRAINT `equipo_empleado_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `equipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipo` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `estado_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estado` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fudo_ventas_cache` (
  `tipo` varchar(4) NOT NULL,
  `periodo` varchar(10) NOT NULL,
  `total` double NOT NULL DEFAULT 0,
  `costo` double DEFAULT NULL,
  `gastos` double DEFAULT NULL,
  `cantidad_ventas` int(11) NOT NULL DEFAULT 0,
  `personas` int(11) NOT NULL DEFAULT 0,
  `cerrado` tinyint(1) NOT NULL DEFAULT 0,
  `actualizado_en` datetime NOT NULL,
  PRIMARY KEY (`tipo`,`periodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `detalle` varchar(500) NOT NULL,
  `monto` double NOT NULL,
  `tipo` varchar(1) NOT NULL,
  `id_liquidacion` int(11) DEFAULT NULL,
  `id_clase` int(11) NOT NULL DEFAULT 1,
  `id_medio_pago` int(11) NOT NULL DEFAULT 1,
  `id_canal` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `id_liquidacion` (`id_liquidacion`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`id_liquidacion`) REFERENCES `liquidacion` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gastos_clase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) NOT NULL,
  `presupuesto` bigint(20) NOT NULL DEFAULT 0,
  `prioridad` int(2) NOT NULL DEFAULT 1,
  `dia_pago` int(2) NOT NULL DEFAULT 30,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_canal` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `id_estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_canal` (`id_canal`),
  CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_canal`) REFERENCES `canal` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `inventario_costo_mantenimiento_config` (
  `id` tinyint(1) NOT NULL,
  `tasa_anual` double NOT NULL DEFAULT 0,
  `tasa_interes_anual` double NOT NULL DEFAULT 0,
  `actualizado_en` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `inversiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `detalle` varchar(100) NOT NULL,
  `monto` double NOT NULL,
  `libros` double NOT NULL,
  `cotizacion` double NOT NULL,
  `id_invertido_por` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `inversores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `id_cuenta` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `liquidacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `total_caja` double NOT NULL,
  `total_mercado` double NOT NULL,
  `total_dni` double NOT NULL,
  `total_stock` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `medio_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `cargo_porcentaje` int(11) NOT NULL,
  `id_cuenta` int(11) NOT NULL DEFAULT 1,
  `id_canal` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `objetivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prioridad` int(2) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `objetivo` double NOT NULL,
  `logrado` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `orden_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `monto` double DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `id_estado_pedido` int(11) NOT NULL,
  `id_medio_pago` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `id_estado_pedido` (`id_estado_pedido`),
  KEY `id_proveedor` (`id_proveedor`),
  CONSTRAINT `orden_compra_ibfk_2` FOREIGN KEY (`id_estado_pedido`) REFERENCES `estado_pedido` (`id`),
  CONSTRAINT `orden_compra_ibfk_3` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `cliente` varchar(50) NOT NULL,
  `contacto` varchar(50) NOT NULL,
  `producto` varchar(50) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `observacion` varchar(50) DEFAULT NULL,
  `fecha_actualizacion` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `presupuesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_canal` int(11) NOT NULL,
  `id_clase_gasto` int(11) NOT NULL,
  `monto` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `id_editorial` int(11) NOT NULL,
  `stock` bigint(20) NOT NULL,
  `precio` double NOT NULL,
  `validado` tinyint(1) NOT NULL DEFAULT 0,
  `id_serie` int(11) NOT NULL DEFAULT 1,
  `tomo` int(11) NOT NULL DEFAULT 0,
  `id_formato` int(11) NOT NULL DEFAULT 1,
  `admite_descuento` tinyint(1) NOT NULL DEFAULT 1,
  `nuevo` tinyint(1) NOT NULL DEFAULT 1,
  `precio_costo` double NOT NULL,
  `precio_oferta` double NOT NULL,
  `updateDate` datetime DEFAULT current_timestamp(),
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos_formato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) NOT NULL,
  `monto_objetivo` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos_serie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(500) NOT NULL,
  `cupo_maximo` int(11) NOT NULL DEFAULT 0,
  `cupo_minimo` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_editorial` int(11) NOT NULL,
  `cantidad` bigint(20) NOT NULL,
  `id_ubicacion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `costo` double NOT NULL,
  `nuevo` tinyint(1) NOT NULL,
  `validado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos_ubicacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_canal` int(11) NOT NULL,
  `id_nivel_tipo_01` int(11) NOT NULL,
  `nivel_codigo_01` int(11) NOT NULL,
  `id_nivel_tipo_02` int(11) NOT NULL,
  `nivel_codigo_02` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `productos_ubicacion_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(5) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `proveedor_calendario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) NOT NULL,
  `cron_pedido` varchar(100) DEFAULT NULL,
  `cron_entrega` varchar(100) DEFAULT NULL,
  `cantidad_minima` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_minimo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `vigente_desde` date DEFAULT NULL,
  `vigente_hasta` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_proveedor_calendario_proveedor` (`proveedor_id`),
  CONSTRAINT `fk_proveedor_calendario_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origen` varchar(20) NOT NULL DEFAULT 'local',
  `origen_id` varchar(100) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fiscal_number` varchar(100) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `cliente` varchar(500) NOT NULL,
  `clave` varchar(500) NOT NULL,
  `horas` int(11) NOT NULL,
  `mesas` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `monto` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_salario_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sueldos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `empleado_id` bigint(20) NOT NULL,
  `bruto` bigint(20) NOT NULL,
  `jubilacion` bigint(20) NOT NULL,
  `os` bigint(20) NOT NULL,
  `ley` bigint(20) NOT NULL,
  `neto` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `turnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_canal` int(11) NOT NULL DEFAULT 1,
  `monto_apertura` double NOT NULL,
  `monto_cierre` double NOT NULL,
  `estado` varchar(1) NOT NULL,
  `username` varchar(100) NOT NULL,
  `updateDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `turnos_billetes` (
  `turno_id` int(11) NOT NULL,
  `denominacion` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`turno_id`,`denominacion`),
  CONSTRAINT `fk_turnos_billetes_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `turnos_comandos` (
  `idempotency_key` varchar(64) NOT NULL,
  `tipo` varchar(32) NOT NULL,
  `canal_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `estado` varchar(16) NOT NULL,
  `respuesta_json` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`idempotency_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `turnos_parcial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_canal` int(11) NOT NULL DEFAULT 43,
  `monto_apertura` double NOT NULL,
  `monto_cierre` double NOT NULL,
  `username` varchar(100) NOT NULL,
  `updateDate` datetime NOT NULL,
  `monto10` double DEFAULT NULL,
  `monto20` double DEFAULT NULL,
  `monto50` double DEFAULT NULL,
  `monto100` double DEFAULT NULL,
  `monto200` double DEFAULT NULL,
  `monto500` double DEFAULT NULL,
  `monto1000` double DEFAULT NULL,
  `monto2000` double DEFAULT NULL,
  `monto10000` double DEFAULT NULL,
  `monto20000` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ventas_header` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `total` double NOT NULL,
  `id_canal` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `unidades` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `id_liquidacion` int(11) DEFAULT NULL,
  `id_medio_pago` int(11) NOT NULL,
  `descuento` double DEFAULT NULL,
  `motivo_descuento` varchar(100) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `precio_unitario` double NOT NULL,
  `id_descuento` int(11) DEFAULT NULL,
  `costo` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_equipo` (`id_equipo`),
  KEY `id_liquidacion` (`id_liquidacion`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_liquidacion`) REFERENCES `liquidacion` (`id`),
  CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ventas_header` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_canal` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_medio_pago` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subTotal` double NOT NULL,
  `descuento` double NOT NULL,
  `total` double NOT NULL,
  `sorteo` bit(1) NOT NULL DEFAULT b'0',
  `comprobante` varchar(30) NOT NULL,
  `cae` varchar(14) NOT NULL,
  `updateDate` datetime DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET SQL_MODE = @OLD_SQL_MODE;
