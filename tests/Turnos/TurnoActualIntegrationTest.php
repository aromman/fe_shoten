<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/TurnosBootstrap.php';

use App\Turnos\TurnosBootstrap;

$canalId = isset($argv[1]) ? (int) $argv[1] : 1;
$resultado = TurnosBootstrap::obtenerTurnoActual()->ejecutar($canalId);

if (!isset($resultado['canal'], $resultado['resumen'], $resultado['movimientos'], $resultado['billetes'])) {
    throw new RuntimeException('El contrato de turno actual está incompleto.');
}

if ((int) $resultado['canal']['id'] !== $canalId) {
    throw new RuntimeException('El canal de la respuesta no coincide con el solicitado.');
}

foreach ($resultado['movimientos'] as $indice => $movimiento) {
    if (!array_key_exists('saldo', $movimiento)) {
        throw new RuntimeException('Cada movimiento debe incluir su saldo acumulado.');
    }

    $hoy = (new DateTimeImmutable(
        'now',
        new DateTimeZone('America/Argentina/Buenos_Aires')
    ))->format('Y-m-d');
    $esSaldoInicial = isset($movimiento['tipo']) && $movimiento['tipo'] === 'S';
    $fechaEsperada = $esSaldoInicial
        ? (new DateTimeImmutable($hoy))->modify('-1 day')->format('Y-m-d')
        : $hoy;
    if (substr($movimiento['fecha'], 0, 10) !== $fechaEsperada) {
        throw new RuntimeException('La grilla debe incluir movimientos de hoy y el saldo inicial con fecha de ayer.');
    }

    if ($indice > 0 && strcmp($resultado['movimientos'][$indice - 1]['fecha'], $movimiento['fecha']) < 0) {
        throw new RuntimeException('Los movimientos deben estar ordenados por fecha descendente.');
    }
}

if (!empty($resultado['movimientos'])
    && abs($resultado['movimientos'][0]['saldo'] - $resultado['resumen']['saldoEsperado']) > 0.001
) {
    throw new RuntimeException('El saldo del movimiento más reciente debe coincidir con el saldo esperado.');
}

echo json_encode(array(
    'canal' => $resultado['canal'],
    'turno' => $resultado['turno'],
    'resumen' => $resultado['resumen'],
    'cantidadMovimientos' => count($resultado['movimientos']),
    'cantidadDenominaciones' => count($resultado['billetes']),
), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
