<?php

namespace App\Integraciones\Fudo;

final class ObtenerCuentasACobrarFudo
{
    private $client;

    public function __construct(FudoClient $client)
    {
        $this->client = $client;
    }

    public function ejecutar(\DateTimeImmutable $desde, \DateTimeImmutable $hasta)
    {
        $zonaLocal = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $zonaUtc = new \DateTimeZone('UTC');
        $resultadoPendientes = $this->client->obtenerPagosPendientes();
        $resultadoARecibir = $this->client->obtenerPagosARecibir($desde->setTimezone($zonaUtc));
        $pagos = array();
        $metodosPago = $resultadoPendientes['metodosPago'] + $resultadoARecibir['metodosPago'];

        foreach (array_merge($resultadoPendientes['pagos'], $resultadoARecibir['pagos']) as $pago) {
            $idPago = isset($pago['id']) ? (string) $pago['id'] : '';
            if ($idPago === '') {
                continue;
            }
            $pagos[$idPago] = $pago;
        }

        $total = 0.0;
        $cantidadPagos = 0;
        $metodosIncluidos = array();
        $totalesPorMes = array();
        $items = array();

        foreach ($pagos as $pago) {
            $atributos = isset($pago['attributes']) && is_array($pago['attributes'])
                ? $pago['attributes']
                : array();
            $fechaPagoTexto = '';
            if (!empty($atributos['receivedBy'])) {
                $fechaPagoTexto = (string) $atributos['receivedBy'];
            } elseif (!empty($atributos['expectedBy'])) {
                $fechaPagoTexto = (string) $atributos['expectedBy'];
            }

            if ($fechaPagoTexto === '') {
                continue;
            }

            $fechaPago = new \DateTimeImmutable($fechaPagoTexto);
            $fechaPago = $fechaPago->setTimezone($zonaLocal);
            if ($fechaPago <= $desde || $fechaPago >= $hasta) {
                continue;
            }

            $metodoId = isset($pago['relationships']['paymentMethod']['data']['id'])
                ? (string) $pago['relationships']['paymentMethod']['data']['id']
                : '';
            $metodo = isset($metodosPago[$metodoId]) ? $metodosPago[$metodoId] : array();

            if (!$this->esMetodoACobrar($metodo)) {
                continue;
            }

            $monto = isset($atributos['receivedAmount']) && $atributos['receivedAmount'] !== null
                ? (float) $atributos['receivedAmount']
                : (isset($atributos['amount']) ? (float) $atributos['amount'] : 0.0);
            $total += $monto;
            $cantidadPagos++;
            $mes = $fechaPago->format('Y-m');
            if (!isset($totalesPorMes[$mes])) {
                $totalesPorMes[$mes] = 0.0;
            }
            $totalesPorMes[$mes] += $monto;
            $items[] = array(
                'fecha' => $fechaPago->format('Y-m-d H:i:s'),
                'monto' => $monto,
            );

            $nombreMetodo = isset($metodo['name']) ? (string) $metodo['name'] : $metodoId;
            if ($nombreMetodo !== '') {
                $metodosIncluidos[$nombreMetodo] = true;
            }
        }

        return array(
            'total' => round($total, 2),
            'cantidadPagos' => $cantidadPagos,
            'metodosIncluidos' => array_keys($metodosIncluidos),
            'totalesPorMes' => array_map(function ($valor) {
                return round((float) $valor, 2);
            }, $totalesPorMes),
            'items' => $items,
        );
    }

    private function esMetodoACobrar($metodo)
    {
        $nombre = isset($metodo['name']) ? mb_strtolower((string) $metodo['name'], 'UTF-8') : '';
        $codigo = isset($metodo['code']) ? mb_strtolower((string) $metodo['code'], 'UTF-8') : '';
        $texto = $nombre . ' ' . $codigo;

        return strpos($texto, 'fudopagos') !== false || strpos($texto, 'fudo pagos') !== false;
    }
}
