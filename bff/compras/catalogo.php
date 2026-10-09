<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/producto.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$traerTodos = isset($_GET['all']) && (string) $_GET['all'] === '1';
$limit = isset($_GET['limit']) ? max(10, min(500, (int) $_GET['limit'])) : 100;
$offset = ($page - 1) * $limit;
$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

try {
    $productoPDO = new Producto();
    $where = '';
    $params = array();
    if ($query !== '') {
        $where = "where p.sku like :query or p.titulo like :query or e.nombre like :query";
        $params[':query'] = '%' . $query . '%';
    }

    $sql = "
        select
            p.id id,
            p.titulo titulo,
            p.precio precio,
            e.nombre editorial,
            p.nuevo,
            p.sku,
            p.precio_costo
        from productos p
        join editoriales e on p.id_editorial = e.id
        {$where}
        order by p.titulo
    ";
    if (!$traerTodos) {
        $sql .= ' limit :limit offset :offset';
    }

    $stm = $productoPDO->pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stm->bindValue($key, $value, PDO::PARAM_STR);
    }
    if (!$traerTodos) {
        $stm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stm->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    $stm->execute();
    $productos = $stm->fetchAll(PDO::FETCH_ASSOC);

    $items = array_map(function ($row) {
        $precioLista = isset($row['precio']) ? (float) $row['precio'] : 0;
        $precioCosto = isset($row['precio_costo']) ? (float) $row['precio_costo'] : 0;

        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'sku' => isset($row['sku']) ? $row['sku'] : '',
            'titulo' => isset($row['titulo']) ? $row['titulo'] : '',
            'editorial' => isset($row['editorial']) ? $row['editorial'] : '',
            'nuevo' => isset($row['nuevo']) && (bool) $row['nuevo'],
            'precioLista' => $precioLista,
            'precioCosto' => $precioCosto,
        );
    }, is_array($productos) ? $productos : array());

    JsonResponse::enviar(array(
        'data' => array(
            'productos' => $items,
        ),
        'meta' => array(
            'page' => $page,
            'limit' => $traerTodos ? 'all' : $limit,
            'query' => $query,
            'count' => count($items),
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
