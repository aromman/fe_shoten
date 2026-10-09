<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/inversores.php";
require_once "../app/models/inversiones.php";
require_once "../app/models/cuentasMovimientos.php";

// obtener cantidad de libros invertidos
$inversionesPDO = new Inversiones();
$inversoresPDO = new Inversores();
 
// Define variables and initialize with empty values
$nombre = $porcentaje = $id_proveedor = $consignacion = $montoFijo = "";
$nombre_err = $porcentaje_err = $id_proveedor_err = $consignacion_err = $montoFijo_err = "";

$mensaje = "";

// Processing form data when form is submitted
if(isset($_POST["cuentaOrigen"]) && !empty($_POST["cuentaOrigen"])){

    $mensaje = "Actualizando";

    // Get hidden input value
    $idCuentaOrigen = $_POST["cuentaOrigen"];
    $saldo = $_POST["saldo"];
    $fecha = $_POST["fecha"];
    $cotizacion = $_POST["cotizacion"];
    $idInversor = $_POST["idInversor"];
    $descripcion = "Pase de Deuda a Inversion";

    // inserto inversion
    $inversionesPDO->setFecha($fecha);
    $inversionesPDO->setDetalle($descripcion);
    $inversionesPDO->setMonto($saldo);
    $inversionesPDO->setLibros($saldo / $cotizacion);
    $inversionesPDO->setCotizacion($cotizacion);
    $inversionesPDO->setInvertidoPor($idInversor);
    $inversionesPDO->create();

    // Genero Credito en cuenta para dejarla en zero
    $cuentasMovimientosPDO = new CuentasMovimientos();
    $cuentasMovimientosPDO->setIdCuenta($idCuentaOrigen);
    $cuentasMovimientosPDO->setFecha($fecha);
    $cuentasMovimientosPDO->setTipoMovimiento("c"); // Credito
    $cuentasMovimientosPDO->setDescripcion($descripcion);
    $cuentasMovimientosPDO->setMonto($saldo);
    $cuentasMovimientosPDO->setConsolidado("FALSE");
    $cuentasMovimientosPDO->setIdCausal(4); // PAGOS
    $cuentasMovimientosPDO->create();

    header('location: cuentas.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $idCuenta =  trim($_GET["id"]);
        $saldo = (trim($_GET["saldo"])) * -1;
        $fecha = date("Y-m-d"); 
        
        // Prepare a select statement
        $nombreInversor = "";
        $idInversor = "";
        $rs = $inversoresPDO->getByCuenta($idCuenta);
        if (!is_null($rs)){
            $nombreInversor = $rs['nombre'];
            $idInversor = $rs['id'];
        }

        // cotizacion 
        $cotizacion = 1;
        $rsCotizacion = $inversionesPDO->getCotizacion();
        if (!is_null($rsCotizacion)){
            $cotizacion = $rsCotizacion['cotizacion'];
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
        <title>Invertir</title>
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
                        <h1 class="mt-4">Invertir Saldo de Cuenta</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="cuentas.php">Cuentas</a></li>
                            <li class="breadcrumb-item active">Invertir</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <input type="hidden" name="cuentaOrigen" value="<?php echo $idCuenta; ?>"/>
                                <input type="hidden" name="idInversor" value="<?php echo $idInversor; ?>"/>

                                <div class="form-group">
                                    <label>Inversor</label>
                                    <input type="text" name="nombre" value="<?php echo $nombreInversor;?>" class="form-control" readOnly>
                                </div>
                                <div class="form-group">
                                    <label>Saldo</label>
                                    <input type="number" step="0.1" name="saldo" value="<?php echo $saldo;?>" class="form-control" readOnly>
                                </div>
                               
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>

                                <div class="form-group">
                                    <label>Cotizacion</label>
                                    <input type="number" step="0.1" name="cotizacion" value="<?php echo $cotizacion;?>" class="form-control" required="required">
                                </div>

                                <br> 
                                <input type="submit" class="btn btn-primary" value="Grabar">
                                <a href="cuentas.php" class="btn btn-secondary ml-2">Cancelar</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>