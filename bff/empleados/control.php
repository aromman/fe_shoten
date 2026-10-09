<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
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
    $soloSinCuenta = isset($_GET['sinCuenta']) && $_GET['sinCuenta'] === '1';

    if ($soloSinCuenta) {
        $stm = $empleadoPDO->pdo->prepare(
            "SELECT e.*
             FROM empleados e
             LEFT JOIN cuentas c ON c.id_empleado = e.id AND c.ambito_uso = 'EMPLEADO'
             WHERE c.id IS NULL
             ORDER BY e.apellido ASC, e.nombre ASC"
        );
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $result = $empleadoPDO->getAll('apellido ASC, nombre ASC');
    }

    if (!is_array($result)) {
        $result = array();
    }

    $items = array_map(function ($row) {
        $apellido = isset($row['apellido']) ? $row['apellido'] : '';
        $nombre = isset($row['nombre']) ? $row['nombre'] : '';

        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'apellido' => $apellido,
            'nombre' => $nombre,
            'nombreCompleto' => trim($apellido . ', ' . $nombre, ', '),
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
