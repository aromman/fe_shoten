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
    $forwardOk = $_POST["forwardOk"];
    
    $input_fecha = trim($_POST["fecha"]);
    $input_id_producto = trim($_POST["producto"]);
    $input_cantidad = trim($_POST["cantidad"]);
    $input_precio_lista = trim($_POST["precioLista"]);
    $input_precio_costo = trim($_POST["precioCosto"]);
    $input_id_orden_compra = trim($_POST["orden"]);
    $input_id_estado = trim($_POST["estado"]);

    $sqlUpdate = 'update compras set ';
    $sqlUpdate = $sqlUpdate . 'fecha ="'.$input_fecha.'"';
    $sqlUpdate = $sqlUpdate . ', id_producto	 = '.$input_id_producto;
    $sqlUpdate = $sqlUpdate . ', cantidad = '.$input_cantidad;
    $sqlUpdate = $sqlUpdate . ', precio_lista = '.$input_precio_lista;
    $sqlUpdate = $sqlUpdate . ', precio_costo = '.$input_precio_costo;

    if (!empty($input_id_orden_compra)){
        $sqlUpdate = $sqlUpdate . ', id_orden_compra = '.$input_id_orden_compra;
    }
    $sqlUpdate = $sqlUpdate . ', id_estado = '.$input_id_estado;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);


    // actualizar headers
    if (!empty($input_id_orden_compra)){
       
        $sqlUpdate = 'update orden_compra set cantidad = (select sum(c.cantidad) cantidad from compras c where c.id_orden_compra = orden_compra.id);';
        $db->query($sqlUpdate);

        $sqlUpdate = 'update orden_compra set monto = (select sum(precio_lista * cantidad) monto  from compras c where c.id_orden_compra = orden_compra.id);';
        $db->query($sqlUpdate);
    
    }

    header('location: ' . $forwardOk);
    exit();
    
} else{

    $mensaje = "Editando";
    //error_log(PHP_EOL.$mensaje, 3, "my-errors.log");  

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        $forwardOk = (isset($_GET["forwardOk"]) && !empty(trim($_GET["forwardOk"]))) ? trim($_GET['forwardOk']) : "compras.php";
        //error_log(PHP_EOL.$forwardOk, 3, "my-errors.log");  
        
        // Prepare a select statement
        
        $rsCompra = $db->query("select * from compras where id = " . $id);
        $valCompra  =  $rsCompra->fetch_assoc();

        $fecha = $valCompra["fecha"]; 
        $producto = $valCompra["id_producto"]; 
        $cantidad = $valCompra["cantidad"]; 
        $precioLista = $valCompra["precio_lista"]; 
        $precioCosto = $valCompra["precio_costo"]; 
        $orden = $valCompra["id_orden_compra"]; 
        $estado = $valCompra["id_estado"]; 
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
                        <h1 class="mt-4">Actualizar Compra</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="gastos.php">Compras</a></li>
                            <li class="breadcrumb-item active">Actualizar Compra</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Producto</label>
                                    <select name="producto" id="producto" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM productos ORDER BY titulo ASC");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['sku']?>)"
                                                <?php if($val['id']==$producto) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['titulo'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" step="0" name="cantidad" value="<?php echo $cantidad;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio Lista</label>
                                    <input type="number" step=".01" name="precioLista" value="<?php echo $precioLista;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio Costo</label>
                                    <input type="number" step=".01" name="precioCosto" value="<?php echo $precioCosto;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Orden</label>
                                    <select name="orden" id="orden" class="form-control" data-live-search="true" data-size="10">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("select 
                                            oc.id,
                                            concat(oc.fecha,' ', p.nombre )  nombre
                                            from orden_compra oc, proveedores p
                                            where oc.id_proveedor = p.id
                                            order by oc.fecha, p.nombre;
                                            ");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$orden) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                        <?php }?>
                                    </select>
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
                                <input type="hidden" name="forwardOk" value="<?php echo $forwardOk; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="<?php echo $forwardOk; ?>" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

    </body>
</html>