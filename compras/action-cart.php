<?php 

session_start();

include_once('../config.php');
require_once __DIR__ . '/../app/config/url.php';

if(isset($_REQUEST['action']) and $_REQUEST['action']!=""){

    if(isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){
        $forwardOK = $_REQUEST['forwardOk'];
    } else {
        $forwardOK = "buy.php";
    }


    switch($_REQUEST["action"]) {
    case "add" :
        if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){
            
            $sql = "SELECT 
            p.id,
            p.titulo,
            p.sku,
            p.precio,
            (p.precio * ((100 - e.porcentaje) / 100)) costo 
            FROM 
            productos p, editoriales e
            WHERE 
            p.id_editorial = e.id 
            and p.id =" . $_REQUEST['id'] ;

            $productByCode = $db->query($sql);
            $val  =   $productByCode->fetch_assoc();
            $itemArray = array($val["id"]=>array('id'=>$val["id"], 'titulo'=>$val["titulo"], 'sku'=>$val["sku"], 'cantidad'=>1, 'precioLista'=>$val["precio"], 'precioCosto'=>$val["costo"]));
            if(!empty($_SESSION["oc_item"])) {
                $found = FALSE;
                foreach($_SESSION["oc_item"] as $k => $v) {
                    if($val["id"] == $v["id"]) {
                        $found = TRUE;
                        if(empty($_SESSION["oc_item"][$k]["cantidad"])) {
                            $_SESSION["oc_item"][$k]["cantidad"] = 0;
                        }
                        $_SESSION["oc_item"][$k]["cantidad"] += 1;
                    }
                }
                if (!$found){
                    $_SESSION["oc_item"] = array_merge($_SESSION["oc_item"],$itemArray);
                }
            } else {
                $_SESSION["oc_item"] = $itemArray;
            }
        }
        break;    
	case "remove":
		if(!empty($_SESSION["oc_item"])) {
            if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){
                $id = $_REQUEST['id'];
                foreach($_SESSION["oc_item"] as $k => $v) {
                    if($id == $v["id"]) {
                          unset($_SESSION["oc_item"][$k]);
                    }
                    if(empty($_SESSION["oc_item"]))
                        unset($_SESSION["oc_item"]);
                    
                }
		    }
        }
	    break;
	case "empty":
		unset($_SESSION["oc_item"]);
        unset($_SESSION["oc_header"]);
	    break;
    }
    header("location:". $forwardOK);
    exit;

}   else{
    // URL doesn't contain id parameter. Redirect to error page
    header('Location: ' . app_url('/error.php'));
    exit();
}
?>
