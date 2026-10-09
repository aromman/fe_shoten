<?php

session_start();

require_once dirname(__DIR__, 3) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 3) . '/app/Turnos/TurnosBootstrap.php';

use App\Http\JsonResponse;
use App\Turnos\TurnosBootstrap;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$canalId = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
if ($canalId <= 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CANAL_REQUERIDO')), 400);
}

try {
    $resultado = TurnosBootstrap::obtenerTurnoActual()->ejecutar($canalId);
    JsonResponse::enviar(array(
        'data' => $resultado,
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
