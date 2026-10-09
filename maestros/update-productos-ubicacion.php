<?php
// Include config file
include_once('../config.php');
require_once "../app/models/canal.php";

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
    $input_canal = trim($_POST["canal"]);
    $input_tipo_nivel_01 = trim($_POST["tipoNivel01"]);
    $input_codigo_nivel_01 = trim($_POST["codigoNivel01"]);
    $input_tipo_nivel_02 = trim($_POST["tipoNivel02"]);
    $input_codigo_nivel_02 = trim($_POST["codigoNivel02"]);

    $sqlUpdate = 'update productos_ubicacion set id_canal='.$input_canal.', id_nivel_tipo_01='.$input_tipo_nivel_01.', nivel_codigo_01='.$input_codigo_nivel_01.', id_nivel_tipo_02='.$input_tipo_nivel_02.', nivel_codigo_02='.$input_codigo_nivel_02;
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    header('location: productos-ubicacion.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rs = $db->query("select * from productos_ubicacion where id = " . $id);
        $val  =  $rs->fetch_assoc();
        
        $canal = $val['id_canal'];
        $tipoNivel01 = $val['id_nivel_tipo_01'];
        $codigoNivel01 = $val['nivel_codigo_01'];
        $tipoNivel02 = $val['id_nivel_tipo_02'];
        $codigoNivel02 = $val['nivel_codigo_02'];

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
        <title>Actualizar Producto Ubicacion</title>
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
                        <h1 class="mt-4">Actualizar Producto Ubicacion</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-ubicacion-tipo.php">Producto Ubicacion</a></li>
                            <li class="breadcrumb-item active">Actualizar Producto Ubicacion</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                
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


                                <div class="form-group">
                                    <label>Tipo Nivel 01</label>
                                    <select name="tipoNivel01" id="tipoNivel01" class="form-control" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM productos_ubicacion_tipo");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$tipoNivel01) echo 'selected="selected"'; ?>
                                            >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                        <?php }?>
                                        
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Codigo Nivel 01</label>
                                    <input type="text" name="codigoNivel01" value="<?php echo $codigoNivel01;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Tipo Nivel 02</label>
                                    <select name="tipoNivel02" id="tipoNivel02" class="form-control" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM productos_ubicacion_tipo");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$tipoNivel02) echo 'selected="selected"'; ?>
                                            >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                        <?php }?>
                                        
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Codigo Nivel 02</label>
                                    <input type="text" name="codigoNivel02" value="<?php echo $codigoNivel02;?>" class="form-control" required="required">
                                </div>

                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="productos-ubicacion.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>