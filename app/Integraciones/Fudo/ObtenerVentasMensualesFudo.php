<?php

namespace App\Integraciones\Fudo;

final class ObtenerVentasMensualesFudo
{
    private $client;

    public function __construct(FudoClient $client)
    {
        $this->client = $client;
    }

    public function ejecutar(\DateTimeImmutable $mes)
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $inicioLocal = $mes->setTimezone($zonaLocal)->modify('first day of this month')->setTime(0, 0);
        $finLocal = $inicioLocal->modify('+1 month');

        return $this->ejecutarPeriodo($inicioLocal, $finLocal, $inicioLocal->format('Y-m'));
    }

    public function ejecutarDia(\DateTimeImmutable $dia)
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $inicioLocal = $dia->setTimezone($zonaLocal)->setTime(0, 0);
        $finLocal = $inicioLocal->modify('+1 day');

        return $this->ejecutarPeriodo($inicioLocal, $finLocal, $inicioLocal->format('Y-m-d'), 'createdAt');
    }

    public function ejecutarRangoDiario(\DateTimeImmutable $desde, \DateTimeImmutable $hasta)
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $zonaUtc = new \DateTimeZone('UTC');
        $inicioLocal = $desde->setTimezone($zonaLocal)->setTime(0, 0);
        $finLocal = $hasta->setTimezone($zonaLocal)->setTime(0, 0)->modify('+1 day');

        $resultado = $this->client->obtenerVentasConItems(
            $inicioLocal->modify('-6 months')->setTimezone($zonaUtc),
            $finLocal->modify('-1 second')->setTimezone($zonaUtc)
        );
        $ventas = isset($resultado['ventas']) && is_array($resultado['ventas'])
            ? $resultado['ventas']
            : array();
        $ventasConItemsConfirmados = $this->obtenerVentasConItemsConfirmados(
            isset($resultado['incluidos']) && is_array($resultado['incluidos']) ? $resultado['incluidos'] : array()
        );

        $porDia = array();
        for ($cursor = $inicioLocal; $cursor < $finLocal; $cursor = $cursor->modify('+1 day')) {
            $porDia[$cursor->format('Y-m-d')] = array(
                'periodo' => $cursor->format('Y-m-d'),
                'total' => 0.0,
                'cantidadVentas' => 0,
                'personas' => 0,
            );
        }

        foreach ($ventas as $venta) {
            $ventaId = isset($venta['id']) ? (string) $venta['id'] : '';
            if ($ventaId === '' || !isset($ventasConItemsConfirmados[$ventaId])) {
                continue;
            }

            $atributos = isset($venta['attributes']) && is_array($venta['attributes'])
                ? $venta['attributes']
                : array();

            if (!$this->ventaEnPeriodo($atributos, $inicioLocal, $finLocal, $zonaLocal, 'closedAt')) {
                continue;
            }

            $fecha = (new \DateTimeImmutable($atributos['closedAt']))->setTimezone($zonaLocal);
            $clave = $fecha->format('Y-m-d');
            if (!isset($porDia[$clave])) {
                continue;
            }

            $porDia[$clave]['cantidadVentas']++;
            $porDia[$clave]['total'] += isset($atributos['total']) ? (float) $atributos['total'] : 0.0;
            $porDia[$clave]['personas'] += $this->extraerPersonas($atributos);
        }

        foreach ($porDia as &$dia) {
            $dia['total'] = round($dia['total'], 2);
        }
        unset($dia);

        return array_values($porDia);
    }

    public function listarDia(\DateTimeImmutable $dia)
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $zonaUtc = new \DateTimeZone('UTC');
        $inicioLocal = $dia->setTimezone($zonaLocal)->setTime(0, 0);
        $finLocal = $inicioLocal->modify('+1 day');
        $resultado = $this->client->obtenerVentasConItems(
            $inicioLocal->modify('-1 day')->setTimezone($zonaUtc),
            $finLocal->modify('-1 second')->setTimezone($zonaUtc)
        );
        $ventas = isset($resultado['ventas']) && is_array($resultado['ventas'])
            ? $resultado['ventas']
            : array();
        $ventasConItemsConfirmados = $this->obtenerVentasConItemsConfirmados(
            isset($resultado['incluidos']) && is_array($resultado['incluidos']) ? $resultado['incluidos'] : array()
        );
        $detalle = array();

        foreach ($ventas as $venta) {
            $ventaId = isset($venta['id']) ? (string) $venta['id'] : '';
            if ($ventaId === '' || !isset($ventasConItemsConfirmados[$ventaId])) {
                continue;
            }

            $atributos = isset($venta['attributes']) && is_array($venta['attributes'])
                ? $venta['attributes']
                : array();
            if (!$this->ventaEnPeriodo($atributos, $inicioLocal, $finLocal, $zonaLocal, 'createdAt')) {
                continue;
            }

            $fecha = new \DateTimeImmutable($atributos['closedAt']);
            $detalle[] = array(
                'id' => $ventaId,
                'hora' => $fecha->setTimezone($zonaLocal)->format('H:i'),
                'estado' => isset($atributos['saleState']) ? (string) $atributos['saleState'] : '',
                'personas' => $this->extraerPersonas($atributos),
                'importe' => isset($atributos['total']) ? round((float) $atributos['total'], 2) : 0,
            );
        }

        usort($detalle, function ($a, $b) {
            return strcmp($a['hora'], $b['hora']);
        });

        return $detalle;
    }

    private function ejecutarPeriodo(\DateTimeImmutable $inicioLocal, \DateTimeImmutable $finLocal, $periodo, $campoFecha = 'closedAt')
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $zonaUtc = new \DateTimeZone('UTC');
        $resultado = $this->client->obtenerVentasConItems(
            $inicioLocal->modify('-6 months')->setTimezone($zonaUtc),
            $finLocal->modify('-1 second')->setTimezone($zonaUtc)
        );
        $ventas = isset($resultado['ventas']) && is_array($resultado['ventas'])
            ? $resultado['ventas']
            : array();
        $incluidos = isset($resultado['incluidos']) && is_array($resultado['incluidos']) ? $resultado['incluidos'] : array();
        $ventasConItemsConfirmados = $this->obtenerVentasConItemsConfirmados($incluidos);
        $itemsPorVenta = $this->agruparItemsConfirmadosPorVenta($incluidos);

        $total = 0.0;
        $personas = 0;
        $cantidadVentas = 0;
        $itemsEnPeriodo = array();
        foreach ($ventas as $venta) {
            $ventaId = isset($venta['id']) ? (string) $venta['id'] : '';
            if ($ventaId === '' || !isset($ventasConItemsConfirmados[$ventaId])) {
                continue;
            }

            $atributos = isset($venta['attributes']) && is_array($venta['attributes'])
                ? $venta['attributes']
                : array();

            if (!$this->ventaEnPeriodo($atributos, $inicioLocal, $finLocal, $zonaLocal, $campoFecha)) {
                continue;
            }

            $cantidadVentas++;
            $total += isset($atributos['total']) ? (float) $atributos['total'] : 0.0;
            $personas += $this->extraerPersonas($atributos);
            if (isset($itemsPorVenta[$ventaId])) {
                $itemsEnPeriodo = array_merge($itemsEnPeriodo, $itemsPorVenta[$ventaId]);
            }
        }

        return array(
            'periodo' => $periodo,
            'total' => round($total, 2),
            'cantidadVentas' => $cantidadVentas,
            'personas' => $personas,
            'costo' => round($this->calcularCostoItems($itemsEnPeriodo), 2),
        );
    }

    /**
     * Costo de mercaderia vendida: por cada item vendido y no cancelado, cantidad x costo
     * del producto (segun Fudo). Fudo no expone un total de costo por venta, asi que hay
     * que recorrer los items y resolver el costo de cada producto involucrado.
     */
    private function calcularCostoItems($items)
    {
        $productIds = array();
        foreach ($items as $item) {
            $productIds[] = $item['productId'];
        }
        $productIds = array_values(array_unique($productIds));
        if (empty($productIds)) {
            return 0.0;
        }

        $costoPorProducto = $this->obtenerCostoPorProducto($productIds);

        $costo = 0.0;
        foreach ($items as $item) {
            $costoUnitario = isset($costoPorProducto[$item['productId']]) ? $costoPorProducto[$item['productId']] : 0.0;
            $costo += $item['quantity'] * $costoUnitario;
        }

        return $costo;
    }

    private function obtenerCostoPorProducto($productIds)
    {
        $resultado = $this->client->obtenerProductosPorIds($productIds);
        $productos = isset($resultado['data']) && is_array($resultado['data']) ? $resultado['data'] : array();
        $costoPorProducto = array();

        foreach ($productos as $producto) {
            $productId = isset($producto['id']) ? (string) $producto['id'] : '';
            if ($productId === '') {
                continue;
            }
            $atributos = isset($producto['attributes']) && is_array($producto['attributes'])
                ? $producto['attributes']
                : array();
            $costoPorProducto[$productId] = isset($atributos['cost']) ? (float) $atributos['cost'] : 0.0;
        }

        return $costoPorProducto;
    }

    private function agruparItemsConfirmadosPorVenta($incluidos)
    {
        $itemsPorVenta = array();

        foreach ($incluidos as $incluido) {
            if (!isset($incluido['type']) || $incluido['type'] !== 'Item') {
                continue;
            }

            $atributos = isset($incluido['attributes']) && is_array($incluido['attributes'])
                ? $incluido['attributes']
                : array();
            if (!$this->itemConfirmado($atributos)) {
                continue;
            }

            $ventaId = isset($incluido['relationships']['sale']['data']['id'])
                ? (string) $incluido['relationships']['sale']['data']['id']
                : '';
            $productId = isset($incluido['relationships']['product']['data']['id'])
                ? (string) $incluido['relationships']['product']['data']['id']
                : '';
            if ($ventaId === '' || $productId === '') {
                continue;
            }

            if (!isset($itemsPorVenta[$ventaId])) {
                $itemsPorVenta[$ventaId] = array();
            }
            $itemsPorVenta[$ventaId][] = array(
                'productId' => $productId,
                'quantity' => isset($atributos['quantity']) ? (float) $atributos['quantity'] : 0.0,
            );
        }

        return $itemsPorVenta;
    }

    private function obtenerVentasConItemsConfirmados($incluidos)
    {
        $ventas = array();

        foreach ($incluidos as $incluido) {
            if (!isset($incluido['type']) || $incluido['type'] !== 'Item') {
                continue;
            }

            $atributos = isset($incluido['attributes']) && is_array($incluido['attributes'])
                ? $incluido['attributes']
                : array();
            if (!$this->itemConfirmado($atributos)) {
                continue;
            }

            $ventaId = isset($incluido['relationships']['sale']['data']['id'])
                ? (string) $incluido['relationships']['sale']['data']['id']
                : '';
            if ($ventaId !== '') {
                $ventas[$ventaId] = true;
            }
        }

        return $ventas;
    }

    private function itemConfirmado($atributos)
    {
        if (!empty($atributos['canceled'])) {
            return false;
        }

        if (isset($atributos['status']) && in_array((string) $atributos['status'], array('CANCELED', 'CANCELLED'), true)) {
            return false;
        }

        return true;
    }

    private function ventaEnPeriodo($atributos, \DateTimeImmutable $inicioLocal, \DateTimeImmutable $finLocal, \DateTimeZone $zonaLocal, $campoFecha)
    {
        if ($campoFecha !== 'createdAt') {
            $campoFecha = 'closedAt';
        }

        if (empty($atributos[$campoFecha])) {
            return false;
        }

        try {
            $fecha = (new \DateTimeImmutable($atributos[$campoFecha]))->setTimezone($zonaLocal);
        } catch (\Exception $exception) {
            return false;
        }

        return $fecha >= $inicioLocal && $fecha < $finLocal;
    }

    private function extraerPersonas($atributos)
    {
        foreach (array('people', 'personas', 'guests', 'guestCount', 'customers', 'customerCount', 'diners', 'covers', 'pax') as $campo) {
            if (isset($atributos[$campo]) && is_numeric($atributos[$campo])) {
                return (int) $atributos[$campo];
            }
        }

        return 0;
    }
}
