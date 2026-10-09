<?php 

date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once "../app/models/pedidos.php";

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['id_estado']) and $_REQUEST['id_estado']!=""
){
    
    $fechaHoy = date("Y-m-d");

    $updatePedidosPDO = new Pedidos();
    $updatePedidosPDO->id = $_REQUEST['id'];
    $updatePedidosPDO->idEstado = $_REQUEST['id_estado'];
    $updatePedidosPDO->fechaActualizacion = $fechaHoy;
    $updatePedidosPDO->update();

    header('location: pedidos.php');
    exit;
}
?>