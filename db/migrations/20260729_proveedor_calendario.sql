ALTER TABLE proveedores
    ADD COLUMN origen VARCHAR(20) NOT NULL DEFAULT 'local' AFTER id,
    ADD COLUMN origen_id VARCHAR(100) NULL AFTER origen,
    ADD COLUMN email VARCHAR(255) NULL AFTER nombre,
    ADD COLUMN telefono VARCHAR(100) NULL AFTER email,
    ADD COLUMN direccion VARCHAR(255) NULL AFTER telefono,
    ADD COLUMN fiscal_number VARCHAR(100) NULL AFTER direccion,
    ADD COLUMN comentario TEXT NULL AFTER fiscal_number;

CREATE TABLE proveedor_calendario (
    id INT NOT NULL AUTO_INCREMENT,
    proveedor_id INT NOT NULL,
    cron_pedido VARCHAR(100) NULL,
    cron_entrega VARCHAR(100) NULL,
    cantidad_minima DECIMAL(12,2) NOT NULL DEFAULT 0,
    monto_minimo DECIMAL(12,2) NOT NULL DEFAULT 0,
    vigente_desde DATE NULL,
    vigente_hasta DATE NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY ux_proveedor_calendario_proveedor (proveedor_id),
    CONSTRAINT fk_proveedor_calendario_proveedor
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO proveedor_calendario (
    proveedor_id,
    cron_pedido,
    cron_entrega,
    cantidad_minima,
    monto_minimo,
    activo
)
SELECT
    id,
    cron_pedido,
    cron_entrega,
    cantidad_minima,
    monto_minimo,
    1
FROM proveedores
ON DUPLICATE KEY UPDATE
    cron_pedido = VALUES(cron_pedido),
    cron_entrega = VALUES(cron_entrega),
    cantidad_minima = VALUES(cantidad_minima),
    monto_minimo = VALUES(monto_minimo),
    activo = VALUES(activo);

ALTER TABLE proveedores
    DROP COLUMN cron_pedido,
    DROP COLUMN cron_entrega,
    DROP COLUMN dia_pedido,
    DROP COLUMN dias_entrega,
    DROP COLUMN cantidad_minima,
    DROP COLUMN monto_minimo;
