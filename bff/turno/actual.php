<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/TurnosBootstrap.php';

use App\Http\JsonResponse;
use App\Turnos\TurnosBootstrap;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$canalId = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($canalId <= 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CANAL_REQUERIDO')), 400);
}

try {
    $resultado = TurnosBootstrap::obtenerTurnoActual()->ejecutar($canalId);
    $resumen = $resultado['resumen'];

    JsonResponse::enviar(array(
        'data' => array(
            'canal' => $resultado['canal'],
            'turno' => $resultado['turno'],
            'tarjetas' => array(
                array('clave' => 'saldo', 'titulo' => 'Saldo esperado', 'monto' => $resumen['saldoEsperado'], 'tono' => 'primary'),
                array('clave' => 'apertura', 'titulo' => 'Apertura', 'monto' => $resumen['apertura'], 'tono' => 'info'),
                array('clave' => 'ventas', 'titulo' => 'Ventas en efectivo', 'monto' => $resumen['ventas'], 'tono' => 'success'),
                array('clave' => 'ingresos', 'titulo' => 'Otros ingresos', 'monto' => $resumen['depositos'], 'tono' => 'warning'),
                array('clave' => 'egresos', 'titulo' => 'Egresos', 'monto' => $resumen['egresos'], 'tono' => 'danger'),
            ),
            'desglose' => $resumen,
            'movimientos' => $resultado['movimientos'],
            'billetes' => $resultado['billetes'],
            'arqueos' => array_slice($resultado['arqueos'], 0, 5),
            'acciones' => array(
                'puedeAbrir' => $resultado['turno'] === null && $rol === 0,
                'puedeArqueo' => $resultado['turno'] !== null,
                'puedeCerrar' => $resultado['turno'] !== null && $rol === 0,
            ),
            'modo' => 'lectura-y-apertura',
        ),
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
