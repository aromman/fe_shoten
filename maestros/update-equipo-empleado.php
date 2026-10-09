<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}


// Include config file
require_once "../app/models/equipoEmpleado.php";
require_once "../app/models/equipo.php";
require_once "../app/models/empleados.php";

// Define variables and initialize with empty values
$id_equipo = $id_empleado = "";
$mensaje = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){

    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];

    $input_id_equipo = trim($_POST["id_equipo"]);
    $input_id_empleado = trim($_POST["id_empleado"]);

    $updateEquipoEmpleadoPDO = new EquipoEmpleado();
    $updateEquipoEmpleadoPDO->id_equipo = $input_id_equipo;
    $updateEquipoEmpleadoPDO->id_empleado = $input_id_empleado;
    $updateEquipoEmpleadoPDO->id = $id;
    $updateEquipoEmpleadoPDO->update();

    header('location: equipo-empleado.php');
    exit();

} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);

        // Prepare a select statement
        $getEquipoEmpleadoPDO = new EquipoEmpleado();
        $val  =  $getEquipoEmpleadoPDO->getById($id);

        $id_equipo = $val["id_equipo"];
        $id_empleado = $val["id_empleado"];

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
        <title>Integrantes de Equipo</title>
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
                        <h1 class="mt-4">Actualizar Integrante de Equipo</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="equipo-empleado.php">Integrantes de Equipo</a></li>
                            <li class="breadcrumb-item active">Actualizar Integrante</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div class="form-group">
                                    <label>Equipo</label>
                                    <select name="id_equipo" id="id_equipo" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $equipoPDO = new Equipo();
                                            $equipos = $equipoPDO->getAll("equipo");
                                            foreach ($equipos as $val) {
                                            ?>
                                            <option
                                                value="<?php echo $val['id']?>"
                                                <?php if($val['id']==$id_equipo) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['equipo'],'UTF-8')?>
                                            </option>
                                            <?php
                                                }
                                                unset($equipoPDO);
                                            ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Empleado</label>
                                    <select name="id_empleado" id="id_empleado" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $empleadoPDO = new Empleado();
                                            $empleados = $empleadoPDO->getAll("apellido");
                                            foreach ($empleados as $val) {
                                            ?>
                                            <option
                                                value="<?php echo $val['id']?>"
                                                <?php if($val['id']==$id_empleado) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['apellido'].', '.$val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php
                                                }
                                                unset($empleadoPDO);
                                            ?>
                                    </select>
                                </div>
                                <br>
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="equipo-empleado.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>
