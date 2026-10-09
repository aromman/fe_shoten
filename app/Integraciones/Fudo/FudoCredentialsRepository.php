<?php

namespace App\Integraciones\Fudo;

use App\Integraciones\Seguridad\SecretCipher;

final class FudoCredentialsRepository extends \Connection
{
    private $pdo;
    private $cipher;

    public function __construct(SecretCipher $cipher)
    {
        parent::__construct();
        $this->conexion();
        $this->pdo = $this->conexion();
        $this->cipher = $cipher;

        if (!$this->pdo instanceof \PDO) {
            throw new \RuntimeException('No se pudo establecer conexión con la base de datos.');
        }
    }

    public function obtener()
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT credenciales_cifradas
                FROM integraciones_configuracion
                WHERE proveedor = :proveedor
                  AND habilitada = 1
                LIMIT 1
            ");
            $statement->execute(array(':proveedor' => 'fudo'));
            $row = $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $error) {
            if ($error->getCode() === '42S02') {
                return array();
            }
            throw $error;
        }

        return $row === false
            ? array()
            : $this->cipher->descifrar($row['credenciales_cifradas']);
    }

    public function guardar($apiKey, $apiSecret)
    {
        $this->crearTablaSiNoExiste();
        $payload = $this->cipher->cifrar(array(
            'apiKey' => trim((string) $apiKey),
            'apiSecret' => trim((string) $apiSecret),
        ));

        $statement = $this->pdo->prepare("
            INSERT INTO integraciones_configuracion
                (proveedor, credenciales_cifradas, habilitada, actualizado_en)
            VALUES
                (:proveedor, :credenciales, 1, NOW())
            ON DUPLICATE KEY UPDATE
                credenciales_cifradas = VALUES(credenciales_cifradas),
                habilitada = 1,
                actualizado_en = NOW()
        ");
        $statement->execute(array(
            ':proveedor' => 'fudo',
            ':credenciales' => $payload,
        ));
    }

    private function crearTablaSiNoExiste()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS integraciones_configuracion (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                proveedor VARCHAR(50) NOT NULL,
                credenciales_cifradas TEXT NOT NULL,
                habilitada TINYINT(1) NOT NULL DEFAULT 1,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_integraciones_proveedor (proveedor)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
