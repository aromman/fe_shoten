<?php

namespace App\Liquidaciones\Infrastructure;

require_once __DIR__ . '/../../models/inversiones.php';
require_once __DIR__ . '/../../models/inversores.php';

final class LegacyLiquidacionReadRepository extends \Connection
{
    private $pdo;
    private $inversionesPDO;
    private $inversoresPDO;

    public function __construct()
    {
        parent::__construct();
        $this->conexion();
        $this->pdo = $this->conexion();
        $this->inversionesPDO = new \Inversiones();
        $this->inversoresPDO = new \Inversores();
    }

    public function obtenerResumen()
    {
        // Deuda del mes corriente por inversor (ids hardcodeados 1=Ale, 2=Flor, igual que el original).
        $deudaMesAle = 0.0;
        $deudaMesFlor = 0.0;
        $totalDeudasMes = 0.0;

        $filasDeuda = $this->consultarTodos("
            SELECT
                i.id,
                i.nombre,
                (c.saldo_inicial
                    + (SELECT SUM(cmc.monto) FROM cuentas_movimientos cmc WHERE cmc.id_cuenta = c.id AND cmc.tipo_movimiento='C')
                    - (SELECT SUM(cmd.monto) FROM cuentas_movimientos cmd WHERE cmd.id_cuenta = c.id AND cmd.tipo_movimiento='D')
                ) * -1 AS deuda
            FROM cuentas c
            INNER JOIN inversores i ON i.id_cuenta = c.id
            WHERE c.tipo = 'P'
        ");

        foreach ($filasDeuda as $fila) {
            $deuda = (float) $fila['deuda'];
            if ($deuda > 0) {
                if ((int) $fila['id'] === 1) {
                    $deudaMesAle += $deuda;
                    $totalDeudasMes += $deudaMesAle;
                } elseif ((int) $fila['id'] === 2) {
                    $deudaMesFlor += $deuda;
                    $totalDeudasMes += $deudaMesFlor;
                }
            }
        }

        $porcentajeDeudaMesAle = 0.0;
        $porcentajeDeudaMesFlor = 0.0;
        if ($totalDeudasMes > 0) {
            $porcentajeDeudaMesAle = ($deudaMesAle * 100) / $totalDeudasMes;
            $porcentajeDeudaMesFlor = 100 - $porcentajeDeudaMesAle;
        }

        // Cuentas de tipo Activo.
        $filasCuentas = $this->consultarTodos("
            SELECT
                c.nombre,
                (c.saldo_inicial
                    + (SELECT SUM(cmc.monto) FROM cuentas_movimientos cmc WHERE cmc.id_cuenta = c.id AND cmc.tipo_movimiento='C')
                    - (SELECT SUM(cmd.monto) FROM cuentas_movimientos cmd WHERE cmd.id_cuenta = c.id AND cmd.tipo_movimiento='D')
                ) AS saldo
            FROM cuentas c
            WHERE c.tipo = 'A'
        ");

        $cuentas = array();
        $totalCuentas = 0.0;
        foreach ($filasCuentas as $fila) {
            $saldo = isset($fila['saldo']) ? (float) $fila['saldo'] : 0.0;
            $cuentas[] = array('nombre' => $fila['nombre'], 'saldo' => round($saldo, 2));
            $totalCuentas += $saldo;
        }

        // Costos y gastos (presupuesto total + stockeo fijo, igual que el original).
        $filasCostos = $this->consultarTodos("
            SELECT 'Presupuesto' nombre, SUM(presupuesto) total FROM gastos_clase
            UNION
            SELECT 'Stockeo' nombre, 60 * 5900 total
        ");

        $costos = array();
        $totalCostos = 0.0;
        foreach ($filasCostos as $fila) {
            $total = isset($fila['total']) ? (float) $fila['total'] : 0.0;
            $costos[] = array('nombre' => $fila['nombre'], 'total' => round($total, 2));
            $totalCostos += $total;
        }

        // Distribucion del saldo disponible.
        if ($totalCuentas > $totalCostos) {
            $netoCuentas = $totalCuentas - $totalCostos;
            $disponiblePresupuesto = $netoCuentas * 70 / 100;
            $disponibleDeudas = $netoCuentas * 20 / 100;
            $disponibleDividendos = $netoCuentas - $disponiblePresupuesto - $disponibleDeudas;
        } else {
            $disponiblePresupuesto = $totalCuentas;
            $disponibleDeudas = 0.0;
            $disponibleDividendos = 0.0;
        }

        // Pago de deudas del mes corriente.
        if ($totalDeudasMes > 0) {
            $disponibleDeudasAle = $disponibleDeudas * $porcentajeDeudaMesAle / 100;
            $disponibleDeudasFlor = $disponibleDeudas - $disponibleDeudasAle;

            $pagoDeudaAle = $disponibleDeudasAle > $deudaMesAle ? $deudaMesAle : $disponibleDeudasAle;
            $pagoDeudaFlor = $disponibleDeudasFlor > $deudaMesFlor ? $deudaMesFlor : $disponibleDeudasFlor;
        } else {
            $pagoDeudaAle = 0.0;
            $pagoDeudaFlor = 0.0;
        }

        $disponibleInversiones = $disponibleDeudas - $pagoDeudaAle - $pagoDeudaFlor;

        // Inversiones.
        $cotizacion = 1.0;
        $rsCotizacion = $this->inversionesPDO->getCotizacion();
        if (!is_null($rsCotizacion) && !empty($rsCotizacion['cotizacion'])) {
            $cotizacion = (float) $rsCotizacion['cotizacion'];
        }

        $totalInvertido = 0.0;
        $rsTotal = $this->inversionesPDO->getTotal();
        if (!is_null($rsTotal) && !empty($rsTotal['total'])) {
            $totalInvertido = (float) $rsTotal['total'] * $cotizacion;
        }

        $inversores = $this->inversoresPDO->getAll('nombre');
        $inversiones = array();
        $pagoDisponibleInversiones = $disponibleInversiones;

        foreach ($inversores as $inversor) {
            $rsInversor = $this->inversionesPDO->getByInversor($inversor['id']);
            if (is_null($rsInversor)) {
                continue;
            }

            $cantidadLibros = isset($rsInversor['total']) ? (float) $rsInversor['total'] : 0.0;
            $valorizado = $cantidadLibros * $cotizacion;
            $porcentaje = $totalInvertido > 0 ? ($valorizado * 100) / $totalInvertido : 0.0;

            if ($pagoDisponibleInversiones > 0) {
                $pagarInversor = $pagoDisponibleInversiones * $porcentaje / 100;
                $pagoDisponibleInversiones -= $pagarInversor;
            } else {
                $pagarInversor = 0.0;
            }

            $inversiones[] = array(
                'nombre' => $inversor['nombre'],
                'porcentaje' => round($porcentaje),
                'valorizado' => round($valorizado, 2),
                'pagar' => round($pagarInversor, 2),
            );
        }

        // Dividendos.
        $dividendosAle = $disponibleDividendos / 2;
        $dividendosFlor = $disponibleDividendos - $dividendosAle;

        return array(
            'cuentas' => $cuentas,
            'totalCuentas' => round($totalCuentas, 2),
            'costos' => $costos,
            'totalCostos' => round($totalCostos, 2),
            'disponiblePresupuesto' => round($disponiblePresupuesto, 2),
            'disponibleDeudas' => round($disponibleDeudas, 2),
            'disponibleDividendos' => round($disponibleDividendos, 2),
            'deudaMes' => array(
                'total' => round($totalDeudasMes, 2),
                'ale' => round($deudaMesAle, 2),
                'flor' => round($deudaMesFlor, 2),
                'porcentajeAle' => round($porcentajeDeudaMesAle),
                'porcentajeFlor' => round($porcentajeDeudaMesFlor),
                'pagoAle' => round($pagoDeudaAle, 2),
                'pagoFlor' => round($pagoDeudaFlor, 2),
            ),
            'disponibleInversiones' => round($disponibleInversiones, 2),
            'inversiones' => $inversiones,
            'dividendos' => array(
                'total' => round($disponibleDividendos, 2),
                'ale' => round($dividendosAle, 2),
                'flor' => round($dividendosFlor, 2),
            ),
        );
    }

    private function consultarTodos($sql, $parametros = array())
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parametros);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
