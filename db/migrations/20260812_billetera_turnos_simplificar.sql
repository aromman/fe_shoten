DROP INDEX idx_billetera_turnos_cuenta_abierta ON billetera_turnos;

ALTER TABLE billetera_turnos
    DROP COLUMN id_cuenta_abierta;

DROP TABLE IF EXISTS billetera_turnos_billetes;
