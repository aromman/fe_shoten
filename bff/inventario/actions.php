<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/producto.php';

use App\Http\JsonResponse;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'POST') {
    header('Allow: POST');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$accion = isset($_POST['accion']) ? trim((string) $_POST['accion']) : '';
$ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();

if ($accion === '') {
    JsonResponse::enviar(array('error' => array('codigo' => 'SOLICITUD_INVALIDA')), 400);
}

try {
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime = date("Y/m/d H:i:sa");

    $productoPDO = new Producto();
    if ($accion === 'editar') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $stock = isset($_POST['stock']) ? trim((string) $_POST['stock']) : '';
        $precio = isset($_POST['precio']) ? trim((string) $_POST['precio']) : '';

        if ($id <= 0 || $stock === '' || $precio === '' || !is_numeric($stock) || !is_numeric($precio)) {
            JsonResponse::enviar(array('error' => array('codigo' => 'SOLICITUD_INVALIDA')), 400);
        }

        $productoPDO->id = $id;
        $productoPDO->stock = $stock;
        $productoPDO->precio = $precio;
        $productoPDO->update();
        $actualizados = 1;
    } elseif ($accion === 'validar') {
        if (count($ids) === 0) {
            JsonResponse::enviar(array('error' => array('codigo' => 'SOLICITUD_INVALIDA')), 400);
        }

        $actualizados = $productoPDO->validarMasivo($ids, $updateTime);
    } elseif ($accion === 'sin_stock') {
        if (count($ids) === 0) {
            JsonResponse::enviar(array('error' => array('codigo' => 'SOLICITUD_INVALIDA')), 400);
        }

        $actualizados = $productoPDO->marcarSinStockMasivo($ids, $updateTime);
    } else {
        JsonResponse::enviar(array('error' => array('codigo' => 'ACCION_INVALIDA')), 400);
    }

    JsonResponse::enviar(array(
        'data' => array(
            'actualizados' => $actualizados,
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
