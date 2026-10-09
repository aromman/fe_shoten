<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/rentabilidad.php';
require_once dirname(__DIR__, 2) . '/app/models/presupuesto.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$idCanal = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
$anio = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');
if ($anio < 2000 || $anio > 2100) {
    JsonResponse::enviar(array('error' => array('codigo' => 'ANIO_INVALIDO')), 400);
}

$mesesNombre = array(
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
);

$sumarMatriz = function ($matriz) {
    $totales = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0);
    foreach ($matriz as $meses) {
        foreach ($meses as $mes => $valor) {
            $totales[$mes] += (float) $valor;
        }
    }

    return $totales;
};

$aFilas = function ($matriz, $campoEtiqueta) {
    $filas = array();
    foreach ($matriz as $etiqueta => $meses) {
        $filas[] = array(
            $campoEtiqueta => $etiqueta,
            'meses' => $meses,
            'total' => round(array_sum($meses), 2),
        );
    }

    return $filas;
};

try {
    $modelo = new Rentabilidad();
    $presupuestoModelo = new Presupuesto();

    $ventasPorCanal = $modelo->getVentasPorCanalYMes($anio);
    $ventasPorMes = $sumarMatriz($ventasPorCanal);
    $descuentosPorMes = $modelo->getDescuentosPorMes($anio);
    $gastosFijosPorClase = $modelo->getGastosFijosPorClaseYMes($anio);
    $gastosFijosPorMes = $sumarMatriz($gastosFijosPorClase);
    $ajustesStockPorMes = $modelo->getAjustesStockPorMes($anio);

    $meses = array();
    $totalesMes = array(
        'ventas' => 0, 'costoEstimadoVentas' => 0, 'ajustesStock' => 0, 'descuentos' => 0,
        'costoVentas' => 0, 'margenBruto' => 0, 'gastosFijos' => 0, 'utilidad' => 0,
    );

    for ($mes = 1; $mes <= 12; $mes++) {
        $ventas = round((float) $ventasPorMes[$mes], 2);
        $costoEstimadoVentas = round($ventas * 0.5, 2);
        $ajustesStock = round((float) $ajustesStockPorMes[$mes], 2);
        $descuentos = round((float) $descuentosPorMes[$mes], 2);
        $costoVentas = round($costoEstimadoVentas + $ajustesStock + $descuentos, 2);
        $margenBruto = round($ventas - $costoVentas, 2);
        $gastosFijos = round((float) $gastosFijosPorMes[$mes], 2);
        $utilidad = round($margenBruto - $gastosFijos, 2);
        $productividad = $ventas > 0 ? round(($utilidad * 100) / $ventas, 2) : 0;
        $gastosSobreVentas = $ventas > 0 ? round(($gastosFijos * 100) / $ventas, 2) : 0;

        $meses[] = array(
            'mes' => $mes,
            'etiqueta' => $mesesNombre[$mes],
            'ventas' => $ventas,
            'costoEstimadoVentas' => $costoEstimadoVentas,
            'ajustesStock' => $ajustesStock,
            'descuentos' => $descuentos,
            'costoVentas' => $costoVentas,
            'margenBruto' => $margenBruto,
            'gastosFijos' => $gastosFijos,
            'utilidad' => $utilidad,
            'productividad' => $productividad,
            'gastosSobreVentas' => $gastosSobreVentas,
        );

        $totalesMes['ventas'] += $ventas;
        $totalesMes['costoEstimadoVentas'] += $costoEstimadoVentas;
        $totalesMes['ajustesStock'] += $ajustesStock;
        $totalesMes['descuentos'] += $descuentos;
        $totalesMes['costoVentas'] += $costoVentas;
        $totalesMes['margenBruto'] += $margenBruto;
        $totalesMes['gastosFijos'] += $gastosFijos;
        $totalesMes['utilidad'] += $utilidad;
    }

    $totalesMes['productividad'] = $totalesMes['ventas'] > 0
        ? round(($totalesMes['utilidad'] * 100) / $totalesMes['ventas'], 2)
        : 0;
    $totalesMes['gastosSobreVentas'] = $totalesMes['ventas'] > 0
        ? round(($totalesMes['gastosFijos'] * 100) / $totalesMes['ventas'], 2)
        : 0;
    foreach ($totalesMes as $clave => $valor) {
        if ($clave !== 'productividad' && $clave !== 'gastosSobreVentas') {
            $totalesMes[$clave] = round($valor, 2);
        }
    }

    $resumenMesActual = $modelo->getResumenMesActual();
    $ventasMes = round((float) $resumenMesActual['total_ventas'], 2);
    $costoVentasMes = round((float) $resumenMesActual['total_costo'], 2);
    $porcentajeCostoVentas = $ventasMes > 0 ? round(($costoVentasMes * 100) / $ventasMes, 2) : 0;
    $gananciaBrutaMes = round($ventasMes - $costoVentasMes, 2);
    $porcentajeGananciaBruta = $ventasMes > 0 ? round(($gananciaBrutaMes * 100) / $ventasMes, 2) : 0;
    $gastosFijosProyectados = round((float) $presupuestoModelo->getTotalByCanal($idCanal), 2);
    $gananciaNetaMes = round($gananciaBrutaMes - $gastosFijosProyectados, 2);
    $porcentajeGananciaNeta = $ventasMes > 0 ? round(($gananciaNetaMes * 100) / $ventasMes, 2) : 0;

    $anioActual = (int) date('Y');
    $mesActual = $anio === $anioActual ? (int) date('n') : 12;

    // Proyeccion segun ventas actuales
    $mesesObjetivo = 12;
    $montoObjetivo = $mesesObjetivo * 1000000;
    $montoCostoProyVentas = round($ventasMes * 0.45, 2);
    $gananciaBrutaProyVentas = round($ventasMes - $montoCostoProyVentas, 2);
    $gananciaNetaProyVentas = round($gananciaBrutaProyVentas - $gastosFijosProyectados, 2);
    $objetivoProyVentas = round($gananciaNetaProyVentas * $mesesObjetivo, 2);

    // Proyeccion segun meta de ganancias
    $gananciaNetaProyGanancia = round($montoObjetivo / $mesesObjetivo, 2);
    $gananciaBrutaProyGanancia = round($gananciaNetaProyGanancia + $gastosFijosProyectados, 2);
    $montoCostoProyGanancia = round(($gananciaBrutaProyGanancia * 45) / 55, 2);
    $ventasProyGanancia = round($gananciaBrutaProyGanancia + $montoCostoProyGanancia, 2);

    JsonResponse::enviar(array(
        'data' => array(
            'anio' => $anio,
            'totales' => array(
                'ventasMes' => $ventasMes,
                'costoVentasMes' => $costoVentasMes,
                'porcentajeCostoVentas' => $porcentajeCostoVentas,
                'gananciaBrutaMes' => $gananciaBrutaMes,
                'porcentajeGananciaBruta' => $porcentajeGananciaBruta,
                'gastosFijosProyectados' => $gastosFijosProyectados,
                'gananciaNetaMes' => $gananciaNetaMes,
                'porcentajeGananciaNeta' => $porcentajeGananciaNeta,
            ),
            'mesActual' => $mesActual,
            'meses' => $meses,
            'totalesAnio' => $totalesMes,
            'ventasPorCanal' => $aFilas($ventasPorCanal, 'canal'),
            'gastosFijosPorClase' => $aFilas($gastosFijosPorClase, 'clase'),
            'proyeccionSegunVentas' => array(
                'mesesObjetivo' => $mesesObjetivo,
                'montoObjetivo' => $montoObjetivo,
                'ventas' => $ventasMes,
                'costo' => $montoCostoProyVentas,
                'gananciaBruta' => $gananciaBrutaProyVentas,
                'gastosFijos' => $gastosFijosProyectados,
                'gananciaNeta' => $gananciaNetaProyVentas,
                'objetivoGanancias' => $objetivoProyVentas,
                'cumplido' => $objetivoProyVentas > $montoObjetivo,
            ),
            'proyeccionSegunGanancia' => array(
                'mesesObjetivo' => $mesesObjetivo,
                'montoObjetivo' => $montoObjetivo,
                'ventas' => $ventasProyGanancia,
                'costo' => $montoCostoProyGanancia,
                'gananciaBruta' => $gananciaBrutaProyGanancia,
                'gastosFijos' => $gastosFijosProyectados,
                'gananciaNeta' => $gananciaNetaProyGanancia,
                'objetivoVentas' => $ventasMes,
                'cumplido' => $ventasMes > $ventasProyGanancia,
            ),
        ),
        'meta' => array(
            'version' => 'v1',
            'actualizadoEn' => date(DATE_ATOM),
        ),
    ), 200);
} catch (Throwable $error) {
    error_log('Rentabilidad bff: ' . $error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
