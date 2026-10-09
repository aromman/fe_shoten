<?php
// Include config file
include_once('../config.php');
require_once "../app/models/editoriales.php";
require_once "../app/models/productoStock.php";

// Define variables and initialize with empty values
$nombre  = "";
$nombre_err = "";
$mensaje = "";

//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_producto= trim($_POST["producto"]);
    $input_editorial= trim($_POST["editorial"]);
    $input_cantidad= trim($_POST["cantidad"]);
    $input_ubicacion= trim($_POST["ubicacion"]);
    $input_fecha= trim($_POST["fecha"]);
    $input_costo= trim($_POST["costo"]);
    $input_nuevo= trim($_POST["esNuevo"]);

    $sqlUpdate = 'update productos_stock set id_producto='.$input_producto.', id_editorial='.$input_editorial.', cantidad='.$input_cantidad.', id_ubicacion='.$input_ubicacion.', fecha="'.$input_fecha.'", costo='.$input_costo.', nuevo='.$input_nuevo;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    header("location: productos-stock.php?forwardOk=productos/productos.php&id=".$input_producto);
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);

        $productoStockPDO = new ProductoStock();
        $val  =  $productoStockPDO->getById($id);
        
        $producto = $val['id_producto'];
        $editorial = $val['id_editorial'];
        $cantidad = $val['cantidad'];
        $ubicacion = $val['id_ubicacion'];
        $fecha = $val['fecha'];
        $costo = $val['costo'];
        $nuevo = $val['nuevo'];

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
        <title>Actualizar Producto</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Actualizar Producto Stock</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-stock.php">Producto Stock</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto Stock</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                
                                <div  class="form-group">
                                    <label>Editorial</label>
                                    <select name="editorial" id="editorial" class="form-control" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $editorialPDO = new Editorial();
                                            $editoriales = $editorialPDO->getAll('nombre');
                                            if (!is_null($editoriales)){
                                                foreach ($editoriales as $val) {
                                            ?>
                                            <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$editorial) echo 'selected="selected"'; ?>
                                            >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                                        <?php 
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" step="0.1" name="cantidad" value="<?php echo $cantidad;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Ubicacion</label>
                                    <select name="ubicacion" id="ubicacion" class="form-control" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query('select 
                                                                    pu.id id,
                                                                    concat(c.nombre, " - ", put1.nombre, " ", pu.nivel_codigo_01, " - ", put2.nombre, " ", pu.nivel_codigo_02 )  nombre
                                                                    from productos_ubicacion pu, 
                                                                    canal c,
                                                                    productos_ubicacion_tipo put1,
                                                                    productos_ubicacion_tipo put2
                                                                    where pu.id_canal = c.id
                                                                    and pu.id_nivel_tipo_01 = put1.id
                                                                    and pu.id_nivel_tipo_02 = put2.id');
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$ubicacion) echo 'selected="selected"'; ?>
                                            >
                                            <?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                                        <?php }?>
                                        
                                    </select>

                                </div>
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>

                                <div class="form-group">
                                    <label>Costo</label>
                                    <input type="number" step=".01" name="costo" value="<?php echo $costo;?>" class="form-control" required="required"></td>
                                </div>    
                                <div class="form-group">
                                    <label>Nuevo</label>
                                    <select name="esNuevo" id="esNuevo" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <option value="1" data-subtext="(TRUE)" <?php if(1==$nuevo) echo 'selected="selected"'; ?>>Si</option>
                                        <option value="0" data-subtext="(FALSE)" <?php if(0==$nuevo) echo 'selected="selected"'; ?>>No</option>
                                    </select>
                                </div>    
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="producto" value="<?php echo $producto; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="productos-stock.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>