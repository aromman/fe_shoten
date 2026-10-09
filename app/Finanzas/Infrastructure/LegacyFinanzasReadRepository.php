<?php

namespace App\Finanzas\Infrastructure;

use App\Finanzas\Application\FinanzasReadRepository;

final class LegacyFinanzasReadRepository extends \Connection implements FinanzasReadRepository
{
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->conexion();
        $this->pdo = $this->conexion();
    }

    public function obtenerResumen($canalId)
    {
        $canalId = (int) $canalId;
        $actual = $this->obtenerPeriodo($canalId, 0);
        $anterior = $this->obtenerPeriodo($canalId, -1);
        $presupuesto = $this->obtenerPresupuesto($canalId);
        $saldos = $this->obtenerSaldosClasificados();

        $actual['gananciaBruta'] = round($actual['ventas'] - $actual['costoVentas'], 2);
        $actual['resultado'] = round($actual['gananciaBruta'] - $actual['gastos'], 2);
        $actual['margenBruto'] = $actual['ventas'] > 0
            ? ($actual['gananciaBruta'] * 100) / $actual['ventas']
            : 0;
        $actual['ejecucionPresupuesto'] = $presupuesto > 0
            ? ($actual['gastos'] * 100) / $presupuesto
            : 0;
        $actual['rentabilidad'] = $actual['ventas'] > 0
            ? ($actual['resultado'] * 100) / $actual['ventas']
            : 0;
        $actual['puntoEquilibrio'] = $actual['margenBruto'] > 0
            ? $presupuesto / ($actual['margenBruto'] / 100)
            : 0;
        $actual['margenBruto'] = round($actual['margenBruto'], 2);
        $actual['ejecucionPresupuesto'] = round($actual['ejecucionPresupuesto'], 2);
        $actual['rentabilidad'] = round($actual['rentabilidad'], 2);
        $actual['puntoEquilibrio'] = round($actual['puntoEquilibrio'], 2);

        $anterior['gananciaBruta'] = round($anterior['ventas'] - $anterior['costoVentas'], 2);
        $anterior['resultado'] = round($anterior['gananciaBruta'] - $anterior['gastos'], 2);
        $anterior['margenBruto'] = $anterior['ventas'] > 0
            ? round(($anterior['gananciaBruta'] * 100) / $anterior['ventas'], 2)
            : 0;
        $anterior['rentabilidad'] = $anterior['ventas'] > 0
            ? round(($anterior['resultado'] * 100) / $anterior['ventas'], 2)
            : 0;
        $anterior['puntoEquilibrio'] = $anterior['margenBruto'] > 0
            ? round($presupuesto / ($anterior['margenBruto'] / 100), 2)
            : 0;

        return array(
            'canalId' => $canalId,
            'periodo' => date('Y-m'),
            'actual' => $actual,
            'anterior' => $anterior,
            'presupuesto' => $presupuesto,
            'saldoCuentas' => $saldos['disponibilidades'],
            'disponibilidades' => $saldos['disponibilidades'],
            'dineroPorCobrar' => $saldos['dineroPorCobrar'],
            'dineroPorPagar' => $saldos['dineroPorPagar'],
            'evolucion' => $this->obtenerEvolucion($canalId),
        );
    }

    private function obtenerPeriodo($canalId, $desplazamientoMes)
    {
        $inicio = new \DateTimeImmutable('first day of this month');
        if ($desplazamientoMes !== 0) {
            $inicio = $inicio->modify($desplazamientoMes . ' month');
        }
        $fin = $inicio->modify('+1 month');

        $ventasSql = "
            SELECT
                COALESCE(SUM(total), 0) AS ventas,
                COALESCE(SUM(unidades * costo), 0) AS costo_ventas,
                COALESCE(SUM(descuento), 0) AS descuentos,
                COUNT(DISTINCT id_ventas_header) AS operaciones
            FROM ventas
            WHERE id_canal = :canal
              AND fecha >= :inicio
              AND fecha < :fin
        ";
        $ventas = $this->consultarUno($ventasSql, array(
            ':canal' => $canalId,
            ':inicio' => $inicio->format('Y-m-d'),
            ':fin' => $fin->format('Y-m-d'),
        ));

        $gastosSql = "
            SELECT COALESCE(SUM(monto), 0) AS gastos
            FROM gastos
            WHERE id_canal = :canal
              AND fecha >= :inicio
              AND fecha < :fin
        ";
        $gastos = $this->consultarUno($gastosSql, array(
            ':canal' => $canalId,
            ':inicio' => $inicio->format('Y-m-d'),
            ':fin' => $fin->format('Y-m-d'),
        ));

        return array(
            'ventas' => round((float) $ventas['ventas'], 2),
            'costoVentas' => round((float) $ventas['costo_ventas'], 2),
            'descuentos' => round((float) $ventas['descuentos'], 2),
            'operaciones' => (int) $ventas['operaciones'],
            'gastos' => round((float) $gastos['gastos'], 2),
        );
    }

    private function obtenerPresupuesto($canalId)
    {
        $row = $this->consultarUno(
            'SELECT COALESCE(SUM(monto), 0) AS total FROM presupuesto WHERE id_canal = :canal',
            array(':canal' => $canalId)
        );

        return round((float) $row['total'], 2);
    }

    private function obtenerSaldosClasificados()
    {
        $rows = $this->consultarTodos("
            SELECT
                c.ambito_uso,
                c.tipo,
                c.tipo_saldo,
                COALESCE(SUM(
                c.saldo_inicial
                + (SELECT COALESCE(SUM(credito.monto), 0)
                   FROM cuentas_movimientos credito
                   WHERE credito.id_cuenta = c.id AND credito.tipo_movimiento = 'C')
                - (SELECT COALESCE(SUM(debito.monto), 0)
                   FROM cuentas_movimientos debito
                   WHERE debito.id_cuenta = c.id AND debito.tipo_movimiento = 'D')
                ), 0) AS total
            FROM cuentas c
            WHERE c.activa = 1
              AND (
                    (c.ambito_uso = 'COMERCIAL' AND c.tipo = 'A')
                    OR (c.ambito_uso IN ('PERSONAL', 'EMPLEADO') AND c.tipo = 'P' AND c.tipo_saldo = 'D')
              )
            GROUP BY c.ambito_uso, c.tipo, c.tipo_saldo
        ");

        $saldos = array(
            'disponibilidades' => 0.0,
            'dineroPorCobrar' => 0.0,
            'dineroPorPagar' => 0.0,
        );

        foreach ($rows as $row) {
            $total = (float) $row['total'];

            if ($row['ambito_uso'] === 'COMERCIAL') {
                if (in_array($row['tipo_saldo'], array('E', 'B', 'P'), true)) {
                    $saldos['disponibilidades'] += $total;
                }
                continue;
            }

            // Cuentas Pasivo de Deuda en Personal/Empleado: saldo negativo = nos deben (cobrar),
            // saldo positivo = debemos (pagar). El monto se muestra siempre en positivo.
            if ($total < 0) {
                $saldos['dineroPorCobrar'] += abs($total);
            } elseif ($total > 0) {
                $saldos['dineroPorPagar'] += $total;
            }
        }

        return array_map(function ($valor) {
            return round($valor, 2);
        }, $saldos);
    }

    private function obtenerEvolucion($canalId)
    {
        $inicio = new \DateTimeImmutable('first day of January this year');
        $fin = $inicio->modify('+1 year');
        $meses = array();

        for ($mes = 1; $mes <= 12; $mes++) {
            $clave = sprintf('%04d-%02d', (int) $inicio->format('Y'), $mes);
            $meses[$clave] = array(
                'mes' => $clave,
                'ventas' => 0.0,
                'costoVentas' => 0.0,
                'gastos' => 0.0,
                'resultado' => 0.0,
            );
        }

        $ventas = $this->consultarTodos("
            SELECT
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                COALESCE(SUM(total), 0) AS ventas,
                COALESCE(SUM(unidades * costo), 0) AS costo_ventas
            FROM ventas
            WHERE id_canal = :canal
              AND fecha >= :inicio
              AND fecha < :fin
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ", array(
            ':canal' => $canalId,
            ':inicio' => $inicio->format('Y-m-d'),
            ':fin' => $fin->format('Y-m-d'),
        ));

        foreach ($ventas as $row) {
            if (isset($meses[$row['mes']])) {
                $meses[$row['mes']]['ventas'] = round((float) $row['ventas'], 2);
                $meses[$row['mes']]['costoVentas'] = round((float) $row['costo_ventas'], 2);
            }
        }

        $gastos = $this->consultarTodos("
            SELECT
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                COALESCE(SUM(monto), 0) AS gastos
            FROM gastos
            WHERE id_canal = :canal
              AND fecha >= :inicio
              AND fecha < :fin
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ", array(
            ':canal' => $canalId,
            ':inicio' => $inicio->format('Y-m-d'),
            ':fin' => $fin->format('Y-m-d'),
        ));

        foreach ($gastos as $row) {
            if (isset($meses[$row['mes']])) {
                $meses[$row['mes']]['gastos'] = round((float) $row['gastos'], 2);
            }
        }

        foreach ($meses as &$mes) {
            $mes['resultado'] = round($mes['ventas'] - $mes['costoVentas'] - $mes['gastos'], 2);
        }
        unset($mes);

        return array_values($meses);
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
