<?php
// Include config file
include_once '../config.php';
require_once "../app/models/canal.php";

// Define variables and initialize with empty values
$nombre     = $porcentaje     = $cuenta = $canal = "";
$nombre_err = $porcentaje_err = $cuenta_err = $canal_err = "";
$mensaje = "";

//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_nombre = trim($_POST["nombre"]);
    $input_porcentaje = trim($_POST["porcentaje"]);
    $input_cuenta = trim($_POST["cuenta"]);
    $input_canal = trim($_POST["canal"]);

    $sqlUpdate = 'update medio_pago set nombre = "'.$input_nombre.'", cargo_porcentaje = ' .$input_porcentaje. ', id_cuenta = ' . $input_cuenta . ', id_canal = '. $input_canal;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  
    $db->query($sqlUpdate);

    header('location: medios-pago.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rs = $db->query("select mp.*, c.id cuenta from medio_pago mp, cuentas c where mp.id_cuenta = c.id and mp.id = " . $id);
        $val  =  $rs->fetch_assoc();

        $nombre = $val["nombre"];
        $porcentaje = $val["cargo_porcentaje"]; 
        $cuenta = $val["cuenta"];
        $canal = $val["id_canal"];

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
        <title>Medios de Pago</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include_once '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include_once '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Actualizar Medios de Pago</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="medios-pago.php">Medios de Pago</a></li>
                            <li class="breadcrumb-item active">Actualizar Medio de Pago</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Porcentaje</label>
                                    <input type="number" step="0" name="porcentaje" value="<?php echo $porcentaje;?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Cuenta</label>
                                    <select name="cuenta" id="cuenta" class="form-control" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM cuentas");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$cuenta) echo 'selected="selected"'; ?>
                                            >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                        <?php }?>
                                        
                                    </select>
                                </div>
                                <div  class="form-group">
                                    <label>Canal</label>
                                    <select name="canal" id="canal" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $canalPDO = new Canal();
                                            $eventos = $canalPDO->getAllActive("nombre ASC");
                                            foreach ($eventos as $val) {
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$canal) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php 
                                                }
                                                unset($canalPDO);
                                            ?>
                                    </select>
                                </div>
                                <br>
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="medios-pago.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>