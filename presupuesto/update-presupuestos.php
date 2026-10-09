<?php
// Include config file
include_once('../config.php');

require_once "../app/models/canal.php";
require_once "../app/models/gastosClase.php";
 
// Define variables and initialize with empty values
$monto = "";
$monto_err = "";

$mensaje = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_monto = trim($_POST["monto"]);
    $input_clase = trim($_POST["clase"]);
    $input_canal = trim($_POST["canal"]);

    $sqlUpdate = 'update presupuesto set monto = '.$input_monto.', id_clase_gasto = '.$input_clase .', id_canal = '.$input_canal.' WHERE id = '.$id;
    
    $db->query($sqlUpdate);

    header('location: presupuestos.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rs = $db->query("select * from presupuesto where id = " . $id);
        $val  =  $rs->fetch_assoc();

        $monto = $val["monto"]; 
        $clase = $val["id_clase_gasto"]; 
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
        <title>Actualizar Presupuesto</title>
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
                        <h1 class="mt-4">Actualizar Presupuesto</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="presupuestos.php">Presupuestos</a></li>
                            <li class="breadcrumb-item active">Actualizar Presupuesto</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Canal</label>
                                    <select name="canal" id="canal" class="form-control" data-live-search="true" data-size="10" required="required">
                                    <option value="">Seleccione</option>
                                    <?php
                                        $canalPDO = new Canal();
                                        $canales = $canalPDO->getAllActive("nombre ASC");
                                        foreach ($canales as $val) {
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
                                <div class="form-group">
                                    <label>Clase Gasto</label>
                                    <select name="clase" id="clase" class="form-control" data-live-search="true" data-size="10" required="required">
                                    <option value="">Seleccione</option>
                                    <?php
                                        $gastosClasePDO = new GastosClase();
                                        $gastosClase = $gastosClasePDO->getAll("nombre ASC");
                                        foreach ($gastosClase as $val) {
                                    ?>
                                        <option 
                                            value="<?php echo $val['id']?>" 
                                            data-subtext="(<?php echo $val['id']?>)"
                                            <?php if($val['id']==$clase) echo 'selected="selected"'; ?>
                                        >
                                            <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                        </option>
                                    <?php 
                                        }
                                        unset($gastosClasePDO);
                                    ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Monto</label>
                                    <input type="number" step=".01" name="monto" value="<?php echo $monto;?>" class="form-control" required="required">
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="presupuestos.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>