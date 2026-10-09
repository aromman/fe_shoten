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

INSERT INTO empleados (id, apellido, nombre) VALUES
  (1, 'Demo', 'Administrador'),
  (2, 'Demo', 'Vendedor')
ON DUPLICATE KEY UPDATE apellido = VALUES(apellido), nombre = VALUES(nombre);

INSERT INTO users (id, username, email, password, id_rol, id_empleado) VALUES
  (1, 'admin', 'admin@demo.shoten.com.ar', '$2y$10$YjEhCdNiI8ma6Jyn57A1H.bmGu0aT5W6efjtjRKunSVPOlgI1MoeK', 0, 1),
  (2, 'venta', 'venta@demo.shoten.com.ar', '$2y$10$QcxgubPzxyXdPZfDLxNs.u9l1NQJo7MXDNxYQ7DOnlJLcsUvCdPYO', 1, 2)
ON DUPLICATE KEY UPDATE username = VALUES(username), email = VALUES(email), password = VALUES(password),
  id_rol = VALUES(id_rol), id_empleado = VALUES(id_empleado);
