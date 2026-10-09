<?php 
require_once "../app/models/inscripcion.php";

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['id_estado']) and $_REQUEST['id_estado']!=""
){
    // actualiza estado a entregado
    $inscripcionPDO = new Inscripcion();
    $inscripcionPDO->id = $_REQUEST['id'];
    $inscripcionPDO->idEstado = $_REQUEST['id_estado'];
    $inscripcionPDO->update();
    //error_log(PHP_EOL.$result, 3, "my-errors.log");  
    header('location: inscripcion.php');
    exit;
}
?>