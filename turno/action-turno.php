<?php

require_once "../app/models/turnos.php";

if(isset($_REQUEST['id']) && $_REQUEST['id']!=""
  && isset($_REQUEST['estado']) && $_REQUEST['estado']!=""
){
    $turnosPDO = new Turnos();
    if ($_REQUEST['estado']=='D')  {
      $turnosPDO->delete($_REQUEST['id']);
    } else {
    // actualiza estado a entregado
      
      $turnosPDO->estado = $_REQUEST['estado'];
      $turnosPDO->id = $_REQUEST['id'];
      $turnosPDO->update();
    }
    header('location: movimientos.php');
    exit;
}
