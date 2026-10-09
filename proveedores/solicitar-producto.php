<?php 

require_once "../app/models/pedidos.php";

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['nombre']) and $_REQUEST['nombre']!=""
  and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""
){

    $fecha = date("Y-m-d");
    $idProducto = $_REQUEST['id'];
    $nombreProducto = $_REQUEST['nombre'];

    $pedidoNewPdo = new Pedido();
    $pedidoNewPdo->fecha = $fecha;
    $pedidoNewPdo->idProducto = $idProducto;
    $pedidoNewPdo->producto = $nombreProducto;
    $pedidoNewPdo->idEstado = 1; // Pendiente
    $pedidoNewPdo->fechaActualizacion = $fecha;
    $pedidoNewPdo->create();

    //error_log(PHP_EOL.$result, 3, "my-errors.log");  
    header('location:'.$_REQUEST['forwardOk']);

    exit;
}
?>