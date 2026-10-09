<?php
require_once 'crud.php';
require_once 'logger.php';

class TicketPromedio extends Crud
{
    const TABLE = 'ventas_header';
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

    public function getResumenMesActual()
    {
        try {
            $sql = "
                SELECT
                    COALESCE(SUM(total), 0) ventas,
                    COUNT(id) operaciones,
                    COALESCE(SUM(cantidad), 0) unidades,
                    COALESCE(SUM(total) / NULLIF(COUNT(id), 0), 0) ticket_promedio,
                    COALESCE(SUM(cantidad) / NULLIF(COUNT(id), 0), 0) unidades_promedio
                FROM ventas_header
                WHERE fecha >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
                  AND fecha < DATE_ADD(DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'), INTERVAL 1 MONTH)
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : $this->resumenVacio();
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return $this->resumenVacio();
        }
    }

    public function getResumenDiaActual()
    {
        try {
            $sql = "
                SELECT
                    COALESCE(SUM(total), 0) ventas,
                    COUNT(id) operaciones,
                    COALESCE(SUM(cantidad), 0) unidades,
                    COALESCE(SUM(total) / NULLIF(COUNT(id), 0), 0) ticket_promedio,
                    COALESCE(SUM(cantidad) / NULLIF(COUNT(id), 0), 0) unidades_promedio
                FROM ventas_header
                WHERE fecha >= CURRENT_DATE
                  AND fecha < DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY)
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $row = $stm->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : $this->resumenVacio();
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return $this->resumenVacio();
        }
    }

    public function getEvolucionMensual($meses)
    {
        try {
            $meses = max(1, min(24, (int) $meses));
            $sql = "
                SELECT
                    DATE_FORMAT(fecha, '%Y-%m') periodo,
                    COALESCE(SUM(total), 0) ventas,
                    COUNT(id) operaciones,
                    COALESCE(SUM(cantidad), 0) unidades,
                    COALESCE(SUM(total) / NULLIF(COUNT(id), 0), 0) ticket_promedio
                FROM ventas_header
                WHERE fecha >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL " . ($meses - 1) . " MONTH), '%Y-%m-01')
                  AND fecha < DATE_ADD(DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'), INTERVAL 1 MONTH)
                GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                ORDER BY periodo DESC
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return array();
        }
    }

    public function getEvolucionDiaria($dias)
    {
        try {
            $dias = max(1, min(90, (int) $dias));
            $sql = "
                SELECT
                    DATE_FORMAT(fecha, '%Y-%m-%d') periodo,
                    COALESCE(SUM(total), 0) ventas,
                    COUNT(id) operaciones,
                    COALESCE(SUM(cantidad), 0) unidades,
                    COALESCE(SUM(total) / NULLIF(COUNT(id), 0), 0) ticket_promedio
                FROM ventas_header
                WHERE fecha >= DATE_SUB(CURRENT_DATE, INTERVAL " . ($dias - 1) . " DAY)
                  AND fecha < DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY)
                GROUP BY DATE_FORMAT(fecha, '%Y-%m-%d')
                ORDER BY periodo DESC
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return array();
        }
    }

    public function getMesActualPorCanal()
    {
        try {
            $sql = "
                SELECT
                    COALESCE(c.nombre, 'Sin canal') canal,
                    COALESCE(SUM(vh.total), 0) ventas,
                    COUNT(vh.id) operaciones,
                    COALESCE(SUM(vh.total) / NULLIF(COUNT(vh.id), 0), 0) ticket_promedio
                FROM ventas_header vh
                LEFT JOIN canal c ON c.id = vh.id_canal
                WHERE vh.fecha >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
                  AND vh.fecha < DATE_ADD(DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'), INTERVAL 1 MONTH)
                GROUP BY c.nombre
                ORDER BY ticket_promedio DESC
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return array();
        }
    }

    private function resumenVacio()
    {
        return array(
            'ventas' => 0,
            'operaciones' => 0,
            'unidades' => 0,
            'ticket_promedio' => 0,
            'unidades_promedio' => 0,
        );
    }
}
