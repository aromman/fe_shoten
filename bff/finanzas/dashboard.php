<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/Finanzas/FinanzasBootstrap.php';

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

try {
    $resumen = FinanzasBootstrap::obtenerResumen()->ejecutar($canalId);
    $actual = $resumen['actual'];
    $anterior = $resumen['anterior'];
    $ventasLocalesActual = $actual['ventas'];

    $variacion = function ($valorActual, $valorAnterior) {
        $valorActual = (float) $valorActual;
        $valorAnterior = (float) $valorAnterior;

        if ($valorAnterior == 0.0) {
            return $valorActual == 0.0 ? 0.0 : null;
        }

        return round((($valorActual - $valorAnterior) * 100) / abs($valorAnterior), 2);
    };

    $cumplimientoEquilibrio = $actual['puntoEquilibrio'] > 0
        ? round(($actual['ventas'] * 100) / $actual['puntoEquilibrio'], 2)
        : null;
    $semaforoFacturacion = 'secondary';
    if ($cumplimientoEquilibrio !== null) {
        if ($cumplimientoEquilibrio >= 100) {
            $semaforoFacturacion = 'success';
        } elseif ($cumplimientoEquilibrio >= 90) {
            $semaforoFacturacion = 'warning';
        } else {
            $semaforoFacturacion = 'danger';
        }
    }

    $semaforoMargen = 'danger';
    if ($actual['margenBruto'] >= 60) {
        $semaforoMargen = 'success';
    } elseif ($actual['margenBruto'] >= 50) {
        $semaforoMargen = 'warning';
    }

    $semaforoRentabilidad = 'danger';
    if ($actual['rentabilidad'] >= 15) {
        $semaforoRentabilidad = 'success';
    } elseif ($actual['rentabilidad'] >= 5) {
        $semaforoRentabilidad = 'warning';
    }

    $coberturaDeudas = $resumen['dineroPorPagar'] > 0
        ? round(($resumen['disponibilidades'] * 100) / $resumen['dineroPorPagar'], 2)
        : null;
    $semaforoDisponibilidades = 'success';
    if ($resumen['dineroPorPagar'] > 0) {
        if ($resumen['disponibilidades'] > $resumen['dineroPorPagar']) {
            $semaforoDisponibilidades = 'success';
        } elseif ($coberturaDeudas >= 75) {
            $semaforoDisponibilidades = 'warning';
        } else {
            $semaforoDisponibilidades = 'danger';
        }
    }

    $porcentajePorCobrar = $actual['ventas'] > 0
        ? round(($resumen['dineroPorCobrar'] * 100) / $actual['ventas'], 2)
        : null;
    $semaforoPorCobrar = 'success';
    if ($porcentajePorCobrar !== null) {
        if ($porcentajePorCobrar > 30) {
            $semaforoPorCobrar = 'danger';
        } elseif ($porcentajePorCobrar >= 15) {
            $semaforoPorCobrar = 'warning';
        }
    } elseif ($resumen['dineroPorCobrar'] > 0) {
        $semaforoPorCobrar = 'danger';
    }

    $recursosParaPagar = $resumen['disponibilidades'] + $resumen['dineroPorCobrar'];
    $coberturaPorPagar = $resumen['dineroPorPagar'] > 0
        ? round(($recursosParaPagar * 100) / $resumen['dineroPorPagar'], 2)
        : null;
    $semaforoPorPagar = 'success';
    if ($resumen['dineroPorPagar'] > 0) {
        if ($recursosParaPagar > $resumen['dineroPorPagar']) {
            $semaforoPorPagar = 'success';
        } elseif ($coberturaPorPagar >= 75) {
            $semaforoPorPagar = 'warning';
        } else {
            $semaforoPorPagar = 'danger';
        }
    }

    $posicionFinancieraReal = round(
        $resumen['disponibilidades']
        + $resumen['dineroPorCobrar']
        - $resumen['dineroPorPagar'],
        2
    );

    $tarjetas = array(
        array(
            'clave' => 'ventas',
            'titulo' => 'Facturación total',
            'valor' => $actual['ventas'],
            'formato' => 'moneda',
            'variacion' => $variacion($actual['ventas'], $anterior['ventas']),
            'tono' => 'success',
            'icono' => 'fa-chart-line',
            'semaforo' => array(
                'tono' => $semaforoFacturacion,
                'porcentaje' => $cumplimientoEquilibrio,
                'descripcion' => 'del punto de equilibrio',
            ),
        ),
        array(
            'clave' => 'margen',
            'titulo' => 'Margen bruto',
            'valor' => $actual['margenBruto'],
            'formato' => 'porcentaje',
            'variacion' => null,
            'tono' => 'primary',
            'icono' => 'fa-percent',
            'semaforo' => array(
                'tono' => $semaforoMargen,
                'porcentaje' => $actual['margenBruto'],
                'descripcion' => 'de margen bruto',
            ),
        ),
        array(
            'clave' => 'rentabilidad',
            'titulo' => 'Rentabilidad',
            'valor' => $actual['rentabilidad'],
            'formato' => 'porcentaje',
            'variacion' => null,
            'tono' => $actual['rentabilidad'] >= 0 ? 'info' : 'danger',
            'icono' => 'fa-arrow-trend-up',
            'semaforo' => array(
                'tono' => $semaforoRentabilidad,
                'porcentaje' => $actual['rentabilidad'],
                'descripcion' => 'de rentabilidad',
            ),
        ),
        array(
            'clave' => 'punto-equilibrio',
            'titulo' => 'Punto de equilibrio',
            'valor' => $actual['puntoEquilibrio'],
            'formato' => 'moneda',
            'variacion' => $variacion($actual['puntoEquilibrio'], $anterior['puntoEquilibrio']),
            'tendenciaInversa' => true,
            'tono' => 'warning',
            'icono' => 'fa-scale-balanced',
            'semaforo' => array(
                'tono' => $semaforoFacturacion,
                'porcentaje' => $cumplimientoEquilibrio,
                'descripcion' => 'alcanzado',
            ),
        ),
        array(
            'clave' => 'disponibilidades',
            'titulo' => 'Disponibilidades',
            'valor' => $resumen['disponibilidades'],
            'formato' => 'moneda',
            'variacion' => null,
            'tono' => 'success',
            'icono' => 'fa-wallet',
            'semaforo' => array(
                'tono' => $semaforoDisponibilidades,
                'porcentaje' => $coberturaDeudas,
                'descripcion' => 'de cobertura de pagos',
            ),
        ),
        array(
            'clave' => 'dinero-cobrar',
            'titulo' => 'Dinero por cobrar',
            'valor' => $resumen['dineroPorCobrar'],
            'formato' => 'moneda',
            'variacion' => null,
            'tono' => 'primary',
            'icono' => 'fa-hand-holding-dollar',
            'semaforo' => array(
                'tono' => $semaforoPorCobrar,
                'porcentaje' => $porcentajePorCobrar,
                'descripcion' => 'de la facturación',
            ),
        ),
        array(
            'clave' => 'dinero-pagar',
            'titulo' => 'Dinero por pagar',
            'valor' => $resumen['dineroPorPagar'],
            'formato' => 'moneda',
            'variacion' => null,
            'tono' => 'danger',
            'icono' => 'fa-file-invoice-dollar',
            'semaforo' => array(
                'tono' => $semaforoPorPagar,
                'porcentaje' => $coberturaPorPagar,
                'descripcion' => 'de cobertura disponible',
            ),
        ),
        array(
            'clave' => 'posicion-financiera-real',
            'titulo' => 'Posición Financiera Real',
            'valor' => $posicionFinancieraReal,
            'formato' => 'moneda',
            'variacion' => null,
            'tono' => $posicionFinancieraReal >= 0 ? 'success' : 'danger',
            'icono' => 'fa-chart-pie',
        ),
    );

    $evolucion = $resumen['evolucion'];
    JsonResponse::enviar(array(
        'data' => array(
            'periodo' => $resumen['periodo'],
            'tarjetas' => $tarjetas,
            'detalle' => array(
                'facturacion' => $actual['ventas'],
                'facturacionLocal' => $ventasLocalesActual,
                'costoVentas' => $actual['costoVentas'],
                'costosFijos' => $actual['gastos'],
                'gananciaBruta' => $actual['gananciaBruta'],
                'descuentos' => $actual['descuentos'],
                'operaciones' => $actual['operaciones'],
                'presupuesto' => $resumen['presupuesto'],
                'ejecucionPresupuesto' => $actual['ejecucionPresupuesto'],
                'saldoCuentas' => $resumen['saldoCuentas'],
            ),
            'grafico' => array(
                'etiquetas' => array_map(function ($mes) {
                    return $mes['mes'];
                }, $evolucion),
                'series' => array(
                    array(
                        'clave' => 'ventas',
                        'titulo' => 'Ventas locales',
                        'valores' => array_map(function ($mes) {
                            return $mes['ventas'];
                        }, $evolucion),
                    ),
                    array(
                        'clave' => 'resultado',
                        'titulo' => 'Resultado',
                        'valores' => array_map(function ($mes) {
                            return $mes['resultado'];
                        }, $evolucion),
                    ),
                ),
            ),
            'acciones' => array(
                array('titulo' => 'Rentabilidad', 'url' => '/rentabilidad/rentabilidad.php', 'icono' => 'fa-arrow-trend-up'),
                array('titulo' => 'Flujo de caja', 'url' => '/cashflow/flujo-caja-12m.php', 'icono' => 'fa-chart-line'),
                array('titulo' => 'Presupuesto', 'url' => '/presupuesto/dashboard.php', 'icono' => 'fa-gauge-high'),
                array('titulo' => 'Cuentas', 'url' => '/cuentas/cuentas.php', 'icono' => 'fa-building-columns'),
            ),
        ),
        'meta' => array(
            'version' => 'v1',
            'actualizadoEn' => date(DATE_ATOM),
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
