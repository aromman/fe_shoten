<?php

namespace App\Turnos\Domain;

final class ConteoBilletes
{
    const DENOMINACIONES = array(10, 20, 50, 100, 200, 500, 1000, 2000, 10000, 20000);

    public static function calcular($cantidades)
    {
        if (!is_array($cantidades)) {
            throw new \InvalidArgumentException('CONTEO_BILLETES_INVALIDO');
        }

        $total = 0;
        $normalizado = array();

        foreach (self::DENOMINACIONES as $denominacion) {
            $clave = (string) $denominacion;
            $cantidad = isset($cantidades[$clave]) ? $cantidades[$clave] : 0;

            if (filter_var($cantidad, FILTER_VALIDATE_INT) === false || (int) $cantidad < 0) {
                throw new \InvalidArgumentException('CANTIDAD_BILLETES_INVALIDA');
            }

            $cantidad = (int) $cantidad;
            $normalizado[$clave] = $cantidad;
            $total += $denominacion * $cantidad;
        }

        return array(
            'cantidades' => $normalizado,
            'total' => round((float) $total, 2),
        );
    }
}
