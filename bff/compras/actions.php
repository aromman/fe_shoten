<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/producto.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('Location: ' . app_url('/login.php'));
    exit;
}

$forwardOk = isset($_REQUEST['forwardOk']) && $_REQUEST['forwardOk'] !== '' ? $_REQUEST['forwardOk'] : 'buy.php';

try {
    if (isset($_POST['buyNewAction']) && $_POST['buyNewAction'] === 'new' && isset($_POST['sku']) && $_POST['sku'] !== '') {
        $itemNewPDO = new Producto();
        $itemNewPDO->setPrecio($_POST['precioVenta']);
        $itemNewPDO->setIdEditorial($_POST['editorial']);
        $itemNewPDO->setSku($_POST['sku']);
        $itemNewPDO->setTitulo($_POST['titulo']);
        $itemNewPDO->setStock(0);
        $itemNewPDO->setIdSerie(empty($_POST['serie']) ? 1 : $_POST['serie']);
        $itemNewPDO->setIdFormato(empty($_POST['formato']) ? 1 : $_POST['formato']);
        $itemNewPDO->setPrecioCosto($_POST['precioCompra']);
        $itemNewPDO->tomo = empty($_POST['tomo']) ? 0 : $_POST['tomo'];
        $itemNewPDO->setAdmiteDescuento(isset($_POST['admiteDescuento']) ? $_POST['admiteDescuento'] : 1);
        $itemNewPDO->nuevo = $_POST['esNuevo'];
        $idProducto = $itemNewPDO->create();

        if (!empty($idProducto)) {
            $itemArray = array($idProducto => array(
                'id' => $idProducto,
                'titulo' => $_POST['titulo'],
                'sku' => $_POST['sku'],
                'cantidad' => $_POST['cantidad'],
                'precioLista' => $_POST['precioVenta'],
                'precioCosto' => $_POST['precioCompra'],
            ));

            if (!empty($_SESSION['oc_item'])) {
                $found = false;
                foreach ($_SESSION['oc_item'] as $k => $v) {
                    if ($idProducto == $v['id']) {
                        $found = true;
                        if (empty($_SESSION['oc_item'][$k]['cantidad'])) {
                            $_SESSION['oc_item'][$k]['cantidad'] = 0;
                        }
                        $_SESSION['oc_item'][$k]['cantidad'] += $_POST['cantidad'];
                    }
                }
                if (!$found) {
                    $_SESSION['oc_item'] = array_merge($_SESSION['oc_item'], $itemArray);
                }
            } else {
                $_SESSION['oc_item'] = $itemArray;
            }
        }
    }

    if (isset($_POST['buyAction']) && $_POST['buyAction'] === 'add' && isset($_POST['id']) && $_POST['id'] !== '') {
        $itemPDO = new Producto();
        $val = $itemPDO->getById($_POST['id']);

        if (!empty($val)) {
            $itemArray = array($val['id'] => array(
                'id' => $val['id'],
                'titulo' => $val['titulo'],
                'sku' => $val['sku'],
                'cantidad' => $_POST['cantidad'],
                'precioLista' => $_POST['precioVenta'],
                'precioCosto' => $_POST['precioCompra'],
            ));

            if (!empty($_SESSION['oc_item'])) {
                $found = false;
                foreach ($_SESSION['oc_item'] as $k => $v) {
                    if ($val['id'] == $v['id']) {
                        $found = true;
                        if (empty($_SESSION['oc_item'][$k]['cantidad'])) {
                            $_SESSION['oc_item'][$k]['cantidad'] = 0;
                        }
                        $_SESSION['oc_item'][$k]['cantidad'] += $_POST['cantidad'];
                    }
                }
                if (!$found) {
                    $_SESSION['oc_item'] = array_merge($_SESSION['oc_item'], $itemArray);
                }
            } else {
                $_SESSION['oc_item'] = $itemArray;
            }
        }
    }

    if (isset($_POST['buyActionClone']) && $_POST['buyActionClone'] === 'add' && isset($_POST['proId']) && $_POST['proId'] !== '') {
        $itemPDO = new Producto();
        $val = $itemPDO->getById($_POST['proId']);

        if (!empty($val)) {
            $itemNewPDO = new Producto();
            $itemNewPDO->setSku(trim($_POST['proSku']));
            $itemNewPDO->setTitulo(trim($_POST['proProducto']));
            $itemNewPDO->setIdEditorial($val['id_editorial']);
            $itemNewPDO->setStock(0);
            $itemNewPDO->setPrecio($val['precio']);
            $itemNewPDO->setIdSerie($val['id_serie']);
            $itemNewPDO->tomo = trim($_POST['proTomo']);
            $itemNewPDO->setIdFormato($val['id_formato']);
            $itemNewPDO->setAdmiteDescuento(isset($val['admite_descuento']) ? $val['admite_descuento'] : 1);
            $itemNewPDO->nuevo = $val['nuevo'];
            $itemNewPDO->setPrecioCosto($_POST['precioCompra']);
            $id_new = $itemNewPDO->create();

            if (!empty($id_new)) {
                $itemArray = array($id_new => array(
                    'id' => $id_new,
                    'titulo' => trim($_POST['proProducto']),
                    'sku' => trim($_POST['proSku']),
                    'cantidad' => $_POST['cantidad'],
                    'precioLista' => $_POST['precioVenta'],
                    'precioCosto' => $_POST['precioCompra'],
                ));

                if (!empty($_SESSION['oc_item'])) {
                    $found = false;
                    foreach ($_SESSION['oc_item'] as $k => $v) {
                        if ($id_new == $v['id']) {
                            $found = true;
                            if (empty($_SESSION['oc_item'][$k]['cantidad'])) {
                                $_SESSION['oc_item'][$k]['cantidad'] = 0;
                            }
                            $_SESSION['oc_item'][$k]['cantidad'] += $_POST['cantidad'];
                        }
                    }
                    if (!$found) {
                        $_SESSION['oc_item'] = array_merge($_SESSION['oc_item'], $itemArray);
                    }
                } else {
                    $_SESSION['oc_item'] = $itemArray;
                }
            }
        }
    }
} catch (\Throwable $error) {
    error_log($error->getMessage());
}

header('Location: ' . app_url('/compras/' . $forwardOk));
exit;
