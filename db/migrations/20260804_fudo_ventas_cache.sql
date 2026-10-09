CREATE TABLE IF NOT EXISTS fudo_ventas_cache (
    tipo VARCHAR(4) NOT NULL,
    periodo VARCHAR(10) NOT NULL,
    total DOUBLE NOT NULL DEFAULT 0,
    cantidad_ventas INT(11) NOT NULL DEFAULT 0,
    personas INT(11) NOT NULL DEFAULT 0,
    cerrado TINYINT(1) NOT NULL DEFAULT 0,
    actualizado_en DATETIME NOT NULL,
    PRIMARY KEY (tipo, periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
