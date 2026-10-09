<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/constantes.php';
require_once dirname(__DIR__, 2) . '/app/models/connection.php';
require_once dirname(__DIR__, 2) . '/app/Liquidaciones/Infrastructure/LegacyLiquidacionReadRepository.php';

use App\Http\JsonResponse;
use App\Liquidaciones\Infrastructure\LegacyLiquidacionReadRepository;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

try {
    $repositorio = new LegacyLiquidacionReadRepository();
    $resumen = $repositorio->obtenerResumen();

    JsonResponse::enviar(array(
        'data' => $resumen,
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
