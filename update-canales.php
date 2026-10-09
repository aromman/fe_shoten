<?php

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "app/models/canal.php";
 
// Define variables and initialize with empty values
$tipo     = $nombre     = $fechaInicio     = $fechaFin = $estado = "";
$tipo_err = $nombre_err = $fechaInicio_err = $fechaFin_err = $estado_err = "";

$mensaje = "";

//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_tipo = trim($_POST["tipo"]);
    $input_nombre = trim($_POST["nombre"]);
    $input_fechaInicio = trim($_POST["fechaInicio"]);
    $input_fechaFin = trim($_POST["fechaFin"]);
    $input_activo = trim($_POST["activo"]);
    $input_vende = trim($_POST["vende"]);
    $input_punto_venta = trim($_POST["puntoVenta"]);
    
    $canalPDO = new Canal();
    $canalPDO->id = $id;
    $canalPDO->tipo = $input_tipo;
    $canalPDO->nombre = $input_nombre;
    $canalPDO->fechaInicio = $input_fechaInicio;
    $canalPDO->fechaFin = $input_fechaFin;
    $canalPDO->activo = $input_activo;
    $canalPDO->vende = $input_vende;
    $canalPDO->puntoVenta = $input_punto_venta;
    $canalPDO->update();
    
    header('location: canales.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $canalPDO = new Canal();    
        $val = $canalPDO->getById($id);

        $tipo = $val["tipo"]; 
        $nombre = $val["nombre"]; 
        $fechaInicio = $val["fechaInicio"]; 
        $fechaFin = $val["fechaFin"]; 
        $activo = $val["activo"]; 
        $vende = $val["vende"]; 
        $puntoVenta = $val["punto_venta"]; 

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
        <title>Canales</title>
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
                        <h1 class="mt-4">Actualizar Canal</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="gastos.php">Canales</a></li>
                            <li class="breadcrumb-item active">Actualizar Canal</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div>
                                    <label>Tipo</label>
                                    <select name="tipo" id="tipo" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <option value="FISICO" data-subtext="(Fisico)" <?php if("FISICO"==$tipo) echo 'selected="selected"'; ?>>FISICO</option>
                                        <option value="VIRTUAL" data-subtext="(Virtual)" <?php if("VIRTUAL"==$tipo) echo 'selected="selected"'; ?>>VIRTUAL</option>
                                        <option value="EVENTO" data-subtext="(Evento)"<?php if("EVENTO"==$tipo) echo 'selected="selected"'; ?>>EVENTO</option>
                                        <option value="TORNEO" data-subtext="(Torneo)"<?php if("TORNEO"==$tipo) echo 'selected="selected"'; ?>>TORNEO</option>
                                        <option value="CAFETERIA" data-subtext="(Cafeteria)"<?php if("CAFETERIA"==$tipo) echo 'selected="selected"'; ?>>CAFETERIA</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Fecha Inicio</label>
                                    <input type="date" name="fechaInicio" value="<?php echo $fechaInicio;?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Fecha Fin</label>
                                    <input type="date" name="fechaFin" value="<?php echo $fechaFin;?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select name="activo" id="activo" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <option value="1" data-subtext="(TRUE)" <?php if(1==$activo) echo 'selected="selected"'; ?>>Activo</option>
                                        <option value="0" data-subtext="(FALSE)" <?php if(0==$activo) echo 'selected="selected"'; ?>>Finalizado</option>
                                    </select>
                                </div>                                
                                <div class="form-group">
                                    <label>Es canal de venta</label>
                                    <select name="vende" id="vende" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <option value="1" data-subtext="(TRUE)" <?php if(1==$vende) echo 'selected="selected"'; ?>>SI</option>
                                        <option value="0" data-subtext="(FALSE)" <?php if(0==$vende) echo 'selected="selected"'; ?>>NO</option>
                                    </select>
                                </div>     
                                <div class="form-group">
                                    <label>Punto de Venta</label>
                                    <input type="number" step="0" name="puntoVenta" value="<?php echo $puntoVenta;?>" class="form-control">
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="canales.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

    </body>
</html>