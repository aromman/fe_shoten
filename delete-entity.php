<?php 

/**
 * Params
 * entityName : Nombre de entidad a eliminar
 * delId : Identificador
 * forwardOk : Redireccion por exito
 * 
*/

include_once('config.php');


if(isset($_REQUEST['entityName']) and $_REQUEST['entityName']!=""
  and isset($_REQUEST['delId']) and $_REQUEST['delId']!=""
  and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""
){
    $sql = "delete FROM ". $_REQUEST['entityName']. " WHERE id =" . $_REQUEST['delId'];

    //error_log(PHP_EOL.$sql, 3, "my-errors.log");  
    $result =   $db->query($sql);

    unset($_SESSION["productosRows"]);

    //error_log(PHP_EOL.$result, 3, "my-errors.log");  
    header('location:'.$_REQUEST['forwardOk']);

    exit;
}
?>