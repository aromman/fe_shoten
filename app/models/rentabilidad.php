<?php
require_once 'crud.php';
require_once 'logger.php';

class Rentabilidad extends Crud
{
    const TABLE = 'ventas';
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
                    COALESCE(SUM(total), 0) total_ventas,
                    COUNT(DISTINCT id_ventas_header) cantidad_ventas,
                    COALESCE(SUM(unidades), 0) total_unidades,
                    COALESCE(SUM(costo), 0) total_costo
                FROM ventas
                WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
                  AND MONTH(fecha) = MONTH(CURRENT_DATE())
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

    public function getVentasPorCanalYMes($anio)
    {
        try {
            $sql = "
                SELECT
                    c.nombre canal,
                    MONTH(v.fecha) mes,
                    COALESCE(SUM(v.unidades * v.precio_unitario), 0) total
                FROM ventas v
                INNER JOIN canal c ON c.id = v.id_canal
                WHERE YEAR(v.fecha) = :anio
                GROUP BY c.nombre, MONTH(v.fecha)
                ORDER BY c.nombre
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute(array(':anio' => $anio));

            return $this->agruparPorEtiquetaYMes($stm->fetchAll(PDO::FETCH_ASSOC), 'canal');
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return array();
        }
    }

    public function getDescuentosPorMes($anio)
    {
        try {
            $sql = "
                SELECT
                    MONTH(fecha) mes,
                    COALESCE(SUM(descuento), 0) total
                FROM ventas
                WHERE YEAR(fecha) = :anio
                GROUP BY MONTH(fecha)
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute(array(':anio' => $anio));

            return $this->indexarPorMes($stm->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return $this->mesesVacios();
        }
    }

    public function getGastosFijosPorClaseYMes($anio)
    {
        try {
            $sql = "
                SELECT
                    gc.nombre clase,
                    MONTH(g.fecha) mes,
                    COALESCE(SUM(g.monto), 0) total
                FROM gastos g
                INNER JOIN gastos_clase gc ON gc.id = g.id_clase
                WHERE g.tipo = 'F'
                  AND g.monto > 0
                  AND YEAR(g.fecha) = :anio
                GROUP BY gc.nombre, MONTH(g.fecha)
                ORDER BY gc.nombre
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute(array(':anio' => $anio));

            return $this->agruparPorEtiquetaYMes($stm->fetchAll(PDO::FETCH_ASSOC), 'clase');
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return array();
        }
    }

    public function getAjustesStockPorMes($anio)
    {
        try {
            $sql = "
                SELECT
                    MONTH(fecha) mes,
                    COALESCE(SUM(monto), 0) total
                FROM costos
                WHERE YEAR(fecha) = :anio
                GROUP BY MONTH(fecha)
            ";
            $stm = $this->pdo->prepare($sql);
            $stm->execute(array(':anio' => $anio));

            return $this->indexarPorMes($stm->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
            return $this->mesesVacios();
        }
    }

    private function agruparPorEtiquetaYMes($filas, $campoEtiqueta)
    {
        $porEtiqueta = array();
        foreach ($filas as $fila) {
            $etiqueta = $fila[$campoEtiqueta];
            if (!isset($porEtiqueta[$etiqueta])) {
                $porEtiqueta[$etiqueta] = $this->mesesVacios();
            }
            $porEtiqueta[$etiqueta][(int) $fila['mes']] = round((float) $fila['total'], 2);
        }

        return $porEtiqueta;
    }

    private function indexarPorMes($filas)
    {
        $meses = $this->mesesVacios();
        foreach ($filas as $fila) {
            $meses[(int) $fila['mes']] = round((float) $fila['total'], 2);
        }

        return $meses;
    }

    private function mesesVacios()
    {
        return array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0);
    }

    private function resumenVacio()
    {
        return array(
            'total_ventas' => 0,
            'cantidad_ventas' => 0,
            'total_unidades' => 0,
            'total_costo' => 0,
        );
    }
}
