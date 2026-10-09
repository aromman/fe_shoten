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
    $input_codigo = trim($_POST["codigo"]);
    $input_nombre = trim($_POST["nombre"]);

    $sqlUpdate = 'update productos_ubicacion_tipo set codigo = "'.$input_codigo.'" , nombre = "'.$input_nombre.'"';
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    header('location: productos-ubicacion-tipo.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rs = $db->query("select * from productos_ubicacion_tipo where id = " . $id);
        $val  =  $rs->fetch_assoc();
        $codigo = $val["codigo"]; 
        $nombre = $val["nombre"]; 

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
        <title>Actualizar Producto Ubicacion Tipo</title>
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
                        <h1 class="mt-4">Actualizar Producto Ubicacion Tipo</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-ubicacion-tipo.php">Producto Ubicacion Tipo</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto Ubicacion Tipo</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Codigo</label>
                                    <input type="text" name="codigo" value="<?php echo $codigo;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" required="required">
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="productos-ubicacion-tipo.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>