<?php

namespace App\Integraciones\Fudo;

final class ObtenerGastosFudo
{
    private $client;

    public function __construct(FudoClient $client)
    {
        $this->client = $client;
    }

    public function ejecutar(\DateTimeImmutable $desde, \DateTimeImmutable $hasta)
    {
        $financialCategoryPorId = $this->obtenerFinancialCategoryPorId();

        $resultado = $this->client->obtenerGastosEntreFechas($desde, $hasta);
        $gastos = isset($resultado['gastos']) && is_array($resultado['gastos']) ? $resultado['gastos'] : array();
        $categoriasGasto = isset($resultado['categoriasGasto']) && is_array($resultado['categoriasGasto'])
            ? $resultado['categoriasGasto']
            : array();

        $total = 0.0;
        $porClase = array();
        $totalImpuestos = 0.0;
        $porClaseImpuestos = array();

        foreach ($gastos as $gasto) {
            $atributos = isset($gasto['attributes']) && is_array($gasto['attributes'])
                ? $gasto['attributes']
                : array();
            if (empty($atributos['date'])) {
                continue;
            }

            $fecha = new \DateTimeImmutable((string) $atributos['date']);
            if ($fecha < $desde || $fecha >= $hasta) {
                continue;
            }

            $monto = isset($atributos['amount']) ? (float) $atributos['amount'] : 0.0;
            if ($monto <= 0) {
                continue;
            }

            $categoriaId = isset($gasto['relationships']['expenseCategory']['data']['id'])
                ? (string) $gasto['relationships']['expenseCategory']['data']['id']
                : '';

            // categoria financiera "Compra de mercaderia" (GOODS-PURCHASES): ya se contabiliza
            // como costo de ventas (costo por producto vendido), no debe duplicarse en gastos.
            $financialCategory = isset($financialCategoryPorId[$categoriaId]) ? $financialCategoryPorId[$categoriaId] : '';
            if ($financialCategory === 'GOODS-PURCHASES') {
                continue;
            }

            $categoria = isset($categoriasGasto[$categoriaId]) ? $categoriasGasto[$categoriaId] : array();
            $nombreCategoria = isset($categoria['name']) && $categoria['name'] !== ''
                ? (string) $categoria['name']
                : 'Sin clasificar';

            // categoria financiera "Gastos Administrativos" (ADMINISTRATIVE-EXPENSES): se reporta
            // aparte, en el totalizador de Impuestos, y se excluye del total de gastos operativos.
            if ($financialCategory === 'ADMINISTRATIVE-EXPENSES') {
                if (!isset($porClaseImpuestos[$nombreCategoria])) {
                    $porClaseImpuestos[$nombreCategoria] = 0.0;
                }
                $porClaseImpuestos[$nombreCategoria] += $monto;
                $totalImpuestos += $monto;
                continue;
            }

            if (!isset($porClase[$nombreCategoria])) {
                $porClase[$nombreCategoria] = 0.0;
            }
            $porClase[$nombreCategoria] += $monto;
            $total += $monto;
        }

        return array(
            'total' => round($total, 2),
            'porClase' => array_map(function ($valor) {
                return round((float) $valor, 2);
            }, $porClase),
            'totalImpuestos' => round($totalImpuestos, 2),
            'porClaseImpuestos' => array_map(function ($valor) {
                return round((float) $valor, 2);
            }, $porClaseImpuestos),
        );
    }

    private function obtenerFinancialCategoryPorId()
    {
        $resultado = $this->client->obtenerCategoriasGasto();
        $categorias = isset($resultado['data']) && is_array($resultado['data']) ? $resultado['data'] : array();
        $porId = array();

        foreach ($categorias as $categoria) {
            $id = isset($categoria['id']) ? (string) $categoria['id'] : '';
            if ($id === '') {
                continue;
            }
            $atributos = isset($categoria['attributes']) && is_array($categoria['attributes'])
                ? $categoria['attributes']
                : array();
            $porId[$id] = isset($atributos['financialCategory']) ? (string) $atributos['financialCategory'] : '';
        }

        return $porId;
    }
}
