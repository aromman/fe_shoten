-- Historial de salarios de referencia (Personal > Salarios).
-- La pantalla maestros/salarios.php la usaba pero la tabla nunca existió.
CREATE TABLE IF NOT EXISTS salario (
  id    INT(11)       NOT NULL AUTO_INCREMENT,
  fecha DATE          NOT NULL,
  monto DECIMAL(14,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_salario_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
