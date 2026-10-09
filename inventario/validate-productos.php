<?php

require_once "../app/models/producto.php";

// Processing form data when form is submitted
if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");

    $productoUpdatePDO = new Producto();
    $idProducto = $_REQUEST['id'];
    $productoUpdatePDO->validado = TRUE;
    $productoUpdatePDO->updateDate = $updateTime;
    $productoUpdatePDO->setId($idProducto);
    $productoUpdatePDO->update();
   
} 

header('location: control.php');
exit();
