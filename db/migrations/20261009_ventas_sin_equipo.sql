-- Shoten no usa equipos: las ventas dejan de depender de la tabla equipos.
-- La columna se conserva (nullable) para no romper datos históricos.
ALTER TABLE ventas
  DROP FOREIGN KEY ventas_ibfk_1,
  MODIFY id_equipo INT(11) NULL DEFAULT NULL;
