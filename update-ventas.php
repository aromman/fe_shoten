<?php
// Include config file
include_once('config.php');
require_once "app/models/producto.php";
 
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
    $input_total = trim($_POST["total"]);
    $input_id_canal = trim($_POST["canal"]);
    $input_id_producto = trim($_POST["producto"]);
    $input_unidades = trim($_POST["unidades"]);
    $input_id_medio_pago = trim($_POST["medioPago"]);
    $input_descuento = trim($_POST["descuento"]);
    $input_motivo_descuento = trim($_POST["motivoDescuento"]);
    $input_id_cliente = trim($_POST["cliente"]);
    $input_precio_unitario = trim($_POST["precioUnitario"]);
    $input_id_ventas_header = trim($_POST["ventasHeader"]);
    $input_id_descuento = trim($_POST["idDescuento"]);

    $sqlUpdate = 'update ventas set ';
    $sqlUpdate = $sqlUpdate . 'fecha ="'.$input_fecha.'"';
    $sqlUpdate = $sqlUpdate . ', id_canal = '.$input_id_canal;
    $sqlUpdate = $sqlUpdate . ', id_producto	 = '.$input_id_producto;
    $sqlUpdate = $sqlUpdate . ', unidades = '.$input_unidades;
    $sqlUpdate = $sqlUpdate . ', precio_unitario = '.$input_precio_unitario;
    if (!empty($input_descuento)){
        $sqlUpdate = $sqlUpdate . ', descuento = '.$input_descuento;
    }
    if (!empty($input_motivo_descuento)){
        $sqlUpdate = $sqlUpdate . ', motivo_descuento = "'.$input_motivo_descuento.'"';
    }
    if (!empty($input_id_descuento)){
        $sqlUpdate = $sqlUpdate . ', id_descuento = '.$input_id_descuento;
    }
    $sqlUpdate = $sqlUpdate . ', total = '.$input_total;
    $sqlUpdate = $sqlUpdate . ', id_medio_pago = '.$input_id_medio_pago;
    $sqlUpdate = $sqlUpdate . ', id_cliente = '.$input_id_cliente;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    // actualizar header
    if (!empty($input_id_ventas_header)){

        // recupero totales
        $rsVentaHeader = $db->query("select sum(unidades) cantidad,
                                     sum(unidades*precio_unitario) subtotal,
                                     sum(descuento) descuento
                                     from ventas where id_ventas_header = " . $input_id_ventas_header);
        $valVentaHeader  =  $rsVentaHeader ->fetch_assoc();

        $cantidad = $valVentaHeader["cantidad"];
        $subTotal = $valVentaHeader["subtotal"];
        $descuento = $valVentaHeader["descuento"];
        $total = $subTotal - $descuento;

        $sqlUpdate = 'update ventas_header set ';
        $sqlUpdate = $sqlUpdate . 'fecha ="'.$input_fecha.'"';
        $sqlUpdate = $sqlUpdate . ',id_cliente ='.$input_id_cliente;
        $sqlUpdate = $sqlUpdate . ',cantidad ='.$cantidad;
        $sqlUpdate = $sqlUpdate . ',subTotal ='.$subTotal;
        $sqlUpdate = $sqlUpdate . ',descuento ='.$descuento;
        $sqlUpdate = $sqlUpdate . ',total ='.$total;
        $sqlUpdate = $sqlUpdate . ' WHERE id = '.$input_id_ventas_header;
        //error_log(PHP_EOL."Actualizar Header", 3, "my-errors.log");
        //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");
        $db->query($sqlUpdate);
    
        // busco movimiento de header de ventas
        $sqlUpdate = 'update cuentas_movimientos set ';
        $sqlUpdate = $sqlUpdate . 'monto ='.$total;
        $sqlUpdate = $sqlUpdate . ' WHERE descripcion = "Venta-'.$input_id_ventas_header.'"';
        //error_log(PHP_EOL."Actualizar Movimiento Header", 3, "my-errors.log");
        //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");
        $db->query($sqlUpdate);
    }

    header('location: ventas.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rsVenta = $db->query("select * from ventas where id = " . $id);
        $valVenta  =  $rsVenta->fetch_assoc();

        $fecha = $valVenta["fecha"]; 
        $canal = $valVenta["id_canal"]; 
        $producto = $valVenta["id_producto"]; 
        $unidades = $valVenta["unidades"]; 
        $precioUnitario = $valVenta["precio_unitario"]; 
        $descuento = $valVenta["descuento"]; 
        $motivoDescuento = $valVenta["motivo_descuento"]; 
        $total = $valVenta["total"]; 
        $medioPago = $valVenta["id_medio_pago"]; 
        $cliente =  $valVenta["id_cliente"]; 
        $ventasHeader = $valVenta["id_ventas_header"]; 
        $idDescuento = $valVenta["id_descuento"]; 

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
                        <h1 class="mt-4">Actualizar Venta</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="gastos.php">Ventas</a></li>
                            <li class="breadcrumb-item active">Actualizar Venta</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Canal</label>
                                    <select name="canal" id="canal" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM canal ORDER BY nombre ASC ");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$canal) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>
                                <div>
                                    <label>Producto</label>
                                    <select name="producto" id="producto" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $productoPDO = new Producto();
                                            $productos = $productoPDO->getAll();
                                            foreach ($productos as $row) {

                                                $valTitulo = $row['titulo']. ' (' . $row['editorial'].')' . ($row['nuevo']==TRUE ? '' : ' - Usado');

                                            ?>
                                            <option 
                                                value="<?php echo $row['id']?>" 
                                                data-subtext="(<?php echo $row['sku']?>)"
                                                <?php if($row['id']==$producto) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($valTitulo,'UTF-8')?>
                                            </option>
                                            <?php 
                                                }
                                                unset($productoPDO);
                                            ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Unidades</label>
                                    <input type="number" step="0" name="unidades" value="<?php echo $unidades;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio Unitario</label>
                                    <input type="number" step=".01" name="precioUnitario" value="<?php echo $precioUnitario;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Monto Descuento</label>
                                    <input type="number" step=".01" name="descuento" value="<?php echo $descuento;?>" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Codigo Descuento</label>
                                    <select name="idDescuento" id="idDescuento" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM descuentos ORDER BY nombre ASC");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['tipo']?>)"
                                                <?php if($val['id']==$idDescuento) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Motivo Descuento</label>
                                    <input type="text" name="motivoDescuento" value="<?php echo $motivoDescuento;?>" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label>Total</label>
                                    <input type="number" step=".01" name="total" value="<?php echo $total;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Medio de Pago</label>
                                    <select name="medioPago" id="medioPago" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM medio_pago ORDER BY nombre ASC");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$medioPago) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>
                                <div>
                                    <label>Cliente</label>
                                    <select name="cliente" id="cliente" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM clientes ORDER BY apellido, nombre ASC ");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$cliente) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['apellido'] . ' '.$val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>

                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="ventasHeader" value="<?php echo $ventasHeader; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="ventas.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

    </body>
</html>