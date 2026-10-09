<?php
// Include config file
require_once "../app/models/producto.php";
 
// Processing form data when form is submitted
if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){

    $idProducto = $_REQUEST['id'];

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");

    $productoPDO = new Producto();    
    $productoPDO->id = $idProducto;
    $productoPDO->validado = 1; // validado
    $productoPDO->updateDate = $updateTime;
    $productoPDO->update();
   
} 

header('location: existencias.php');
exit();
