<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/Integraciones/Fudo/FudoBootstrap.php';

use App\Http\JsonResponse;
use App\Integraciones\Fudo\FudoBootstrap;
use App\Integraciones\Fudo\FudoException;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($rol !== 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTORIZADO')), 403);
}

function indexarIncluidosFudoInventario($incluidos)
{
    $index = array();
    foreach ($incluidos as $incluido) {
        if (!is_array($incluido) || !isset($incluido['type']) || !isset($incluido['id'])) {
            continue;
        }

        $index[strtolower((string) $incluido['type'])][(string) $incluido['id']] = $incluido;
    }

    return $index;
}

function relacionIdFudoInventario($row, $nombreRelacion)
{
    if (
        !isset($row['relationships'][$nombreRelacion]['data'])
        || !is_array($row['relationships'][$nombreRelacion]['data'])
        || !isset($row['relationships'][$nombreRelacion]['data']['id'])
    ) {
        return '';
    }

    return (string) $row['relationships'][$nombreRelacion]['data']['id'];
}

function nombreIncluidoFudoInventario($index, $tipo, $id)
{
    $tipo = strtolower((string) $tipo);
    $id = (string) $id;
    if (!isset($index[$tipo][$id]['attributes']) || !is_array($index[$tipo][$id]['attributes'])) {
        return '';
    }

    $atributos = $index[$tipo][$id]['attributes'];

    return isset($atributos['name']) ? (string) $atributos['name'] : '';
}

function valorFudoDefinido($atributos, $clave)
{
    return array_key_exists($clave, $atributos) && $atributos[$clave] !== null && $atributos[$clave] !== '';
}

function boolFudoInventario($value)
{
    if ($value === null || $value === '') {
        return '';
    }

    return $value ? 'Si' : 'No';
}

function normalizarItemFudoFaltante($row, $index, $entidad, $relacionCategoria)
{
    $atributos = isset($row['attributes']) && is_array($row['attributes'])
        ? $row['attributes']
        : array();

    $stockDefinido = valorFudoDefinido($atributos, 'stock');
    $minimoDefinido = valorFudoDefinido($atributos, 'minStock');
    $controlStockDefinido = valorFudoDefinido($atributos, 'stockControl');
    $controlStockActivo = $controlStockDefinido ? (bool) $atributos['stockControl'] : null;
    $stock = $stockDefinido ? (float) $atributos['stock'] : null;
    $stockMinimo = $minimoDefinido ? (float) $atributos['minStock'] : null;
    $motivos = array();
    $estado = '';

    if ($controlStockDefinido && !$controlStockActivo) {
        $motivos[] = 'Sin control de stock';
    } elseif ($stockDefinido && $stock <= 0) {
        $motivos[] = 'Sin stock';
    } elseif (!$stockDefinido) {
        $motivos[] = 'Sin stock definido';
    }
    if (!($stockDefinido && $stock <= 0) && !$minimoDefinido) {
        $motivos[] = 'Sin minimo definido';
    }
    if ($stockDefinido && $stock > 0 && $minimoDefinido && $stock < $stockMinimo) {
        $motivos[] = 'Debajo del minimo';
    }

    if (empty($motivos)) {
        return null;
    }

    if ($controlStockDefinido && !$controlStockActivo) {
        $estado = 'sin_control_stock';
    } elseif ($stockDefinido && $stock <= 0) {
        $estado = 'sin_stock';
    } elseif (!$stockDefinido || !$minimoDefinido) {
        $estado = 'sin_definicion';
    } else {
        $estado = 'debajo_minimo';
    }

    $categoriaId = relacionIdFudoInventario($row, $relacionCategoria);
    $unidadId = relacionIdFudoInventario($row, 'unit');

    return array(
        'id' => isset($row['id']) ? (string) $row['id'] : '',
        'entidad' => $entidad,
        'nombre' => isset($atributos['name']) ? (string) $atributos['name'] : '',
        'codigo' => isset($atributos['code']) ? (string) $atributos['code'] : '',
        'categoria' => $categoriaId !== ''
            ? nombreIncluidoFudoInventario($index, $relacionCategoria === 'ingredientCategory' ? 'ingredientcategory' : 'productcategory', $categoriaId)
            : '',
        'unidad' => $unidadId !== '' ? nombreIncluidoFudoInventario($index, 'unit', $unidadId) : '',
        'stock' => $stockDefinido ? $atributos['stock'] : '',
        'stockMinimo' => $minimoDefinido ? $atributos['minStock'] : '',
        'stockNumerico' => $stock,
        'stockMinimoNumerico' => $stockMinimo,
        'controlStock' => isset($atributos['stockControl']) ? boolFudoInventario($atributos['stockControl']) : '',
        'activo' => isset($atributos['active']) ? boolFudoInventario($atributos['active']) : '',
        'costo' => isset($atributos['cost']) ? $atributos['cost'] : '',
        'estado' => $estado,
        'motivo' => implode(', ', $motivos),
    );
}

function normalizarColeccionFudoFaltantes($resultado, $entidad, $relacionCategoria)
{
    $data = isset($resultado['data']) && is_array($resultado['data']) ? $resultado['data'] : array();
    $incluidos = isset($resultado['incluidos']) && is_array($resultado['incluidos']) ? $resultado['incluidos'] : array();
    $index = indexarIncluidosFudoInventario($incluidos);
    $items = array();

    foreach ($data as $row) {
        $item = normalizarItemFudoFaltante($row, $index, $entidad, $relacionCategoria);
        if ($item !== null) {
            $items[] = $item;
        }
    }

    return $items;
}

try {
    $cliente = FudoBootstrap::cliente();
    $productos = normalizarColeccionFudoFaltantes(
        $cliente->obtenerProductos(),
        'Producto',
        'productCategory'
    );
    $ingredientes = normalizarColeccionFudoFaltantes(
        $cliente->obtenerIngredientes(),
        'Ingrediente',
        'ingredientCategory'
    );
    $items = array_merge($productos, $ingredientes);

    usort($items, function ($a, $b) {
        $cmp = strcmp($a['estado'], $b['estado']);
        if ($cmp !== 0) {
            return $cmp;
        }

        return strcasecmp($a['nombre'], $b['nombre']);
    });

    $resumen = array(
        'total' => count($items),
        'productos' => count($productos),
        'ingredientes' => count($ingredientes),
        'sinDefinicion' => 0,
        'debajoMinimo' => 0,
        'sinStock' => 0,
        'sinControlStock' => 0,
    );

    foreach ($items as $item) {
        if ($item['estado'] === 'sin_definicion') {
            $resumen['sinDefinicion']++;
        } elseif ($item['estado'] === 'debajo_minimo') {
            $resumen['debajoMinimo']++;
        } elseif ($item['estado'] === 'sin_stock') {
            $resumen['sinStock']++;
        } elseif ($item['estado'] === 'sin_control_stock') {
            $resumen['sinControlStock']++;
        }
    }

    JsonResponse::enviar(array(
        'data' => array(
            'items' => $items,
            'resumen' => $resumen,
        ),
        'meta' => array(
            'version' => 'v1',
            'proveedor' => 'Fudo',
        ),
    ), 200);
} catch (FudoException $error) {
    $statusCode = $error->getErrorCode() === 'FUDO_NO_CONFIGURADO' ? 503 : 502;
    if ($error->getErrorCode() === 'FUDO_SIN_PERMISO') {
        $statusCode = 403;
    }

    JsonResponse::enviar(array(
        'error' => array(
            'codigo' => $error->getErrorCode(),
            'mensaje' => $error->getMessage(),
        ),
    ), $statusCode);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
