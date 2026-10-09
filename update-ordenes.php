<?php
// Include config file
include_once('config.php');
 
// Define variables and initialize with empty values
$fecha     = $canal     = $total     = $unidades = $equipo = $producto = $precioUnitario = $descuento = $motivoDescuento = $medioPago = $cliente = "";
$fecha_err = $canal_err = $total_err = $unidades_err = $equipo_err = $producto_err = $precioUnitario_err = $descuento_err = $motivoDescuento_err = $medioPago_err = $cliente_err = "";

$mensaje = "";

//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";
    //error_log(PHP_EOL.$mensaje, 3, "my-errors.log");  

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_fecha = trim($_POST["fecha"]);
    $input_id_proveedor = trim($_POST["proveedor"]);
    $input_cantidad = trim($_POST["cantidad"]);
    $input_monto = trim($_POST["monto"]);
    $input_fecha_entrega = trim($_POST["fechaEntrega"]);
    $input_id_estado = trim($_POST["estado"]);

    $sqlUpdate = 'update orden_compra set ';
    $sqlUpdate = $sqlUpdate . 'fecha ="'.$input_fecha.'"';
    $sqlUpdate = $sqlUpdate . ', id_proveedor	 = '.$input_id_proveedor;
    $sqlUpdate = $sqlUpdate . ', cantidad = '.$input_cantidad;
    $sqlUpdate = $sqlUpdate . ', monto = '.$input_monto;
    $sqlUpdate = $sqlUpdate . ', fecha_entrega = "'.$input_fecha_entrega.'"';
    $sqlUpdate = $sqlUpdate . ', id_estado_pedido = '.$input_id_estado;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    header('location: orden-compra.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rsOrdenCompra = $db->query("select * from orden_compra where id = " . $id);
        $valOrdenCompra  =  $rsOrdenCompra->fetch_assoc();

        $fecha = $valOrdenCompra["fecha"]; 
        $proveedor = $valOrdenCompra["id_proveedor"]; 
        $cantidad = $valOrdenCompra["cantidad"]; 
        $monto = $valOrdenCompra["monto"]; 
        $fechaEntrega = $valOrdenCompra["fecha_entrega"]; 
        $estado = $valOrdenCompra["id_estado_pedido"]; 
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Tables - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include 'topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include 'sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Actualizar Orden Compra</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="orden_compra.php">Orden Compra</a></li>
                            <li class="breadcrumb-item active">Actualizar Orden Compra</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Proveedor</label>
                                    <select name="proveedor" id="proveedor" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM proveedores ORDER BY nombre ASC");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$proveedor) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" step="0" name="cantidad" value="<?php echo $cantidad;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Monto</label>
                                    <input type="number" step=".01" name="monto" value="<?php echo $monto;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Fecha Entrega</label>
                                    <input type="date" name="fechaEntrega" value="<?php echo $fechaEntrega;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Estado</label>
                                    <select name="estado" id="estado" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM estado_pedido ORDER BY estado ASC ");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$estado) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['estado'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="orden-compra.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

    </body>
</html>