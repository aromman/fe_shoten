<?php 
include_once('../config.php');

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['id_estado']) and $_REQUEST['id_estado']!=""
){
    // actualiza estado a entregado
    $sql = "update reservas set id_estado = ".$_REQUEST['id_estado']." WHERE id=" . $_REQUEST['id'];
    error_log(PHP_EOL.$sql, 3, "my-errors.log");
    $result =   $db->query($sql);
    error_log(PHP_EOL.$result, 3, "my-errors.log");
    header('location: reserva.php');
    exit;
}
?>