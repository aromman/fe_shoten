<?php
// Include config file
include_once('../config.php');
 
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
    
    $input_nombre = trim($_POST["nombre"]);
    $input_cupo_maximo = trim($_POST["cupoMaximo"]);
    $input_cupo_minimo = trim($_POST["cupoMinimo"]);

    $sqlUpdate = 'update productos_serie set nombre = "'.$input_nombre.'", cupo_maximo = '.$input_cupo_maximo.', cupo_minimo='.$input_cupo_minimo;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    // error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    header('location: productos-serie.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rs = $db->query("select * from productos_serie where id = " . $id);
        $val  =  $rs->fetch_assoc();
        $nombre = $val["nombre"]; 
        $cupoMaximo = number_format($val["cupo_maximo"], 0, ".", ""); 
        $cupoMinimo =number_format($val["cupo_minimo"], 0, ".", "");

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
        <title>Productos Serie</title>
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
                        <h1 class="mt-4">Actualizar Producto Serie</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-formato.php">Producto Serie</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto Serie</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Cupo Maximo</label>
                                    <input type="number" step="0" name="cupoMaximo" value="<?php echo $cupoMaximo;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Cupo Minimo</label>
                                    <input type="number" step="0" name="cupoMinimo" value="<?php echo $cupoMinimo;?>" class="form-control" required="required">
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="productos-serie.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>