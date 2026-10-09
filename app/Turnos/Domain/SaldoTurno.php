<?php

namespace App\Turnos\Domain;

final class SaldoTurno
{
    public static function calcular(array $montos)
    {
        $apertura = self::monto($montos, 'apertura');
        $ventas = self::monto($montos, 'ventas');
        $depositos = self::monto($montos, 'depositos');
        $compras = self::monto($montos, 'compras');
        $gastos = self::monto($montos, 'gastos');
        $extracciones = self::monto($montos, 'extracciones');
        $ingresos = $ventas + $depositos;
        $egresos = $compras + $gastos + $extracciones;

        return array(
            'apertura' => $apertura,
            'ventas' => $ventas,
            'depositos' => $depositos,
            'compras' => $compras,
            'gastos' => $gastos,
            'extracciones' => $extracciones,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldoEsperado' => $apertura + $ingresos - $egresos,
        );
    }

    private static function monto(array $montos, $clave)
    {
        if (!isset($montos[$clave]) || !is_numeric($montos[$clave])) {
            return 0.0;
        }

        return round((float) $montos[$clave], 2);
    }
}
