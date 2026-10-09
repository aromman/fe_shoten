-- Datos mínimos para entrar a la base de demostración (shoten_demo).
-- Se puede ejecutar más de una vez: actualiza los registros si ya existen.
--
-- Usuarios (los mismos que muestra login.php):
--   admin / admin1234  -> rol 0 Administrador
--   venta / venta1234  -> rol 1 Vendedor

SET NAMES utf8mb4;

-- Permite insertar el rol con id 0.
SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO');

INSERT INTO roles (id, rol) VALUES
  (0, 'Administrador'),
  (1, 'Vendedor'),
  (2, 'Contador'),
  (3, 'Marketing'),
  (99, 'Invitado')
ON DUPLICATE KEY UPDATE rol = VALUES(rol);

-- El login solo lista canales activos, que venden y con fechaInicio <= hoy.
INSERT INTO canal (id, nombre, tipo, fechaInicio, activo, vende) VALUES
  (1, 'Librería Demo', 'FISICO', '2026-01-01', 1, 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), tipo = VALUES(tipo), fechaInicio = VALUES(fechaInicio),
  activo = VALUES(activo), vende = VALUES(vende);

-- Cuentas. Saldo actual = saldo_inicial + movimientos (tipo_saldo: E efectivo, B banco/billetera).
INSERT INTO cuentas (id, nombre, fecha_consolidado, saldo_inicial, saldo_consolidado, tipo, tipo_saldo, ambito_uso, activa) VALUES
  (1, 'Efectivo',     '2026-01-01',  150000, 150000, 'A', 'E', 'COMERCIAL', 1),
  (2, 'Mercado Pago', '2026-01-01',  480000, 480000, 'A', 'B', 'COMERCIAL', 1),
  (3, 'Banco Demo',   '2026-01-01', 1250000, 1250000, 'A', 'B', 'COMERCIAL', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), fecha_consolidado = VALUES(fecha_consolidado),
  saldo_inicial = VALUES(saldo_inicial), saldo_consolidado = VALUES(saldo_consolidado), tipo = VALUES(tipo),
  tipo_saldo = VALUES(tipo_saldo), ambito_uso = VALUES(ambito_uso), activa = VALUES(activa);

INSERT INTO medio_pago (id, nombre, cargo_porcentaje, id_cuenta, id_canal) VALUES
  (1, 'Efectivo',       0, 1, 1),
  (2, 'Mercado Pago',   0, 2, 1),
  (3, 'Transferencias', 0, 3, 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), cargo_porcentaje = VALUES(cargo_porcentaje),
  id_cuenta = VALUES(id_cuenta), id_canal = VALUES(id_canal);

INSERT INTO descuentos (id, tipo, nombre, valor, porcentaje) VALUES
  (1, 'F', 'Sin Descuento', 0, 0)
ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), nombre = VALUES(nombre), valor = VALUES(valor), porcentaje = VALUES(porcentaje);

-- Cliente genérico: el sistema usa el id 1 fijo (POS, baja de clientes, cuentas).
INSERT INTO clientes (id, apellido, nombre, dni, email, id_descuento, solo_contacto) VALUES
  (1, 'Generico', 'Sin Descuento', 0, '', 1, 0)
ON DUPLICATE KEY UPDATE apellido = VALUES(apellido), nombre = VALUES(nombre), dni = VALUES(dni), email = VALUES(email),
  id_descuento = VALUES(id_descuento), solo_contacto = VALUES(solo_contacto);

INSERT INTO empleados (id, apellido, nombre) VALUES
  (1, 'Demo', 'Administrador'),
  (2, 'Demo', 'Vendedor')
ON DUPLICATE KEY UPDATE apellido = VALUES(apellido), nombre = VALUES(nombre);

INSERT INTO users (id, username, email, password, id_rol, id_empleado) VALUES
  (1, 'admin', 'admin@demo.shoten.com.ar', '$2y$10$YjEhCdNiI8ma6Jyn57A1H.bmGu0aT5W6efjtjRKunSVPOlgI1MoeK', 0, 1),
  (2, 'venta', 'venta@demo.shoten.com.ar', '$2y$10$QcxgubPzxyXdPZfDLxNs.u9l1NQJo7MXDNxYQ7DOnlJLcsUvCdPYO', 1, 2)
ON DUPLICATE KEY UPDATE username = VALUES(username), email = VALUES(email), password = VALUES(password),
  id_rol = VALUES(id_rol), id_empleado = VALUES(id_empleado);
