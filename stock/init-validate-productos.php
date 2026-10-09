<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
include_once('../config.php');

$id_canal = $_SESSION["user.canal"];

// actualizo canales de venta
$sql = 'update ventas 
set id_canal = 1
where id_canal not in (select id from canal);';
$db->query($sql);

// actualizo stock a 0
$sql = 'update productos_stock set validado=0 where id_ubicacion in (select id_ubicacion from productos_ubicacion where id_canal = '.$id_canal.');';
$db->query($sql);
//error_log(PHP_EOL.$sql, 3, "my-errors.log");  

$sql = 'update productos set stock = 0 where stock < 0';

header('location: existencias.php');
exit();
