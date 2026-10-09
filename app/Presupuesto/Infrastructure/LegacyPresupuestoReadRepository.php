<?php

namespace App\Presupuesto\Infrastructure;

require_once __DIR__ . '/../../models/cuentas.php';
require_once __DIR__ . '/../../models/gastos.php';

final class LegacyPresupuestoReadRepository extends \Connection
{
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->conexion();
        $this->pdo = $this->conexion();
    }

    public function obtenerResumen()
    {
        $cuentasPDO = new \Cuentas();
        $saldoCuentas = 0.0;
        $totalActivo = $cuentasPDO->getTotalByType('A');
        if (!is_null($totalActivo) && !empty($totalActivo['monto'])) {
            $saldoCuentas = (float) $totalActivo['monto'];
        }

        $filasGastosClase = $this->consultarTodos("
            SELECT
                gc.id,
                gc.nombre,
                gc.presupuesto,
                gc.dia_pago,
                (SELECT IFNULL(SUM(g.monto), 0)
                 FROM gastos g
                 WHERE g.id_clase = gc.id
                   AND YEAR(g.fecha) = YEAR(CURRENT_DATE())
                   AND MONTH(g.fecha) = MONTH(CURRENT_DATE())
                ) AS monto
            FROM gastos_clase gc
            ORDER BY gc.prioridad
        ");

        $gastosEstimados = array();
        $gastosReales = array();
        $presupuestoTotal = 0.0;
        $gastoTotal = 0.0;

        foreach ($filasGastosClase as $fila) {
            $estimado = empty($fila['presupuesto']) ? 0.0 : (float) $fila['presupuesto'];
            $real = empty($fila['monto']) ? 0.0 : (float) $fila['monto'];

            $gastosEstimados[] = array('label' => $fila['nombre'], 'y' => $estimado);
            $gastosReales[] = array('label' => $fila['nombre'], 'y' => $real);

            $presupuestoTotal += $estimado;
            $gastoTotal += $real;
        }

        $rowVentas = $this->consultarUno("
            SELECT SUM(total) monto
            FROM ventas
            WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
              AND MONTH(fecha) = MONTH(CURRENT_DATE())
        ");
        $totalVentasBrutas = empty($rowVentas['monto']) ? 0.0 : (float) $rowVentas['monto'];

        $totalGananciaVentas = 0.0;
        $totalCostoCompras = 0.0;
        $totalCostoComprasEstimado = 1.0;
        $porcentajeIngresos = 0.0;
        $porcentajeCostos = 0.0;

        if ($totalVentasBrutas > 0) {
            $totalCostoCompras = $totalVentasBrutas * 0.7;
            $totalGananciaVentas = $totalVentasBrutas - $totalCostoCompras;
            $totalCostoComprasEstimado = (100 * $presupuestoTotal) / 30;

            $porcentajeIngresos = $presupuestoTotal > 0 ? round(($totalGananciaVentas * 100) / $presupuestoTotal) : 0.0;
            $porcentajeCostos = $totalCostoComprasEstimado > 0 ? round(($totalCostoCompras * 100) / $totalCostoComprasEstimado) : 0.0;
        }

        $porcentajeGastos = $presupuestoTotal > 0 ? round(($gastoTotal * 100) / $presupuestoTotal) : 0.0;
        $porcentajeCoberturaGastos = $gastoTotal > 0 ? round(($totalGananciaVentas * 100) / $gastoTotal) : 0.0;

        $rowCompras = $this->consultarUno("
            SELECT SUM(precio_costo * cantidad) monto
            FROM compras
            WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
              AND MONTH(fecha) = MONTH(CURRENT_DATE())
        ");
        $totalCompras = empty($rowCompras['monto']) ? 1.0 : (float) $rowCompras['monto'];

        $porcentajeCostosCompras = $totalCostoComprasEstimado > 0 ? round(($totalCompras * 100) / $totalCostoComprasEstimado) : 0.0;
        $porcentajeCoberturaCompras = $totalCompras > 0 ? round(($totalCostoCompras * 100) / $totalCompras) : 0.0;

        // Tarjetas por clase de gasto: reutiliza las mismas filas (mismo ORDER BY) que la
        // agregacion de arriba, replicando el acumulador de saldo disponible del original.
        $hoy = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
        $diaHoy = (int) $hoy->format('d');

        $gastosPDO = new \Gastos();
        $saldoDisponible = $saldoCuentas;
        $tarjetasGasto = array();

        foreach ($filasGastosClase as $fila) {
            $presupuesto = empty($fila['presupuesto']) ? 0.0 : (float) $fila['presupuesto'];
            $claseGasto = (int) $fila['id'];
            $diaPago = (int) $fila['dia_pago'];

            if ($diaPago > $diaHoy) {
                $fechaBase = new \DateTime(date('Y-m', strtotime('- 1 month')) . '-' . $diaPago);
            } else {
                $fechaBase = new \DateTime(date('Y-m', strtotime('now')) . '-' . $diaPago);
            }

            $ahora = new \DateTime();
            $diasPasados = $fechaBase->diff($ahora)->days;
            $porcentajeIdeal = ($diasPasados * 100) / 30;

            $pagado = 0.0;
            $montoPagado = $gastosPDO->getTotalByClaseAndDate($claseGasto, $fechaBase->format('Y-m-d'));
            if (!is_null($montoPagado) && !empty($montoPagado['monto'])) {
                $pagado = (float) $montoPagado['monto'];
            }

            $pagoDiario = $presupuesto / 30;
            $pagoIdeal = $diasPasados * $pagoDiario;

            $saldoDisponible += $pagado;
            if ($saldoDisponible > $pagoIdeal) {
                $porcentajeReal = $porcentajeIdeal;
                $saldoDisponible -= $pagoIdeal;
            } elseif ($saldoDisponible > 0) {
                $porcentajeReal = $presupuesto > 0 ? ($saldoDisponible * 100) / $presupuesto : 0.0;
                $saldoDisponible = 0.0;
            } else {
                $porcentajeReal = 0.0;
            }

            if ($presupuesto > 0 && $pagado >= $presupuesto) {
                $porcentajePagado = 100.0;
                $porcentajeReal = 100.0;
            } elseif ($presupuesto > 0 && $pagado > 0) {
                $porcentajePagado = ($pagado * 100) / $presupuesto;
            } else {
                $porcentajePagado = 0.0;
            }

            $tarjetasGasto[] = array(
                'nombre' => $fila['nombre'],
                'presupuesto' => round($presupuesto, 2),
                'diasPasados' => $diasPasados,
                'porcentajeIdeal' => round($porcentajeIdeal),
                'porcentajeReal' => round($porcentajeReal),
                'porcentajePagado' => round($porcentajePagado),
            );
        }

        $porcentajeDisponibleCompras = 0.0;
        if ($saldoDisponible > 0 && $saldoCuentas > 0) {
            $porcentajeDisponibleCompras = round(($saldoDisponible * 100) / $saldoCuentas);
        }
        $porcentajeDisponibleGastos = 100 - $porcentajeDisponibleCompras;

        $stockFilas = $this->consultarTodos("
            SELECT
                ps.id,
                ps.nombre,
                ps.cupo_maximo,
                ps.cupo_minimo,
                (SELECT SUM(p.stock) FROM productos p WHERE p.id_serie = ps.id) AS stock
            FROM productos_serie ps
        ");

        $stockMaximo = array();
        $stockMinimo = array();
        $stockActual = array();
        foreach ($stockFilas as $fila) {
            $label = $fila['nombre'];
            $stockMaximo[] = array('label' => $label, 'y' => empty($fila['cupo_maximo']) ? 0.0 : (float) $fila['cupo_maximo']);
            $stockMinimo[] = array('label' => $label, 'y' => empty($fila['cupo_minimo']) ? 0.0 : (float) $fila['cupo_minimo']);
            $stockActual[] = array('label' => $label, 'y' => empty($fila['stock']) ? 0.0 : (float) $fila['stock']);
        }

        return array(
            'saldoCuentas' => round($saldoCuentas, 2),
            'tarjetasGasto' => $tarjetasGasto,
            'porcentajeDisponibleGastos' => $porcentajeDisponibleGastos,
            'porcentajeDisponibleCompras' => $porcentajeDisponibleCompras,
            'porcentajeIngresos' => $porcentajeIngresos,
            'porcentajeGastos' => $porcentajeGastos,
            'porcentajeCoberturaGastos' => $porcentajeCoberturaGastos,
            'porcentajeCostos' => $porcentajeCostos,
            'porcentajeCostosCompras' => $porcentajeCostosCompras,
            'porcentajeCoberturaCompras' => $porcentajeCoberturaCompras,
            'gastosEstimados' => $gastosEstimados,
            'gastosReales' => $gastosReales,
            'stockMaximo' => $stockMaximo,
            'stockMinimo' => $stockMinimo,
            'stockActual' => $stockActual,
        );
    }

    private function consultarUno($sql, $parametros = array())
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parametros);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? array() : $row;
    }

    private function consultarTodos($sql, $parametros = array())
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parametros);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
