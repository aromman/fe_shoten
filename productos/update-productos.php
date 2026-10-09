<?php

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}


// Include config file
require_once "../app/models/producto.php";
require_once "../app/models/editoriales.php";
require_once "../app/models/productoSerie.php";
require_once "../app/models/productoFormato.php";

// Define variables and initialize with empty values
$sku     = $titulo     = $editorial = $stock = $precio = "";
$sku_err = $titulo_err = $editorial_err = $stock_err = $precio_err ="";

$mensaje = "";

$productoPDO = new Producto();


// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";
    //error_log(PHP_EOL.$mensaje, 3, "my-errors.log");  

    // Get hidden input value
    $id = $_POST["id"];
    $forwardOk = $_POST["forwardOk"];
    
    $input_sku = trim($_POST["sku"]);
    $input_titulo = trim($_POST["titulo"]);
    $input_editorial = trim($_POST["editorial"]);
    $input_stock = trim($_POST["stock"]);
    $input_precio = trim($_POST["precio"]);
    $input_precio_costo = trim($_POST["precioCosto"]);
    $input_serie = trim($_POST["serie"]);
    $input_tomo = trim($_POST["tomo"]);
    $input_formato = trim($_POST["formato"]);
    $input_es_nuevo = $_POST["esNuevo"];

    $productoPDO->sku = $input_sku;
    $productoPDO->titulo = $input_titulo;
    $productoPDO->idEditorial = $input_editorial;
    $productoPDO->stock = $input_stock;
    $productoPDO->precio = $input_precio;
    $productoPDO->precioCosto = $input_precio_costo;
    $productoPDO->idSerie = $input_serie;
    $productoPDO->tomo = $input_tomo;
    $productoPDO->idFormato = $input_formato;
    $productoPDO->nuevo = $input_es_nuevo;
    $productoPDO->id = $id;
    $productoPDO->update();

    header('location: ' . $forwardOk);
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id = trim($_GET["id"]);

        $forwardOk = (isset($_GET["forwardOk"]) && !empty(trim($_GET["forwardOk"]))) ? trim($_GET['forwardOk']) : "productos.php";

        $valProducto = $productoPDO->getById($id);

        $sku = $valProducto["sku"]; 
        $titulo = $valProducto["titulo"]; 
        $editorial = $valProducto["id_editorial"]; 
        $stock = $valProducto["stock"]; 
        $precio = $valProducto["precio"]; 
        $precioCosto = $valProducto["precio_costo"]; 
        $serie = $valProducto["id_serie"]; 
        $tomo = $valProducto["tomo"]; 
        $formato = $valProducto["id_formato"]; 
        $esNuevo = $valProducto["nuevo"]; 

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
        <title>Productos</title>
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
                        <h1 class="mt-4">Actualizar Producto</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos.php">Productos</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Sku</label>
                                    <input type="text" name="sku" value="<?php echo $sku;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Titulo</label>
                                    <input type="text" name="titulo" value="<?php echo $titulo;?>" class="form-control" required="required">
                                </div>

                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" step="0" name="stock" value="<?php echo $stock;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio</label>
                                    <input type="number" step=".01" name="precio" value="<?php echo $precio;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio Costo</label>
                                    <input type="number" step=".01" name="precioCosto" value="<?php echo $precioCosto;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Editorial</label>
                                    <select name="editorial" id="editorial" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $editorialesPDO = new Editorial();
                                            $editoriales = $editorialesPDO->getAll("nombre ASC");
                                            foreach ($editoriales as $val) {
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$editorial) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php 
                                                }
                                            ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Serie</label>
                                    <select name="serie" id="serie" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $productoSeriePDO = new ProductoSerie();
                                            $series = $productoSeriePDO->getAll("nombre ASC");
                                            foreach ($series as $val) {
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$serie) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php 
                                                }
                                            ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tomo</label>
                                    <input type="number" step="0" name="tomo" value="<?php echo $tomo;?>" class="form-control" required="required">
                                </div>
                                <div>
                                    <label>Formato</label>
                                    <select name="formato" id="formato" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $productoFormatoPDO = new ProductoFormato();
                                            $formatos = $productoFormatoPDO->getAll("nombre ASC");
                                            foreach ($formatos as $val) {
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$formato) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php 
                                            
                                            }
                                            ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Es Nuevo</label>
                                    <select name="esNuevo" id="esNuevo" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <option value="1" data-subtext="(TRUE)" <?php if(1==$esNuevo) echo 'selected="selected"'; ?>>Si</option>
                                        <option value="0" data-subtext="(FALSE)" <?php if(0==$esNuevo) echo 'selected="selected"'; ?>>No</option>
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

                <?php include '../footer.php'; ?>

            </div>
        </div>

    </body>
</html>