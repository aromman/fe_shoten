<?php
session_start();

require_once dirname(__DIR__, 2) . '/app/models/oferta.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../../login.php');
    exit;
}

function redirigirOfertas($destino = 'ofertas.php')
{
    $permitidos = array('ofertas.php', 'activas.php');
    if (!in_array($destino, $permitidos, true)) {
        $destino = 'ofertas.php';
    }

    header('location: ../../ofertas/' . $destino);
    exit;
}

$accion = isset($_POST['accionOferta']) ? trim((string) $_POST['accionOferta']) : '';
$returnTo = isset($_POST['returnTo']) ? trim((string) $_POST['returnTo']) : 'ofertas.php';

try {
    $ofertaPDO = new Oferta();

    if ($accion === 'stock') {
        $ofertaPDO->actualizarStock(
            isset($_POST['stockProductoId']) ? (int) $_POST['stockProductoId'] : 0,
            isset($_POST['stockCantidadReal']) ? (int) $_POST['stockCantidadReal'] : 0
        );
        redirigirOfertas($returnTo);
    }

    if ($accion === 'oferta') {
        $ofertaPDO->actualizarPrecioOferta(
            isset($_POST['ofertaProductoId']) ? (int) $_POST['ofertaProductoId'] : 0,
            isset($_POST['ofertaPrecio']) ? (float) $_POST['ofertaPrecio'] : 0
        );
        redirigirOfertas($returnTo);
    }

    if ($accion === 'precios') {
        $ofertaPDO->actualizarPreciosProducto(
            isset($_POST['preciosProductoId']) ? (int) $_POST['preciosProductoId'] : 0,
            isset($_POST['preciosCompra']) ? (float) $_POST['preciosCompra'] : 0,
            isset($_POST['preciosLista']) ? (float) $_POST['preciosLista'] : 0,
            isset($_POST['preciosOferta']) ? (float) $_POST['preciosOferta'] : 0
        );
        redirigirOfertas($returnTo);
    }

    if ($accion === 'consignacion') {
        $ofertaPDO->pasarAConsignacion(
            isset($_POST['consignacionProductoId']) ? (int) $_POST['consignacionProductoId'] : 0,
            isset($_POST['consignacionEditorial']) ? (int) $_POST['consignacionEditorial'] : 0,
            isset($_POST['consignacionMedioPago']) ? (int) $_POST['consignacionMedioPago'] : 0,
            isset($_POST['consignacionCantidad']) ? (int) $_POST['consignacionCantidad'] : 0,
            isset($_POST['consignacionPrecio']) ? (float) $_POST['consignacionPrecio'] : 0
        );
        redirigirOfertas($returnTo);
    }

    redirigirOfertas($returnTo);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    header('location: ../../errors.php');
    exit;
}
?>
