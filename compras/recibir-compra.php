<?php

session_start();

require_once "../app/config/url.php";
require_once "../app/models/compras.php";
require_once "../app/models/ordenCompra.php";
require_once "../app/models/producto.php";

if (!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

if (
    isset($_REQUEST['id']) && $_REQUEST['id'] !== "" &&
    isset($_REQUEST['forwardOk']) && $_REQUEST['forwardOk'] !== ""
) {
    $id_header_compra = (int) $_REQUEST['id'];
    $forwardOk = $_REQUEST['forwardOk'];

    $compraPDO = new Compra();
    $compras = $compraPDO->getDetails($id_header_compra);

    if (is_array($compras) && !empty($compras)) {
        $comprasNormalizadas = array_map(function ($item) {
            return array(
                'id' => isset($item['id']) ? (int) $item['id'] : 0,
                'cantidad' => isset($item['cantidad']) ? (float) $item['cantidad'] : 0,
                'precioLista' => isset($item['precio_lista']) ? (float) $item['precio_lista'] : 0,
                'precioCosto' => isset($item['precio_costo']) ? (float) $item['precio_costo'] : 0,
            );
        }, $compras);

        // ACTUALIZO STOCK
        $productoPDO = new Producto();
        $productoPDO->addStockMasive($comprasNormalizadas);

        // actualiza orden
        $ordenCompraPDO = new OrdenCompra();
        $ordenCompraPDO->updateStatus($id_header_compra, 5);
    }

    header('location:' . app_url('/' . ltrim($forwardOk, '/')));
    exit;
}

header('location:' . app_url('/error.php'));
exit;
