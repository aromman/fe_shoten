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

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

try {
    $proveedorPDO = new Proveedor();
    $medioPagoPDO = new MedioPago();
    $editorialPDO = new Editorial();
    $seriePDO = new ProductoSerie();
    $formatoPDO = new ProductoFormato();

    $proveedores = $proveedorPDO->getAllActive();
    $mediosPago = $medioPagoPDO->getAll('nombre ASC');
    $editoriales = $editorialPDO->getAll('nombre ASC');
    $series = $seriePDO->getAll('nombre ASC');
    $formatos = $formatoPDO->getAll('nombre ASC');

    if (!is_array($proveedores)) {
        $proveedores = array();
    }
    if (!is_array($mediosPago)) {
        $mediosPago = array();
    }
    if (!is_array($editoriales)) {
        $editoriales = array();
    }
    if (!is_array($series)) {
        $series = array();
    }
    if (!is_array($formatos)) {
        $formatos = array();
    }

    $itemsProveedor = array_map(function ($row) {
        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
        );
    }, $proveedores);

    $itemsMediosPago = array_map(function ($row) {
        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
        );
    }, $mediosPago);

    $mapearCatalogo = function ($rows) {
        return array_map(function ($row) {
            return array(
                'id' => isset($row['id']) ? (int) $row['id'] : 0,
                'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
            );
        }, $rows);
    };

    $carrito = isset($_SESSION['oc_item']) && is_array($_SESSION['oc_item']) ? array_values($_SESSION['oc_item']) : array();
    $header = isset($_SESSION['oc_header']) && is_array($_SESSION['oc_header']) ? $_SESSION['oc_header'] : array();

    JsonResponse::enviar(array(
        'data' => array(
            'proveedores' => $itemsProveedor,
            'mediosPago' => $itemsMediosPago,
            'editoriales' => $mapearCatalogo($editoriales),
            'series' => $mapearCatalogo($series),
            'formatos' => $mapearCatalogo($formatos),
            'carrito' => $carrito,
            'header' => $header,
        ),
        'meta' => array(
            'totalCarrito' => count($carrito),
            'version' => 'v1',
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
