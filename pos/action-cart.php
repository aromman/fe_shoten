<?php 

session_start();

require_once('../app/models/producto.php');

if(isset($_REQUEST['action']) and $_REQUEST['action']!=""){

    if(isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){
        $forwardOK = $_REQUEST['forwardOk'];
    } else {
        $forwardOK = "pos.php";
    }
    
    switch($_REQUEST["action"]) {
    case "add" :
        if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){
            $productoPDO = new Producto();
            $val = $productoPDO->getById($_REQUEST['id']);
            unset($productoPDO);
            $itemArray = array($val["id"]=>array('id'=>$val["id"], 'titulo'=>$val["titulo"], 'sku'=>$val["sku"], 'cantidad'=>1, 'precio'=>$val["precio"],'costo'=>$val["precio_costo"] ));
            if(!empty($_SESSION["cart_item"])) {
                $found = FALSE;
                foreach($_SESSION["cart_item"] as $k => $v) {
                    if($val["id"] == $v["id"]) {
                        $found = TRUE;
                        if(empty($_SESSION["cart_item"][$k]["cantidad"])) {
                            $_SESSION["cart_item"][$k]["cantidad"] = 0;
                        }
                        $_SESSION["cart_item"][$k]["cantidad"] += 1;
                    }
                }
                if (!$found){
                    $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
                }
            } else {
                $_SESSION["cart_item"] = $itemArray;
            }
        }
        break;    
	case "remove":
		if(!empty($_SESSION["cart_item"])) {
            if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){
                $id = $_REQUEST['id'];
                foreach($_SESSION["cart_item"] as $k => $v) {
                    if($id == $v["id"]) {
                          unset($_SESSION["cart_item"][$k]);
                    }
                    if(empty($_SESSION["cart_item"]))
                        unset($_SESSION["cart_item"]);
                    
                }
		    }
        }
	    break;
	case "empty":
		unset($_SESSION["cart_item"]);
        unset($_SESSION["cart_header"]);
	    break;
    }
    header("location:". $forwardOK);
    exit;

}   else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: /error.php");
    exit();
}
?>