<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/Finanzas/FinanzasBootstrap.php';
require_once dirname(__DIR__, 2) . '/app/models/gastos.php';

use App\Finanzas\FinanzasBootstrap;
use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($rol !== 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTORIZADO')), 403);
}

$canalId = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
if ($canalId <= 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CANAL_REQUERIDO')), 400);
}

$formatearPeriodo = function ($periodo) {
    $meses = array(
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    );
    $partes = explode('-', (string) $periodo);
    if (count($partes) !== 2 || !isset($meses[$partes[1]])) {
        return (string) $periodo;
    }

    return $meses[$partes[1]] . ' ' . $partes[0];
};

$variacion = function ($valorActual, $valorAnterior) {
    $valorActual = (float) $valorActual;
    $valorAnterior = (float) $valorAnterior;

    if ($valorAnterior == 0.0) {
        return $valorActual == 0.0 ? 0.0 : null;
    }

    return round((($valorActual - $valorAnterior) * 100) / abs($valorAnterior), 2);
};

try {
    $modeloGastos = new Gastos();
    $resumen = FinanzasBootstrap::obtenerResumen()->ejecutar($canalId);
    $actual = $resumen['actual'];
    $anterior = $resumen['anterior'];

    $zona = new DateTimeZone('America/Argentina/Buenos_Aires');
    $inicioActual = (new DateTimeImmutable('first day of this month', $zona))->setTime(0, 0);
    $finActual = $inicioActual->modify('+1 month');
    $inicioAnterior = $inicioActual->modify('-1 month');
    $finAnterior = $inicioActual;

    $gastosPorClaseActual = $modeloGastos->getResumenPorClaseCanalPeriodo(
        $canalId,
        $inicioActual->format('Y-m-d'),
        $finActual->format('Y-m-d')
    );
    $gastosPorClaseAnterior = $modeloGastos->getResumenPorClaseCanalPeriodo(
        $canalId,
        $inicioAnterior->format('Y-m-d'),
        $finAnterior->format('Y-m-d')
    );

    $normalizarGastosPorClase = function ($filas) {
        return array_map(function ($fila) {
            return array(
                'clase' => isset($fila['clase']) && $fila['clase'] !== '' ? $fila['clase'] : 'Sin clasificar',
                'monto' => round((float) $fila['monto'], 2),
            );
        }, $filas);
    };

    $armarPeriodo = function ($periodo, $gastosPorClase) use ($normalizarGastosPorClase) {
        $ventasTotal = round((float) $periodo['ventas'], 2);
        $costoVentas = round((float) $periodo['costoVentas'], 2);
        $gananciaBruta = round($ventasTotal - $costoVentas, 2);
        $gastos = round((float) $periodo['gastos'], 2);
        $resultado = round($gananciaBruta - $gastos, 2);
        $margenBruto = $ventasTotal > 0 ? round(($gananciaBruta * 100) / $ventasTotal, 2) : 0;
        // Rentabilidad se calcula sobre el Resultado Neto final (resultado operativo menos
        // gastos financieros -aun sin implementar, 0-), no sobre el operativo.
        $gastosFinancieros = 0.0;
        $resultadoNeto = round($resultado - $gastosFinancieros, 2);
        $rentabilidad = $ventasTotal > 0 ? round(($resultadoNeto * 100) / $ventasTotal, 2) : 0;

        return array(
            'ventasLocal' => $ventasTotal,
            'ventas' => $ventasTotal,
            'costoVentasLocal' => $costoVentas,
            'costoVentas' => $costoVentas,
            'gananciaBruta' => $gananciaBruta,
            'margenBruto' => $margenBruto,
            'gastosLocal' => $gastos,
            'gastos' => $gastos,
            'gastosPorClase' => $normalizarGastosPorClase($gastosPorClase),
            'resultado' => $resultado,
            'rentabilidad' => $rentabilidad,
            'descuentos' => round((float) $periodo['descuentos'], 2),
            'operaciones' => (int) $periodo['operaciones'],
        );
    };

    $periodoActual = $armarPeriodo($actual, $gastosPorClaseActual);
    $periodoAnterior = $armarPeriodo($anterior, $gastosPorClaseAnterior);

    $variaciones = array(
        'ventas' => $variacion($periodoActual['ventas'], $periodoAnterior['ventas']),
        'costoVentas' => $variacion($periodoActual['costoVentas'], $periodoAnterior['costoVentas']),
        'gananciaBruta' => $variacion($periodoActual['gananciaBruta'], $periodoAnterior['gananciaBruta']),
        'gastos' => $variacion($periodoActual['gastos'], $periodoAnterior['gastos']),
        'resultado' => $variacion($periodoActual['resultado'], $periodoAnterior['resultado']),
    );

    $evolucion = array_map(function ($mes) {
        $ventas = round((float) $mes['ventas'], 2);
        $costoVentas = round((float) $mes['costoVentas'], 2);
        $gastos = round((float) $mes['gastos'], 2);

        return array(
            'mes' => $mes['mes'],
            'ventas' => $ventas,
            'costoVentas' => $costoVentas,
            'gastos' => $gastos,
            'resultado' => round($ventas - $costoVentas - $gastos, 2),
        );
    }, $resumen['evolucion']);

    JsonResponse::enviar(array(
        'data' => array(
            'periodo' => $resumen['periodo'],
            'periodoEtiqueta' => $formatearPeriodo($resumen['periodo']),
            'periodoAnteriorEtiqueta' => $formatearPeriodo($inicioAnterior->format('Y-m')),
            'actual' => $periodoActual,
            'anterior' => $periodoAnterior,
            'variaciones' => $variaciones,
            'evolucion' => $evolucion,
            'presupuesto' => $resumen['presupuesto'],
        ),
        'meta' => array(
            'version' => 'v1',
            'actualizadoEn' => date(DATE_ATOM),
        ),
    ), 200);
} catch (Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
