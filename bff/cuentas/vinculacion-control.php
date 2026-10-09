<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentas.php';
require_once dirname(__DIR__, 2) . '/app/models/proveedores.php';
require_once dirname(__DIR__, 2) . '/app/models/gastosClase.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

try {
    $cuentasPDO = new Cuentas();
    $proveedoresPDO = new Proveedor();
    $gastosClasePDO = new GastosClase();

    $cuentas = $cuentasPDO->getAll('nombre ASC');
    $proveedores = $proveedoresPDO->getAll('nombre ASC');
    $clases = $gastosClasePDO->getAll('id ASC');

    if (!is_array($cuentas)) {
        $cuentas = array();
    }
    if (!is_array($proveedores)) {
        $proveedores = array();
    }
    if (!is_array($clases)) {
        $clases = array();
    }

    JsonResponse::enviar(array(
        'data' => array(
            'cuentas' => array_map(function ($row) {
                return array(
                    'id' => isset($row['id']) ? (int) $row['id'] : 0,
                    'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
                );
            }, $cuentas),
            'proveedores' => array_map(function ($row) {
                return array(
                    'id' => isset($row['id']) ? (int) $row['id'] : 0,
                    'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
                );
            }, $proveedores),
            'clases' => array_map(function ($row) {
                return array(
                    'id' => isset($row['id']) ? (int) $row['id'] : 0,
                    'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
                );
            }, $clases),
        ),
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
