<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 3) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 3) . '/app/Finanzas/FinanzasBootstrap.php';

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
    $resultado = FinanzasBootstrap::obtenerResumen()->ejecutar($canalId);
    JsonResponse::enviar(array(
        'data' => $resultado,
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
