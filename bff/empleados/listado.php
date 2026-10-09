<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/empleados.php';

use App\Http\JsonResponse;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

try {
    $empleadoPDO = new Empleado();
    $result = $empleadoPDO->getAll('apellido ASC, nombre ASC');

    if (!is_array($result)) {
        $result = array();
    }

    $items = array_map(function ($row) {
        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'apellido' => isset($row['apellido']) ? $row['apellido'] : '',
            'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
        );
    }, $result);

    JsonResponse::enviar(array(
        'data' => array(
            'empleados' => $items,
        ),
        'meta' => array(
            'total' => count($items),
            'version' => 'v1',
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
