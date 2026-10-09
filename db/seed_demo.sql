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

-- ---------------------------------------------------------------------------
-- Catálogo de demostración
-- Títulos tomados de los catálogos públicos de Ivrea (ivrea.com.ar/catalogo)
-- y Editorial Planeta (planetadelibros.com). Precios, costos y stock son
-- valores de demo. Ivrea no publica ISBN: sus SKU son internos (IVREA-0001...).
-- ---------------------------------------------------------------------------

INSERT INTO proveedores (id, origen, nombre, activo) VALUES
  (1, 'local', 'Ivrea', 1),
  (2, 'local', 'Grupo Planeta', 1)
ON DUPLICATE KEY UPDATE origen = VALUES(origen), nombre = VALUES(nombre), activo = VALUES(activo);

-- porcentaje = margen de la librería sobre el precio de lista (costo = precio * (100 - porcentaje) / 100).
INSERT INTO editoriales (id, nombre, porcentaje, id_proveedor, consignacion, monto_fijo) VALUES
  (1, 'Ivrea',   40, 1, 0, NULL),
  (2, 'Planeta', 35, 2, 0, NULL)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), porcentaje = VALUES(porcentaje), id_proveedor = VALUES(id_proveedor),
  consignacion = VALUES(consignacion), monto_fijo = VALUES(monto_fijo);

INSERT INTO productos_formato (id, nombre, monto_objetivo) VALUES
  (1, 'Otros',          50000),
  (2, 'Libro',         400000),
  (3, 'Manga / Cómic', 300000)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), monto_objetivo = VALUES(monto_objetivo);

INSERT INTO productos_serie (id, nombre, cupo_maximo, cupo_minimo) VALUES
  (1, 'Sin serie', 0, 0)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), cupo_maximo = VALUES(cupo_maximo), cupo_minimo = VALUES(cupo_minimo);

-- Ivrea: primer tomo de las 20 primeras series del catálogo (formato Manga / Cómic).
-- Planeta: 20 primeros libros del catálogo del sello (formato Libro). SKU = ISBN.
INSERT INTO productos (id, sku, titulo, id_editorial, stock, precio, validado, id_serie, tomo, id_formato, admite_descuento, nuevo, precio_costo, precio_oferta, habilitado) VALUES
  ( 1, 'IVREA-0001', '1-F (FUKUSHIMA 1) 01',                    1,  6, 13500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 2, 'IVREA-0002', '10TH 01',                                 1,  3, 12800, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 3, 'IVREA-0003', '20TH CENTURY BOYS 01',                    1,  8, 15900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 4, 'IVREA-0004', '21ST CENTURY BOYS 01',                    1,  5, 14900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 5, 'IVREA-0005', '300 01',                                  1,  2, 24500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 6, 'IVREA-0006', '4 SEGUNDOS 01',                           1,  4, 11900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 7, 'IVREA-0007', '5 CENTIMETROS POR SEGUNDO 01',            1,  7, 12500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 8, 'IVREA-0008', 'ADABANA 01',                              1,  3, 12900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  ( 9, 'IVREA-0009', 'AGENCIA DEL AMOR 01',                     1,  5, 11500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (10, 'IVREA-0010', 'AJIN - SEMIHUMANO 01',                    1,  9, 13900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (11, 'IVREA-0011', 'AKAME GA KILL! 01',                       1,  6, 12900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (12, 'IVREA-0012', 'AKEBI''S SAILOR UNIFORM 01',              1,  4, 12900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (13, 'IVREA-0013', 'AKU NO HANA -LAS FLORES DEL MAL- 01',     1,  3, 13500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (14, 'IVREA-0014', 'AKUMA NO RIDDLE 01',                      1,  2, 12500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (15, 'IVREA-0015', 'ALICE IN BORDERLAND 01',                  1, 10, 13900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (16, 'IVREA-0016', 'ALICE IN BORDERLAND: RETRY 01',           1,  5, 13900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (17, 'IVREA-0017', 'ALICE ON BORDER ROAD 01',                 1,  4, 14500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (18, 'IVREA-0018', 'AMAR Y SER AMADO, DEJAR Y SER DEJADO 01', 1,  6, 12500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (19, 'IVREA-0019', 'AMOR AL DESNUDO 01',                      1,  3, 12900, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (20, 'IVREA-0020', 'AMOR DE GATA: A WHISKER AWAY 01',         1,  2, 16500, 1, 1, 1, 3, 1, 1, 0, 0, 1),
  (21, '9788408326861', 'El día de la Trilla (Empíreo) - Edición limitada con cantos tintados', 2,  3, 52900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (22, '9788408326595', 'Un hombre de honor',                                    2,  8, 38900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (23, '9788408325499', 'Un otoño para enamorarnos',                             2,  6, 32900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (24, '9788408325062', 'La Humanidad en crisis',                                2,  4, 30900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (25, '9788408327134', 'El día que lo fuimos todo',                             2,  7, 27900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (26, '9788408319054', 'Aduana',                                                2,  5, 29900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (27, '9788408326847', 'El día de la Trilla (Empíreo)',                         2, 12, 39900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (28, '9788408324539', 'El círculo de la felicidad',                            2,  9, 24900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (29, '9788408325208', 'El arte de leer la mente',                              2,  4, 31900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (30, '9788408325512', 'El misterio de las recetas de la señora Jeong',         2,  6, 32900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (31, '9788408324867', 'La cena secreta',                                       2,  5, 36900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (32, '9788408324751', 'Los Huérfanos',                                         2, 10, 38900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (33, '9788408324980', 'Adolescencia',                                          2,  4, 33900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (34, '9788408325536', 'El llanto de los muertos (Los crímenes de Fjällbacka)', 2,  7, 41900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (35, '9788408322177', 'Hija de ningún mundo',                                  2,  6, 44900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (36, '9788408322764', 'El baile de las criadas',                               2,  5, 37900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (37, '9788408321262', 'Giulia, el amor y un puñado de lavanda',                2,  8, 34900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (38, '9788408322085', 'La democracia amenazada',                               2,  3, 35900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (39, '9788408320715', 'Mujeres de hierro',                                     2,  4, 34900, 1, 1, 0, 2, 1, 1, 0, 0, 1),
  (40, '9788408320555', 'El club de las indomables',                             2,  6, 47900, 1, 1, 0, 2, 1, 1, 0, 0, 1)
ON DUPLICATE KEY UPDATE sku = VALUES(sku), titulo = VALUES(titulo), id_editorial = VALUES(id_editorial), stock = VALUES(stock),
  precio = VALUES(precio), validado = VALUES(validado), id_serie = VALUES(id_serie), tomo = VALUES(tomo),
  id_formato = VALUES(id_formato), admite_descuento = VALUES(admite_descuento), nuevo = VALUES(nuevo),
  precio_oferta = VALUES(precio_oferta), habilitado = VALUES(habilitado);

-- Costo según el margen de cada editorial.
UPDATE productos p JOIN editoriales e ON e.id = p.id_editorial
SET p.precio_costo = ROUND(p.precio * (100 - e.porcentaje) / 100)
WHERE p.id BETWEEN 1 AND 40;
