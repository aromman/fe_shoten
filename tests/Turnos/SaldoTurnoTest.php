<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/SaldoTurno.php';

use App\Turnos\Domain\SaldoTurno;

function assertSameMoney($esperado, $actual, $mensaje)
{
    if (abs($esperado - $actual) > 0.001) {
        throw new RuntimeException($mensaje . ': esperado ' . $esperado . ', obtenido ' . $actual);
    }
}

$resultado = SaldoTurno::calcular(array(
    'apertura' => 10000,
    'ventas' => 25000.50,
    'depositos' => 5000,
    'compras' => 4000,
    'gastos' => 2500.25,
    'extracciones' => 1000,
));

assertSameMoney(30000.50, $resultado['ingresos'], 'Los ingresos deben sumar ventas y depósitos');
assertSameMoney(7500.25, $resultado['egresos'], 'Los egresos deben sumar compras, gastos y extracciones');
assertSameMoney(32500.25, $resultado['saldoEsperado'], 'El saldo esperado no coincide');

$vacio = SaldoTurno::calcular(array());
assertSameMoney(0, $vacio['saldoEsperado'], 'Los montos ausentes deben valer cero');

echo "SaldoTurnoTest OK\n";
