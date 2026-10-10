-- Datos mínimos que el sistema necesita en cualquier base, incluida producción.
-- Lo corre db/migrate.php cada vez que cambia este archivo: INSERT IGNORE solo
-- crea lo que falta y nunca pisa lo que se haya editado en la base.
--
-- No crea usuarios, canales ni cuentas: eso es propio de cada librería y
-- para la demo lo carga seed_demo.sql.

SET NAMES utf8;

-- Permite insertar el rol con id 0.
SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO');

INSERT IGNORE INTO roles (id, rol) VALUES
  (0, 'Administrador'),
  (1, 'Vendedor'),
  (2, 'Contador'),
  (3, 'Marketing'),
  (99, 'Invitado');

INSERT IGNORE INTO descuentos (id, tipo, nombre, valor, porcentaje) VALUES
  (1, 'F', 'Sin Descuento', 0, 0);

-- Cliente genérico: el sistema usa el id 1 fijo (POS, baja de clientes, cuentas).
INSERT IGNORE INTO clientes (id, apellido, nombre, dni, email, id_descuento, solo_contacto) VALUES
  (1, 'Generico', 'Sin Descuento', 0, '', 1, 0);
