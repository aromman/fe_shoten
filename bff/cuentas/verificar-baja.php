<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentas.php';

use App\Http\JsonResponse;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    JsonResponse::enviar(array('error' => array('codigo' => 'PARAMETRO_INVALIDO')), 422);
}

try {
    $id = (int) $_GET['id'];
    $cuentasPDO = new Cuentas();
    $dependencias = $cuentasPDO->tieneDependencias($id);

    JsonResponse::enviar(array(
        'data' => array(
            'puedeBorrar' => empty($dependencias),
            'dependencias' => $dependencias,
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
