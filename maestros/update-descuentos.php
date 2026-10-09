<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}


// Include config file
require_once "../app/models/descuento.php";
 
// Define variables and initialize with empty values
$nombre     = $porcentaje     = "";
$nombre_err = $porcentaje_err = "";
$mensaje = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_nombre = trim($_POST["nombre"]);
    $input_tipo = trim($_POST["tipo"]);
    $input_valor = trim($_POST["valor"]);
    $input_porcentaje = trim($_POST["porcentaje"]);

    $updateDescuentoPDO = new Descuento();
    $updateDescuentoPDO->nombre = $input_nombre;
    $updateDescuentoPDO->tipo = $input_tipo;
    $updateDescuentoPDO->valor = $input_valor;
    $updateDescuentoPDO->porcentaje = $input_porcentaje;
    $updateDescuentoPDO->id = $id;
    $updateDescuentoPDO->update();

    header('location: descuentos.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $getDescuentoPDO = new Descuento();    
        $val  =  $getDescuentoPDO->getById($id);

        $nombre = $val["nombre"]; 
        $tipo = $val["tipo"]; 
        $valor = $val["valor"]; 
        $porcentaje = $val["porcentaje"]; 

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
        <title>Descuentos</title>
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
                        <h1 class="mt-4">Actualizar Descuentos</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="descuentos.php">Descuentos</a></li>
                            <li class="breadcrumb-item active">Actualizar Descuentos</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select name="tipo" id="tipo" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <option value="F" data-subtext="(F)" <?php if("F"==$tipo) echo 'selected="selected"'; ?>>Monto Fijo</option>
                                        <option value="P" data-subtext="(P)" <?php if("P"==$tipo) echo 'selected="selected"'; ?>>Porcentaje</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Valor</label>
                                    <input type="number" step="0" name="valor" value="<?php echo $valor;?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Porcentaje</label>
                                    <input type="number" step="0" name="porcentaje" value="<?php echo $porcentaje;?>" class="form-control">
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="descuentos.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>