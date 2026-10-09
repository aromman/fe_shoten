<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/producto.php';

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
    $productoPDO = new Producto();
    $productos = $productoPDO->getAllStockControl();

    if (!is_array($productos)) {
        $productos = array();
    }

    $proveedores = array();
    $editoriales = array();
    $formatos = array();
    $series = array();

    $items = array_map(function ($row) use (&$proveedores, &$editoriales, &$formatos, &$series) {
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        $stock = isset($row['stock']) ? (int) $row['stock'] : 0;
        $precio = isset($row['precio']) ? (float) $row['precio'] : 0;
        $precioCosto = isset($row['precio_costo']) ? (float) $row['precio_costo'] : 0;
        $esNuevo = isset($row['nuevo']) && (bool) $row['nuevo'];
        $editorialId = isset($row['editorialId']) ? (int) $row['editorialId'] : 0;
        $editorial = isset($row['editorial']) ? $row['editorial'] : '';
        $titulo = isset($row['titulo']) ? $row['titulo'] : '';
        $proveedorId = isset($row['proveedorId']) ? (int) $row['proveedorId'] : 0;
        $proveedor = isset($row['proveedor']) ? $row['proveedor'] : '';
        $formatoId = isset($row['formatoId']) ? (int) $row['formatoId'] : 0;
        $formato = isset($row['formato']) ? $row['formato'] : '';
        $serieId = isset($row['serieId']) ? (int) $row['serieId'] : 0;
        $serie = isset($row['serie']) ? $row['serie'] : '';
        $validado = isset($row['validado']) && (bool) $row['validado'];
        $habilitado = !isset($row['habilitado']) || (int) $row['habilitado'] !== 0;
        $updateDate = isset($row['updateDate']) ? strtotime($row['updateDate']) : false;
        $limiteValidacion = strtotime('-30 days');
        $estadoStock = 'disponible';
        $fechaActualizacion = $updateDate !== false ? date('d/m/Y', $updateDate) : '';

        if (!$habilitado) {
            $estadoStock = 'noActivo';
        } elseif ($stock <= 0) {
            $estadoStock = 'agotado';
        } elseif ($validado && $updateDate !== false && $updateDate >= $limiteValidacion) {
            $estadoStock = 'validado';
        }

        if ($proveedorId > 0 && $proveedor !== '') {
            $proveedores[$proveedorId] = $proveedor;
        }
        if ($editorialId > 0 && $editorial !== '') {
            $editoriales[$editorialId] = $editorial;
        }
        if ($formatoId > 0 && $formato !== '') {
            $formatos[$formatoId] = $formato;
        }
        if ($serieId > 0 && $serie !== '') {
            $series[$serieId] = $serie;
        }

        return array(
            'id' => $id,
            'sku' => isset($row['sku']) ? $row['sku'] : '',
            'titulo' => $titulo,
            'tituloCompleto' => $titulo . ' (' . $editorial . ')' . ($esNuevo ? '' : ' - Usado'),
            'stock' => $stock,
            'stockEtiqueta' => $stock > 0 ? (string) $stock : 'SIN STOCK',
            'precio' => $precio,
            'precioEtiqueta' => $stock > 0 ? '$ ' . number_format($precio, 0, '.', '') : '',
            'precioCosto' => $precioCosto,
            'precioCostoEtiqueta' => '$ ' . number_format($precioCosto, 0, '.', ''),
            'serieId' => $serieId,
            'serie' => $serie,
            'formatoId' => $formatoId,
            'formato' => $formato,
            'proveedorId' => $proveedorId,
            'proveedor' => $proveedor,
            'editorialId' => $editorialId,
            'editorial' => $editorial,
            'nuevo' => $esNuevo,
            'validado' => $validado,
            'habilitado' => $habilitado,
            'fechaActualizacionStock' => $fechaActualizacion,
            'estadoStock' => $estadoStock,
            'claseFila' => !$habilitado ? 'table-secondary' : ($stock > 0 ? '' : 'table-danger'),
            'acciones' => array(
                'editar' => app_url('/inventario/update-productos.php?id=' . $id),
                'validar' => app_url('/inventario/validate-productos.php?id=' . $id),
            ),
        );
    }, $productos);

    unset($productoPDO);
    asort($proveedores);
    asort($editoriales);
    asort($formatos);
    asort($series);

    $mapearOpciones = function ($opciones) {
        $items = array();
        foreach ($opciones as $id => $nombre) {
            $items[] = array('id' => (int) $id, 'nombre' => $nombre);
        }

        return $items;
    };

    JsonResponse::enviar(array(
        'data' => array(
            'productos' => $items,
            'filtros' => array(
                'proveedores' => $mapearOpciones($proveedores),
                'editoriales' => $mapearOpciones($editoriales),
                'formatos' => $mapearOpciones($formatos),
                'series' => $mapearOpciones($series),
            ),
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
