<?php

namespace App\Integraciones\Fudo;

final class ObtenerPromedioSueldosFudo
{
    private $client;

    public function __construct(FudoClient $client)
    {
        $this->client = $client;
    }

    public function ejecutar(\DateTimeImmutable $desde, \DateTimeImmutable $hasta)
    {
        $resultado = $this->client->obtenerGastosEntreFechas($desde, $hasta);
        $gastos = isset($resultado['gastos']) ? $resultado['gastos'] : array();
        $categoriasGasto = isset($resultado['categoriasGasto']) ? $resultado['categoriasGasto'] : array();
        $totalesPorMes = array();
        $total = 0.0;
        $cantidadGastos = 0;

        foreach ($gastos as $gasto) {
            $atributos = isset($gasto['attributes']) && is_array($gasto['attributes'])
                ? $gasto['attributes']
                : array();
            $categoriaId = isset($gasto['relationships']['expenseCategory']['data']['id'])
                ? (string) $gasto['relationships']['expenseCategory']['data']['id']
                : '';
            $categoria = isset($categoriasGasto[$categoriaId]) ? $categoriasGasto[$categoriaId] : array();
            $nombreCategoria = isset($categoria['name']) ? (string) $categoria['name'] : '';

            if (!$this->esCategoriaSueldo($nombreCategoria) || empty($atributos['date'])) {
                continue;
            }

            $monto = isset($atributos['amount']) ? (float) $atributos['amount'] : 0.0;
            if ($monto <= 0) {
                continue;
            }

            $mes = (new \DateTimeImmutable((string) $atributos['date']))->format('Y-m');
            if (!isset($totalesPorMes[$mes])) {
                $totalesPorMes[$mes] = 0.0;
            }

            $totalesPorMes[$mes] += $monto;
            $total += $monto;
            $cantidadGastos++;
        }

        $mesesConOperatoria = 0;
        foreach ($totalesPorMes as $montoMes) {
            if ((float) $montoMes > 0) {
                $mesesConOperatoria++;
            }
        }

        return array(
            'promedio' => $mesesConOperatoria > 0 ? $total / $mesesConOperatoria : 0,
            'total' => round($total, 2),
            'cantidadGastos' => $cantidadGastos,
            'mesesConOperatoria' => $mesesConOperatoria,
            'totalesPorMes' => array_map(function ($valor) {
                return round((float) $valor, 2);
            }, $totalesPorMes),
        );
    }

    private function esCategoriaSueldo($nombreCategoria)
    {
        $nombre = trim(mb_strtolower($nombreCategoria, 'UTF-8'));

        return $nombre === 'sueldo' || $nombre === 'sueldos';
    }
}
