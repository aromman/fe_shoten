CREATE TABLE IF NOT EXISTS integraciones_configuracion (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    proveedor VARCHAR(50) NOT NULL,
    credenciales_cifradas TEXT NOT NULL,
    habilitada TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_integraciones_proveedor (proveedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
