<?php

namespace App\Integraciones\Fudo;

final class ObtenerGastosAPagarFudo
{
    private $client;

    public function __construct(FudoClient $client)
    {
        $this->client = $client;
    }

    public function ejecutar(\DateTimeImmutable $desde, \DateTimeImmutable $hasta)
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $resultado = $this->client->obtenerGastosAPagar($desde, $hasta);
        $gastos = isset($resultado['gastos']) ? $resultado['gastos'] : array();
        $categoriasGasto = isset($resultado['categoriasGasto']) ? $resultado['categoriasGasto'] : array();
        $total = 0.0;
        $cantidadGastos = 0;
        $totalesPorMes = array();
        $items = array();

        foreach ($gastos as $gasto) {
            $atributos = isset($gasto['attributes']) && is_array($gasto['attributes'])
                ? $gasto['attributes']
                : array();
            $categoriaId = isset($gasto['relationships']['expenseCategory']['data']['id'])
                ? (string) $gasto['relationships']['expenseCategory']['data']['id']
                : '';
            $categoria = isset($categoriasGasto[$categoriaId]) ? $categoriasGasto[$categoriaId] : array();
            $nombreCategoria = isset($categoria['name']) ? (string) $categoria['name'] : '';
            if ($this->esCategoriaSueldo($nombreCategoria)) {
                continue;
            }

            $fechaPagoTexto = !empty($atributos['dueDate']) ? (string) $atributos['dueDate'] : '';
            if ($fechaPagoTexto === '' && !empty($atributos['date'])) {
                $fechaPagoTexto = (string) $atributos['date'];
            }
            if ($fechaPagoTexto === '') {
                continue;
            }

            $fechaPago = new \DateTimeImmutable($fechaPagoTexto, $zonaLocal);
            if ($fechaPago < $desde || $fechaPago >= $hasta) {
                continue;
            }

            $monto = isset($atributos['amount']) ? (float) $atributos['amount'] : 0.0;
            if ($monto <= 0) {
                continue;
            }

            $total += $monto;
            $cantidadGastos++;
            $mes = $fechaPago->format('Y-m');
            if (!isset($totalesPorMes[$mes])) {
                $totalesPorMes[$mes] = 0.0;
            }
            $totalesPorMes[$mes] += $monto;
            $items[] = array(
                'fecha' => $fechaPago->format('Y-m-d H:i:s'),
                'monto' => $monto,
                'concepto' => isset($atributos['description']) ? (string) $atributos['description'] : '',
                'categoria' => $nombreCategoria,
            );
        }

        return array(
            'total' => round($total, 2),
            'cantidadGastos' => $cantidadGastos,
            'totalesPorMes' => array_map(function ($valor) {
                return round((float) $valor, 2);
            }, $totalesPorMes),
            'items' => $items,
        );
    }

    private function esCategoriaSueldo($nombreCategoria)
    {
        $nombre = trim(mb_strtolower($nombreCategoria, 'UTF-8'));

        return $nombre === 'sueldos';
    }
}
