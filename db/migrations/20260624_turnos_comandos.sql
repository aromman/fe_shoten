CREATE TABLE IF NOT EXISTS turnos_comandos (
    idempotency_key VARCHAR(64) NOT NULL,
    tipo VARCHAR(32) NOT NULL,
    canal_id INT(11) NOT NULL,
    username VARCHAR(100) NOT NULL,
    estado VARCHAR(16) NOT NULL,
    respuesta_json TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    completed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (idempotency_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
