<?php
require_once 'crud.php';
require_once 'logger.php';

class InventarioCostoMantenimientoConfig extends Crud
{
    const TABLE = 'inventario_costo_mantenimiento_config';
    public $pdo;
    public $logger;

    public function __construct()
    {
        parent::__construct(self::TABLE);
        $this->pdo = parent::conexion();
        $this->logger = new Logger();
    }

    public function create()
    {
        return null;
    }

    public function update()
    {
        return null;
    }

    public function obtener()
    {
        try {
            $stm = $this->pdo->prepare('SELECT * FROM ' . self::TABLE . ' WHERE id = 1');
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : array('id' => 1, 'tasa_anual' => 0, 'tasa_interes_anual' => 0, 'actualizado_en' => null);
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return array('id' => 1, 'tasa_anual' => 0, 'tasa_interes_anual' => 0, 'actualizado_en' => null);
        }
    }

    public function guardarTasaAnual($tasaAnual)
    {
        try {
            $sql = 'INSERT INTO ' . self::TABLE . ' (id, tasa_anual, actualizado_en)
                    VALUES (1, :tasa, :actualizadoEn)
                    ON DUPLICATE KEY UPDATE
                        tasa_anual = VALUES(tasa_anual),
                        actualizado_en = VALUES(actualizado_en)';
            $stm = $this->pdo->prepare($sql);

            return $stm->execute(array(
                ':tasa' => (float) $tasaAnual,
                ':actualizadoEn' => date('Y-m-d H:i:s'),
            ));
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return false;
        }
    }

    public function guardarTasaInteresAnual($tasaInteresAnual)
    {
        try {
            $sql = 'INSERT INTO ' . self::TABLE . ' (id, tasa_interes_anual, actualizado_en)
                    VALUES (1, :tasa, :actualizadoEn)
                    ON DUPLICATE KEY UPDATE
                        tasa_interes_anual = VALUES(tasa_interes_anual),
                        actualizado_en = VALUES(actualizado_en)';
            $stm = $this->pdo->prepare($sql);

            return $stm->execute(array(
                ':tasa' => (float) $tasaInteresAnual,
                ':actualizadoEn' => date('Y-m-d H:i:s'),
            ));
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return false;
        }
    }
}
