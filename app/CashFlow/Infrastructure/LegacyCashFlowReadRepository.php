<?php

namespace App\CashFlow\Infrastructure;

final class LegacyCashFlowReadRepository extends \Connection
{
    private $pdo;

    private static $meses = array(
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    );

    public function __construct()
    {
        parent::__construct();
        $this->conexion();
        $this->pdo = $this->conexion();
    }

    public function obtenerFlujo12Meses()
    {
        $arrVentas = $this->totalesPorMes("
            SELECT
                SUM(IF(MONTH(v.fecha)=1, v.unidades * v.precio_unitario,0)) AS enero,
                SUM(IF(MONTH(v.fecha)=2, v.unidades * v.precio_unitario,0)) AS febrero,
                SUM(IF(MONTH(v.fecha)=3, v.unidades * v.precio_unitario,0)) AS marzo,
                SUM(IF(MONTH(v.fecha)=4, v.unidades * v.precio_unitario,0)) AS abril,
                SUM(IF(MONTH(v.fecha)=5, v.unidades * v.precio_unitario,0)) AS mayo,
                SUM(IF(MONTH(v.fecha)=6, v.unidades * v.precio_unitario,0)) AS junio,
                SUM(IF(MONTH(v.fecha)=7, v.unidades * v.precio_unitario,0)) AS julio,
                SUM(IF(MONTH(v.fecha)=8, v.unidades * v.precio_unitario,0)) AS agosto,
                SUM(IF(MONTH(v.fecha)=9, v.unidades * v.precio_unitario,0)) AS septiembre,
                SUM(IF(MONTH(v.fecha)=10, v.unidades * v.precio_unitario,0)) AS octubre,
                SUM(IF(MONTH(v.fecha)=11, v.unidades * v.precio_unitario,0)) AS noviembre,
                SUM(IF(MONTH(v.fecha)=12, v.unidades * v.precio_unitario,0)) AS diciembre
            FROM ventas v
            WHERE YEAR(v.fecha) = YEAR(CURRENT_DATE())
        ");

        $arrCompras = $this->totalesPorMes("
            SELECT
                SUM(IF(MONTH(fecha)=1, precio_costo * cantidad,0)) AS enero,
                SUM(IF(MONTH(fecha)=2, precio_costo * cantidad,0)) AS febrero,
                SUM(IF(MONTH(fecha)=3, precio_costo * cantidad,0)) AS marzo,
                SUM(IF(MONTH(fecha)=4, precio_costo * cantidad,0)) AS abril,
                SUM(IF(MONTH(fecha)=5, precio_costo * cantidad,0)) AS mayo,
                SUM(IF(MONTH(fecha)=6, precio_costo * cantidad,0)) AS junio,
                SUM(IF(MONTH(fecha)=7, precio_costo * cantidad,0)) AS julio,
                SUM(IF(MONTH(fecha)=8, precio_costo * cantidad,0)) AS agosto,
                SUM(IF(MONTH(fecha)=9, precio_costo * cantidad,0)) AS septiembre,
                SUM(IF(MONTH(fecha)=10, precio_costo * cantidad,0)) AS octubre,
                SUM(IF(MONTH(fecha)=11, precio_costo * cantidad,0)) AS noviembre,
                SUM(IF(MONTH(fecha)=12, precio_costo * cantidad,0)) AS diciembre
            FROM compras
            WHERE id_estado != 1
              AND YEAR(fecha) = YEAR(CURRENT_DATE())
        ");

        $arrGastos = $this->totalesPorMes("
            SELECT
                SUM(IF(MONTH(fecha)=1, monto,0)) AS enero,
                SUM(IF(MONTH(fecha)=2, monto,0)) AS febrero,
                SUM(IF(MONTH(fecha)=3, monto,0)) AS marzo,
                SUM(IF(MONTH(fecha)=4, monto,0)) AS abril,
                SUM(IF(MONTH(fecha)=5, monto,0)) AS mayo,
                SUM(IF(MONTH(fecha)=6, monto,0)) AS junio,
                SUM(IF(MONTH(fecha)=7, monto,0)) AS julio,
                SUM(IF(MONTH(fecha)=8, monto,0)) AS agosto,
                SUM(IF(MONTH(fecha)=9, monto,0)) AS septiembre,
                SUM(IF(MONTH(fecha)=10, monto,0)) AS octubre,
                SUM(IF(MONTH(fecha)=11, monto,0)) AS noviembre,
                SUM(IF(MONTH(fecha)=12, monto,0)) AS diciembre
            FROM gastos
            WHERE monto > 0
              AND YEAR(fecha) = YEAR(CURRENT_DATE())
        ");

        $arrPrestamosRecibidos = $this->totalesPorMes("
            SELECT
                SUM(IF(MONTH(c.fecha)=1, c.precio_costo * c.cantidad,0)) AS enero,
                SUM(IF(MONTH(c.fecha)=2, c.precio_costo * c.cantidad,0)) AS febrero,
                SUM(IF(MONTH(c.fecha)=3, c.precio_costo * c.cantidad,0)) AS marzo,
                SUM(IF(MONTH(c.fecha)=4, c.precio_costo * c.cantidad,0)) AS abril,
                SUM(IF(MONTH(c.fecha)=5, c.precio_costo * c.cantidad,0)) AS mayo,
                SUM(IF(MONTH(c.fecha)=6, c.precio_costo * c.cantidad,0)) AS junio,
                SUM(IF(MONTH(c.fecha)=7, c.precio_costo * c.cantidad,0)) AS julio,
                SUM(IF(MONTH(c.fecha)=8, c.precio_costo * c.cantidad,0)) AS agosto,
                SUM(IF(MONTH(c.fecha)=9, c.precio_costo * c.cantidad,0)) AS septiembre,
                SUM(IF(MONTH(c.fecha)=10, c.precio_costo * c.cantidad,0)) AS octubre,
                SUM(IF(MONTH(c.fecha)=11, c.precio_costo * c.cantidad,0)) AS noviembre,
                SUM(IF(MONTH(c.fecha)=12, c.precio_costo * c.cantidad,0)) AS diciembre
            FROM compras c
            INNER JOIN medio_pago mp ON c.id_medio_pago = mp.id
            INNER JOIN cuentas cu ON mp.id_cuenta = cu.id
            WHERE c.id_estado != 1
              AND YEAR(c.fecha) = YEAR(CURRENT_DATE())
              AND cu.tipo = 'P'
        ");

        $arrGastosFinanciados = $this->totalesPorMes("
            SELECT
                SUM(IF(MONTH(g.fecha)=1, g.monto,0)) AS enero,
                SUM(IF(MONTH(g.fecha)=2, g.monto,0)) AS febrero,
                SUM(IF(MONTH(g.fecha)=3, g.monto,0)) AS marzo,
                SUM(IF(MONTH(g.fecha)=4, g.monto,0)) AS abril,
                SUM(IF(MONTH(g.fecha)=5, g.monto,0)) AS mayo,
                SUM(IF(MONTH(g.fecha)=6, g.monto,0)) AS junio,
                SUM(IF(MONTH(g.fecha)=7, g.monto,0)) AS julio,
                SUM(IF(MONTH(g.fecha)=8, g.monto,0)) AS agosto,
                SUM(IF(MONTH(g.fecha)=9, g.monto,0)) AS septiembre,
                SUM(IF(MONTH(g.fecha)=10, g.monto,0)) AS octubre,
                SUM(IF(MONTH(g.fecha)=11, g.monto,0)) AS noviembre,
                SUM(IF(MONTH(g.fecha)=12, g.monto,0)) AS diciembre
            FROM gastos g
            INNER JOIN medio_pago mp ON g.id_medio_pago = mp.id
            INNER JOIN cuentas cu ON mp.id_cuenta = cu.id
            WHERE g.monto > 0
              AND YEAR(g.fecha) = YEAR(CURRENT_DATE())
              AND cu.tipo = 'P'
        ");

        foreach (self::$meses as $i => $nombre) {
            $arrPrestamosRecibidos[$i] += $arrGastosFinanciados[$i];
        }

        $arrPagoPrestamos = $this->totalesPorMes("
            SELECT
                SUM(IF(MONTH(v.fecha)=1, v.unidades * v.precio_unitario,0)) AS enero,
                SUM(IF(MONTH(v.fecha)=2, v.unidades * v.precio_unitario,0)) AS febrero,
                SUM(IF(MONTH(v.fecha)=3, v.unidades * v.precio_unitario,0)) AS marzo,
                SUM(IF(MONTH(v.fecha)=4, v.unidades * v.precio_unitario,0)) AS abril,
                SUM(IF(MONTH(v.fecha)=5, v.unidades * v.precio_unitario,0)) AS mayo,
                SUM(IF(MONTH(v.fecha)=6, v.unidades * v.precio_unitario,0)) AS junio,
                SUM(IF(MONTH(v.fecha)=7, v.unidades * v.precio_unitario,0)) AS julio,
                SUM(IF(MONTH(v.fecha)=8, v.unidades * v.precio_unitario,0)) AS agosto,
                SUM(IF(MONTH(v.fecha)=9, v.unidades * v.precio_unitario,0)) AS septiembre,
                SUM(IF(MONTH(v.fecha)=10, v.unidades * v.precio_unitario,0)) AS octubre,
                SUM(IF(MONTH(v.fecha)=11, v.unidades * v.precio_unitario,0)) AS noviembre,
                SUM(IF(MONTH(v.fecha)=12, v.unidades * v.precio_unitario,0)) AS diciembre
            FROM ventas v
            INNER JOIN medio_pago mp ON v.id_medio_pago = mp.id
            INNER JOIN cuentas cu ON mp.id_cuenta = cu.id
            WHERE YEAR(v.fecha) = YEAR(CURRENT_DATE())
              AND cu.tipo = 'P'
        ");

        $arrSaldoInicial = array();
        $arrTotalIngresos = array();
        $arrTotalEgresos = array();
        $arrFlujoCajaEconomico = array();
        $arrFlujoFinanciero = array();

        $arrSaldoInicial[1] = 0.0;
        foreach (self::$meses as $i => $nombre) {
            if ($i > 1) {
                $arrSaldoInicial[$i] = $arrFlujoFinanciero[$i - 1];
            }
            $arrTotalIngresos[$i] = $arrVentas[$i];
            $arrTotalEgresos[$i] = $arrCompras[$i] + $arrGastos[$i];
            $arrFlujoCajaEconomico[$i] = $arrSaldoInicial[$i] + $arrTotalIngresos[$i] - $arrTotalEgresos[$i];
            $arrFlujoFinanciero[$i] = $arrFlujoCajaEconomico[$i] + $arrPrestamosRecibidos[$i] - $arrPagoPrestamos[$i];
        }

        $meses = array();
        $maxima = 0.0;
        foreach (self::$meses as $i => $nombre) {
            $meses[] = array(
                'mes' => $nombre,
                'saldoInicial' => round($arrSaldoInicial[$i], 2),
                'ventas' => round($arrVentas[$i], 2),
                'totalIngresos' => round($arrTotalIngresos[$i], 2),
                'compras' => round($arrCompras[$i], 2),
                'gastos' => round($arrGastos[$i], 2),
                'totalEgresos' => round($arrTotalEgresos[$i], 2),
                'flujoCajaEconomico' => round($arrFlujoCajaEconomico[$i], 2),
                'financiamientoRecibido' => round($arrPrestamosRecibidos[$i], 2),
                'pagoFinanciamiento' => round($arrPagoPrestamos[$i], 2),
                'flujoFinanciero' => round($arrFlujoFinanciero[$i], 2),
            );
            $maxima = max($maxima, $arrFlujoCajaEconomico[$i], $arrFlujoFinanciero[$i]);
        }

        return array(
            'meses' => $meses,
            'maximoEje' => round($maxima, 2),
        );
    }

    private function totalesPorMes($sql)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            $row = array();
        }

        $claves = array(
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        );

        $resultado = array();
        foreach ($claves as $i => $clave) {
            $resultado[$i] = isset($row[$clave]) ? (float) $row[$clave] : 0.0;
        }

        return $resultado;
    }
}
