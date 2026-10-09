CREATE TABLE IF NOT EXISTS billetera_turnos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_cuenta INT(11) NOT NULL,
    fecha_apertura DATETIME NOT NULL,
    monto_apertura DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_cierre DATETIME NULL,
    monto_esperado DECIMAL(12,2) NULL,
    monto_cierre DECIMAL(12,2) NULL,
    diferencia DECIMAL(12,2) NULL,
    estado VARCHAR(1) NOT NULL DEFAULT 'A',
    usuario_apertura VARCHAR(100) NULL,
    usuario_cierre VARCHAR(100) NULL,
    PRIMARY KEY (id),
    KEY idx_billetera_turnos_cuenta (id_cuenta),
    CONSTRAINT chk_billetera_turnos_estado CHECK (estado IN ('A', 'C')),
    CONSTRAINT fk_billetera_turnos_cuenta
        FOREIGN KEY (id_cuenta) REFERENCES cuentas (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS billetera_turnos_billetes (
    turno_id INT(11) NOT NULL,
    momento VARCHAR(10) NOT NULL,
    denominacion INT(11) NOT NULL,
    cantidad INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (turno_id, momento, denominacion),
    CONSTRAINT chk_billetera_turnos_billetes_momento CHECK (momento IN ('APERTURA', 'CIERRE')),
    CONSTRAINT fk_billetera_turnos_billetes_turno
        FOREIGN KEY (turno_id) REFERENCES billetera_turnos (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Solo un turno abierto por cuenta a la vez: columna virtual que solo tiene
-- valor cuando estado='A', con un indice unico sobre ella (los NULL no chocan).
ALTER TABLE billetera_turnos
    ADD COLUMN id_cuenta_abierta INT(11)
        GENERATED ALWAYS AS (CASE WHEN estado = 'A' THEN id_cuenta ELSE NULL END) VIRTUAL;

CREATE UNIQUE INDEX idx_billetera_turnos_cuenta_abierta
    ON billetera_turnos (id_cuenta_abierta);
