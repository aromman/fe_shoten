<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/turnos.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$canalId = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
if ($canalId <= 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CANAL_REQUERIDO')), 400);
}

try {
    $turnosPDO = new Turnos();
    $turnoActivo = $turnosPDO->getActiveByCanal($canalId);
    $fechaDesde = $turnoActivo ? $turnoActivo['fecha'] : '1970-01-01';

    $cuentasMovimientosPDO = new CuentasMovimientos();
    $movsCafeteria = $cuentasMovimientosPDO->getAllByCausalCanalAndDate(5, $canalId, $fechaDesde);
    $movsCafeteria = is_array($movsCafeteria) ? $movsCafeteria : array();

    $total = 0.0;
    $movimientos = array();
    foreach ($movsCafeteria as $mov) {
        $monto = round((float) $mov['monto'], 2);
        $total += $monto;
        $movimientos[] = array(
            'fecha' => $mov['fecha'],
            'descripcion' => $mov['descripcion'],
            'monto' => $monto,
        );
    }

    JsonResponse::enviar(array(
        'data' => array(
            'total' => round($total, 2),
            'movimientos' => $movimientos,
        ),
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
