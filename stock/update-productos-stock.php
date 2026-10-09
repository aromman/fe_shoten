<?php
// Include config file
require_once "../app/models/producto.php";
require_once "../app/models/editoriales.php";


$productoPDO = new Producto();
// Define variables and initialize with empty values
$nombre  = "";
$nombre_err = "";
$mensaje = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_producto= trim($_POST["producto"]);
    $input_editorial= trim($_POST["editorial"]);
    $input_cantidad = trim($_POST["cantidad"]);
    $input_costo = trim($_POST["costo"]);
    $input_precio= trim($_POST["precio"]);
    $input_nuevo = trim($_POST["esNuevo"]);

    $input_forwardOk = trim($_POST["forwardOk"]);

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");


    $productoPDO->id = $id;
    $productoPDO->precio = $input_precio;
    $productoPDO->stock = $input_cantidad;
    $productoPDO->precioCosto = $input_costo;
    $productoPDO->nuevo = $input_nuevo;
    $productoPDO->idEditorial = $input_editorial;
    $productoPDO->updateDate = $updateTime;
    $productoPDO->update();

    header('location: existencias.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $idProducto =  trim($_GET["id"]);

        $val  =  $productoPDO->getById($idProducto);

        //error_log("<pre>".PHP_EOL.print_r($val)."</pre>", 3, "my-errors.log");
        $id = $val['id'];
        $producto = $val['id'];
        $editorial = $val['id_editorial'];
        $cantidad = $val['stock'];
        $costo = $val['precio_costo'];
        $precio = $val['precio'];
        $nuevo = $val['nuevo'];
        $nombreProducto = $val['titulo'];

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
                        <h1 class="mt-4"><?php echo $nombreProducto?></h1>
                        <h2 class="mt-4">Actualizar Producto Stock</h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-stock.php">Producto Stock</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto Stock</li>
                        </ol>
                        <div class="card-body">
                           
                            <form id="formUpdateProductosStock" method="post">
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
                                    <label>Precio</label>
                                    <input type="number" step=".01" name="precio" value="<?php echo $precio;?>" class="form-control" required="required"></td>
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
                                <input type="hidden" name="forwardOK" value="<?php echo $forwardOK; ?>"/>
                                <input type="hidden" name="producto" value="<?php echo $producto; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="existencias.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>