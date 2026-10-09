<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/medioPago.php';
require_once dirname(__DIR__, 2) . '/app/models/ordenCompra.php';
require_once dirname(__DIR__, 2) . '/app/models/compras.php';
require_once dirname(__DIR__, 2) . '/app/models/producto.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('Location: ' . app_url('/login.php'));
    exit;
}

if (!isset($_POST['fecha']) || $_POST['fecha'] === '') {
    header('Location: ' . app_url('/compras/oc.php'));
    exit;
}

try {
    $fecha = $_POST['fecha'];
    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado'];
    $medioPago = $_POST['medioPago'];
    $id_canal = $_SESSION['user.canal'];
    $itemsCompra = isset($_SESSION['oc_item']) && is_array($_SESSION['oc_item']) ? $_SESSION['oc_item'] : array();

    if (empty($itemsCompra)) {
        throw new \RuntimeException('COMPRA_SIN_ITEMS');
    }

    $cantidad = isset($_POST['cantidad']) ? (float) $_POST['cantidad'] : 0;
    $totalCompra = isset($_POST['monto']) ? (float) $_POST['monto'] : 0;

    if ($cantidad <= 0 || $totalCompra <= 0) {
        $cantidad = 0;
        $totalCompra = 0;
        foreach ($itemsCompra as $item) {
            $itemCantidad = isset($item['cantidad']) ? (float) $item['cantidad'] : 0;
            $itemCosto = isset($item['precioCosto']) ? (float) $item['precioCosto'] : 0;
            $cantidad += $itemCantidad;
            $totalCompra += $itemCantidad * $itemCosto;
        }
    }

    $ordenCompraPDO = new OrdenCompra();
    $ordenCompraPDO->fecha = $fecha;
    $ordenCompraPDO->idProveedor = $proveedor;
    $ordenCompraPDO->idEstadoPedido = $estado;
    $ordenCompraPDO->idMedioPago = $medioPago;
    if ($estado == 5) {
        $ordenCompraPDO->fechaEntrega = $fecha;
    }
    $ordenCompraPDO->monto = $totalCompra;
    $ordenCompraPDO->cantidad = $cantidad;
    $id_header = $ordenCompraPDO->create();

    $compraPDO = new Compra();
    $compraPDO->createMasive($id_header, $fecha, $estado, $medioPago, $itemsCompra);

    if ($estado == 5) {
        $productoPDO = new Producto();
        $productoPDO->addStockMasive($itemsCompra);
    }

    $medioPagoPDO = new MedioPago();
    $mediosPago = $medioPagoPDO->getById($medioPago);
    if (!is_array($mediosPago) || !isset($mediosPago['id_cuenta']) || (int) $mediosPago['id_cuenta'] <= 0) {
        throw new \RuntimeException('MEDIO_PAGO_SIN_CUENTA');
    }
    $idCuenta = $mediosPago['id_cuenta'];

    $cuentaMovimientoPDO = new CuentasMovimientos();
    $cuentaMovimientoPDO->idCuenta = $idCuenta;
    $cuentaMovimientoPDO->fecha = $fecha;
    $cuentaMovimientoPDO->tipoMovimiento = "D";
    $cuentaMovimientoPDO->descripcion = "Compra-" . $id_header;
    $cuentaMovimientoPDO->monto = $totalCompra;
    $cuentaMovimientoPDO->consolidado = "FALSE";
    $cuentaMovimientoPDO->idCausal = 2;
    $cuentaMovimientoPDO->idCanal = $id_canal;
    $cuentaMovimientoPDO->origenTipo = 'compra';
    $cuentaMovimientoPDO->origenId = $id_header;
    $cuentaMovimientoPDO->create();

    unset($_SESSION['oc_item']);
    unset($_SESSION['oc_header']);

    header('Location: ' . app_url('/compras/compras.php'));
    exit;
} catch (\Throwable $error) {
    error_log($error->getMessage());
    header('Location: ' . app_url('/compras/oc.php'));
    exit;
}
