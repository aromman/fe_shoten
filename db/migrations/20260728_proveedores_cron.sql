ALTER TABLE proveedores
    ADD COLUMN cron_pedido VARCHAR(100) NULL AFTER dia_pedido,
    ADD COLUMN cron_entrega VARCHAR(100) NULL AFTER dias_entrega;

ALTER TABLE proveedores
    MODIFY dia_pedido INT(3) NOT NULL DEFAULT 0,
    MODIFY dias_entrega INT(3) NOT NULL DEFAULT 0;

UPDATE proveedores
SET cron_pedido = TRIM(BOTH ',' FROM CONCAT(
        IF((dia_pedido & 1) = 1, '0,', ''),
        IF((dia_pedido & 2) = 2, '1,', ''),
        IF((dia_pedido & 4) = 4, '2,', ''),
        IF((dia_pedido & 8) = 8, '3,', ''),
        IF((dia_pedido & 16) = 16, '4,', ''),
        IF((dia_pedido & 32) = 32, '5,', ''),
        IF((dia_pedido & 64) = 64, '6,', '')
    ))
WHERE cron_pedido IS NULL AND dia_pedido > 0;

UPDATE proveedores
SET cron_pedido = CONCAT('0 0 * * ', cron_pedido)
WHERE cron_pedido IS NOT NULL AND cron_pedido <> '' AND cron_pedido NOT LIKE '% %';

UPDATE proveedores
SET cron_entrega = TRIM(BOTH ',' FROM CONCAT(
        IF((dias_entrega & 1) = 1, '0,', ''),
        IF((dias_entrega & 2) = 2, '1,', ''),
        IF((dias_entrega & 4) = 4, '2,', ''),
        IF((dias_entrega & 8) = 8, '3,', ''),
        IF((dias_entrega & 16) = 16, '4,', ''),
        IF((dias_entrega & 32) = 32, '5,', ''),
        IF((dias_entrega & 64) = 64, '6,', '')
    ))
WHERE cron_entrega IS NULL AND dias_entrega > 0;

UPDATE proveedores
SET cron_entrega = CONCAT('0 0 * * ', cron_entrega)
WHERE cron_entrega IS NOT NULL AND cron_entrega <> '' AND cron_entrega NOT LIKE '% %';
