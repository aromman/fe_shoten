<?php 

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/consignaciones.php";
require_once "../app/models/compras.php";


if(isset($_REQUEST['id']) and $_REQUEST['id']!="" ){
    // recupero la venta
    
    $id_compra = $_REQUEST['id'];

    $consignacionDBO = new Consignaciones();
    $consignacionDBO->deleteByCompra($id_compra);
    
    $compraDBO = new Compra();
    $compraDBO->delete($id_compra);

    header('location: consignaciones.php');
    exit;
}
?>