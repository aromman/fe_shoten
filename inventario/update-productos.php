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
 
// Define variables and initialize with empty values
$sku     = $titulo     = $editorial = $stock = $precio = "";
$sku_err = $titulo_err = $editorial_err = $stock_err = $precio_err ="";

$mensaje = "";
$error = "";

$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$productoUpdatePDO = new Producto();

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = (int) $_POST["id"];
    $forwardOk = isset($_POST["forwardOk"]) && trim($_POST["forwardOk"]) !== '' ? trim($_POST["forwardOk"]) : 'control.php';
    
    $input_stock = trim($_POST["stock"]);
    $input_precio = trim($_POST["precio"]);

    $productoUpdatePDO->id = $id;
    $productoUpdatePDO->stock = $input_stock;
    $productoUpdatePDO->precio = $input_precio;
    $productoUpdatePDO->update();

    header('location: ' . $forwardOk);
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id = (int) trim($_GET["id"]);

        $forwardOk = (isset($_GET["forwardOk"]) && !empty(trim($_GET["forwardOk"]))) ? trim($_GET['forwardOk']) : "control.php";
        
        // Prepare a select statement
        
        $valProducto = $productoUpdatePDO->getById($id);

        if (!$valProducto) {
            $error = "No se encontro el producto solicitado.";
            $titulo = "";
            $stock = "";
            $precio = "";
        } else {

            $titulo = $valProducto["titulo"]; 
            $stock = $valProducto["stock"]; 
            $precio = $valProducto["precio"]; 
        }

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
                        <h2 class="mt-4"><?php echo $escape($mensaje); ?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="control.php">Control</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto</li>
                        </ol>
                        <div class="card-body">
                            <?php if ($error !== "") { ?>
                                <div class="alert alert-danger" role="alert"><?php echo $escape($error); ?></div>
                                <a href="<?php echo $escape($forwardOk); ?>" class="btn btn-secondary">Volver</a>
                            <?php } else { ?>
                            <form action="<?php echo $escape($_SERVER['PHP_SELF']); ?>" method="post">
                                <div class="form-group">
                                    <label>Titulo</label>
                                    <input type="text" name="titulo" value="<?php echo $escape($titulo); ?>" class="form-control" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" step="0" name="stock" value="<?php echo $escape($stock); ?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio</label>
                                    <input type="number" step=".01" name="precio" value="<?php echo $escape($precio); ?>" class="form-control" required="required">
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $escape($id); ?>"/>
                                <input type="hidden" name="forwardOk" value="<?php echo $escape($forwardOk); ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="<?php echo $escape($forwardOk); ?>" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                            <?php } ?>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php'; ?>

            </div>
        </div>

    </body>
</html>
