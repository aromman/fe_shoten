CREATE TABLE IF NOT EXISTS turnos_billetes (
    turno_id INT(11) NOT NULL,
    denominacion INT(11) NOT NULL,
    cantidad INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (turno_id, denominacion),
    CONSTRAINT fk_turnos_billetes_turno
        FOREIGN KEY (turno_id) REFERENCES turnos (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
