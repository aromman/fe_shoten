ALTER TABLE inventario_costo_mantenimiento_config
    ADD COLUMN IF NOT EXISTS tasa_interes_anual DOUBLE NOT NULL DEFAULT 0 AFTER tasa_anual;
