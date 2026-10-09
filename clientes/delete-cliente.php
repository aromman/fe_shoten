<?php 

/**
 * Params
 * entityName : Nombre de entidad a eliminar
 * delId : Identificador
 * forwardOk : Redireccion por exito
 * 
*/

include_once('../config.php');


if(isset($_REQUEST['delId']) and $_REQUEST['delId']!=""
  and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""
){

    // actualizo ventas header
    $sql = "update ventas_header set id_cliente = 1 where id_cliente = " . $_REQUEST['delId'];
    error_log(PHP_EOL.$sql, 3, "my-errors.log");  
    $result =   $db->query($sql);
  
    // actualizo ventas 
    $sql = "update ventas set id_cliente = 1 where id_cliente = " . $_REQUEST['delId'];
    error_log(PHP_EOL.$sql, 3, "my-errors.log");  
    $result =   $db->query($sql);
  
    $sql = "delete FROM clientes WHERE id =" . $_REQUEST['delId'];
    error_log(PHP_EOL.$sql, 3, "my-errors.log");  
    $result =   $db->query($sql);

    //error_log(PHP_EOL.$result, 3, "my-errors.log");  
    header('location:'.$_REQUEST['forwardOk']);

    exit;
}
?>