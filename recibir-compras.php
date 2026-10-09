<?php 

/**
 * Params
 * entityName : Nombre de entidad a eliminar
 * delId : Identificador
 * forwardOk : Redireccion por exito
 * 
*/

include_once('config.php');


if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""
){
    // actualiza estado a entregado
    $sql = "update compras set id_estado = 5 WHERE id=" . $_REQUEST['id'];

    // queda pendiente generar venta

    //error_log(PHP_EOL.$sql, 3, "my-errors.log");  
    $result =   $db->query($sql);
    //error_log(PHP_EOL.$result, 3, "my-errors.log");  
    header('location:'.$_REQUEST['forwardOk']);
    exit;
}
?>