<?php

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    && isset($_REQUEST['forwardOk']) && $_REQUEST['forwardOk']!=""){

		require "../server.php";

		$id = $_GET["id"];
		$forwarOK = $_REQUEST['forwardOk'];
		$estado = $_GET["estado"];

		date_default_timezone_set('America/Argentina/Buenos_Aires');
		$updateTime=date("Y/m/d H:i:sa");
	

		$sql = "update comandas set id_estado = ".$estado.", updateDate='".$updateTime."' where id_venta_header =" . $id;
		error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
		
		$db->query($sql);

		header('location: '.$forwarOK);
		exit;
}