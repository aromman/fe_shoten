<?php
require_once 'crud.php';
require_once 'logger.php';

class Objetivo extends Crud
{
    const TABLE = 'objetivos';
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

    public function obtenerOCrear($nombre, $prioridad, $logrado)
    {
        try {
            $row = $this->obtenerPorNombre($nombre);
            if (!is_array($row)) {
                $stm = $this->pdo->prepare("
                    INSERT INTO objetivos (prioridad, nombre, objetivo, logrado)
                    VALUES (:prioridad, :nombre, 0, :logrado)
                ");
                $stm->execute(array(
                    ':prioridad' => (int) $prioridad,
                    ':nombre' => (string) $nombre,
                    ':logrado' => (float) $logrado,
                ));
                $row = $this->obtenerPorNombre($nombre);
            } else {
                $this->actualizarLogrado((int) $row['id'], $logrado);
                $row['logrado'] = (float) $logrado;
            }

            return is_array($row) ? $row : null;
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return null;
        }
    }

    public function actualizarObjetivo($id, $objetivo)
    {
        try {
            $stm = $this->pdo->prepare("UPDATE objetivos SET objetivo = :objetivo WHERE id = :id");
            $stm->execute(array(
                ':id' => (int) $id,
                ':objetivo' => (float) $objetivo,
            ));

            return $this->obtenerPorId($id);
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return null;
        }
    }

    public function actualizarObjetivosPorNombre($objetivos)
    {
        try {
            $this->pdo->beginTransaction();
            $stm = $this->pdo->prepare("UPDATE objetivos SET objetivo = :objetivo WHERE nombre = :nombre");
            foreach ($objetivos as $nombre => $objetivo) {
                $stm->execute(array(
                    ':nombre' => (string) $nombre,
                    ':objetivo' => (float) $objetivo,
                ));
            }
            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return false;
        }
    }

    public function obtenerPorNombre($nombre)
    {
        $stm = $this->pdo->prepare("SELECT * FROM objetivos WHERE nombre = :nombre ORDER BY id LIMIT 1");
        $stm->execute(array(':nombre' => (string) $nombre));
        $row = $stm->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function obtenerPorId($id)
    {
        $stm = $this->pdo->prepare("SELECT * FROM objetivos WHERE id = :id");
        $stm->execute(array(':id' => (int) $id));
        $row = $stm->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function actualizarLogrado($id, $logrado)
    {
        $stm = $this->pdo->prepare("UPDATE objetivos SET logrado = :logrado WHERE id = :id");
        $stm->execute(array(
            ':id' => (int) $id,
            ':logrado' => (float) $logrado,
        ));
    }
}
