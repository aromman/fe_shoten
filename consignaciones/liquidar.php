<?php 

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/ventas.php";
require_once "../app/models/compras.php";
require_once "../app/models/consignaciones.php";

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['fecha']) and $_REQUEST['fecha']!=""
){
    // recupero la venta
    
    $fecha = $_REQUEST['fecha'];
    $id_editorial = $_REQUEST['id'];
    $ventasPDO = new Ventas();
    $rsventas = $ventasPDO->getAllByEditorialAndDate($id_editorial, $fecha);
    if (!is_null($rsventas) && count($rsventas) > 0) {

        foreach($rsventas as $row){
            $id_venta = $row['id'];
            $id_estado = 5; // Estado Entregado

            $fecha = $row['fecha'];
            $id_producto = $row['id_producto'];
            $cantidad = $row['unidades'];
            $precio_lista = $row['precio_unitario'];
            $precio_costo = $row['precio_compra'];

            $comprasPDO = new Compra();
            $comprasPDO->fecha = $fecha;
            $comprasPDO->idProducto = $id_producto;    
            $comprasPDO->cantidad = $cantidad;
            $comprasPDO->precioLista = $precio_lista;
            $comprasPDO->precioCosto = $precio_costo;
            $comprasPDO->idEstado = $id_estado;
            $id_compra = $comprasPDO->create();            

            $consignacionesPDO = new Consignaciones();
            $consignacionesPDO->fecha = $fecha;
            $consignacionesPDO->idVenta = $id_venta;
            $consignacionesPDO->idCompra = $id_compra;
            $consignacionesPDO->idEstado = $id_estado;
            $consignacionesPDO->create();

        }   
    }
    header('location:'.$_REQUEST['forwardOk']);
    exit;
}
?>