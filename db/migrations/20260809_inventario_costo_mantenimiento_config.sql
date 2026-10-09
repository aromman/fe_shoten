CREATE TABLE IF NOT EXISTS inventario_costo_mantenimiento_config (
    id TINYINT(1) NOT NULL,
    tasa_anual DOUBLE NOT NULL DEFAULT 0,
    actualizado_en DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO inventario_costo_mantenimiento_config (id, tasa_anual, actualizado_en)
VALUES (1, 25, NOW())
ON DUPLICATE KEY UPDATE id = id;
