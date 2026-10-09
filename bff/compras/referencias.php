<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/proveedores.php';
require_once dirname(__DIR__, 2) . '/app/models/medioPago.php';
require_once dirname(__DIR__, 2) . '/app/models/editoriales.php';
require_once dirname(__DIR__, 2) . '/app/models/productoSerie.php';
require_once dirname(__DIR__, 2) . '/app/models/productoFormato.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

try {
    $proveedorPDO = new Proveedor();
    $medioPagoPDO = new MedioPago();
    $editorialPDO = new Editorial();
    $seriePDO = new ProductoSerie();
    $formatoPDO = new ProductoFormato();

    $map = function ($rows) {
        if (!is_array($rows)) {
            $rows = array();
        }

        return array_map(function ($row) {
            return array(
                'id' => isset($row['id']) ? (int) $row['id'] : 0,
                'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
            );
        }, $rows);
    };

    JsonResponse::enviar(array(
        'data' => array(
            'proveedores' => $map($proveedorPDO->getAllActive()),
            'mediosPago' => $map($medioPagoPDO->getAll('nombre ASC')),
            'editoriales' => $map($editorialPDO->getAll('nombre ASC')),
            'series' => $map($seriePDO->getAll('nombre ASC')),
            'formatos' => $map($formatoPDO->getAll('nombre ASC')),
        ),
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
