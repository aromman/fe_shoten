<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/producto.php';
require_once dirname(__DIR__, 2) . '/app/models/inventarioCostoMantenimientoConfig.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($rol !== 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTORIZADO')), 403);
}

$modeloConfig = new InventarioCostoMantenimientoConfig();

$accionesTasa = array(
    'guardarTasaMantenimiento' => 'guardarTasaAnual',
    'guardarTasaInteres' => 'guardarTasaInteresAnual',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? (string) $_POST['accion'] : '';
    if (!isset($accionesTasa[$accion])) {
        JsonResponse::enviar(array('error' => array('codigo' => 'ACCION_INVALIDA')), 400);
    }

    $tasa = isset($_POST['tasaAnual']) ? str_replace(',', '.', (string) $_POST['tasaAnual']) : '';
    if ($tasa === '' || !is_numeric($tasa) || (float) $tasa < 0 || (float) $tasa > 1000) {
        JsonResponse::enviar(array('error' => array('codigo' => 'DATOS_INVALIDOS')), 400);
    }

    $metodo = $accionesTasa[$accion];
    if (!$modeloConfig->$metodo((float) $tasa)) {
        JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_GUARDADO')), 500);
    }

    JsonResponse::enviar(array('data' => array('tasaAnual' => round((float) $tasa, 2))), 200);
}

try {
    $modeloProducto = new Producto();
    $config = $modeloConfig->obtener();
    $tasaMantenimientoAnual = isset($config['tasa_anual']) ? (float) $config['tasa_anual'] : 0;
    $tasaInteresAnual = isset($config['tasa_interes_anual']) ? (float) $config['tasa_interes_anual'] : 0;
    $tasaTotalAnual = $tasaMantenimientoAnual + $tasaInteresAnual;

    $resumen = $modeloProducto->getResumenValorInventarioCosto();
    $valorCosto = is_array($resumen) && isset($resumen['valorCosto']) ? (float) $resumen['valorCosto'] : 0;
    $stockTotal = is_array($resumen) && isset($resumen['stock']) ? (float) $resumen['stock'] : 0;
    $cantidadProductos = is_array($resumen) && isset($resumen['cantidadProductos']) ? (int) $resumen['cantidadProductos'] : 0;

    $costoMantenimientoAnual = round($valorCosto * $tasaMantenimientoAnual / 100, 2);
    $costoInteresAnual = round($valorCosto * $tasaInteresAnual / 100, 2);

    $filasEditorial = $modeloProducto->getValorInventarioCostoPorEditorial();
    $porEditorial = array_map(function ($fila) use ($tasaMantenimientoAnual, $tasaInteresAnual, $valorCosto) {
        $filaValorCosto = round((float) $fila['valorCosto'], 2);
        $filaCostoMantenimientoAnual = round($filaValorCosto * $tasaMantenimientoAnual / 100, 2);
        $filaCostoInteresAnual = round($filaValorCosto * $tasaInteresAnual / 100, 2);

        return array(
            'editorial' => isset($fila['editorial']) && $fila['editorial'] !== '' ? $fila['editorial'] : 'Sin editorial',
            'stock' => round((float) $fila['stock'], 2),
            'valorCosto' => $filaValorCosto,
            'costoMantenimientoMensual' => round($filaCostoMantenimientoAnual / 12, 2),
            'costoInteresMensual' => round($filaCostoInteresAnual / 12, 2),
            'costoTotalMensual' => round(($filaCostoMantenimientoAnual + $filaCostoInteresAnual) / 12, 2),
            'participacion' => $valorCosto > 0 ? round(($filaValorCosto * 100) / $valorCosto, 2) : 0,
        );
    }, is_array($filasEditorial) ? $filasEditorial : array());

    JsonResponse::enviar(array(
        'data' => array(
            'tasaAnual' => round($tasaMantenimientoAnual, 2),
            'tasaInteresAnual' => round($tasaInteresAnual, 2),
            'resumen' => array(
                'cantidadProductos' => $cantidadProductos,
                'stock' => round($stockTotal, 2),
                'valorCosto' => round($valorCosto, 2),
                'costoMantenimientoMensual' => round($costoMantenimientoAnual / 12, 2),
                'costoMantenimientoAnual' => $costoMantenimientoAnual,
                'costoInteresMensual' => round($costoInteresAnual / 12, 2),
                'costoInteresAnual' => $costoInteresAnual,
                'costoTotalMensual' => round(($costoMantenimientoAnual + $costoInteresAnual) / 12, 2),
                'costoTotalAnual' => round($costoMantenimientoAnual + $costoInteresAnual, 2),
            ),
            'porEditorial' => $porEditorial,
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
