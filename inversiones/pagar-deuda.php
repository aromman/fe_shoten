<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "../app/models/inversores.php";
require_once "../app/models/inversiones.php";
require_once "../app/models/cuentasMovimientos.php";
require_once "../app/models/cuentas.php";

$inversionesPDO = new Inversiones();
$inversoresPDO = new Inversores();

if(isset($_POST["idInversor"])){

    // Get hidden input value
    $idCuentaOrigen = $_POST["cuentaOrigen"];
    $saldo = $_POST["importe"];
    $fecha = $_POST["fecha"];
    $cotizacion = $_POST["cotizacion"];
    $idInversor = $_POST["idInversor"];
    $nombreInversor = $_POST["nombre"];
    $descripcion = "Pagar Inversion a " . $nombreInversor;
    
    $libros = $saldo / $cotizacion;

    // Resta inversion
    $inversionesPDO->setFecha($fecha);
    $inversionesPDO->setDetalle($descripcion);
    $inversionesPDO->setMonto($saldo * -1);
    $inversionesPDO->setLibros($libros * -1);
    $inversionesPDO->setCotizacion($cotizacion);
    $inversionesPDO->setInvertidoPor($idInversor);
    $inversionesPDO->create();

    // Genero Debito en cuenta origen
    $cuentasMovimientosPDO = new CuentasMovimientos();
    $cuentasMovimientosPDO->setIdCuenta($idCuentaOrigen);
    $cuentasMovimientosPDO->setFecha($fecha);
    $cuentasMovimientosPDO->setTipoMovimiento("D"); // Credito
    $cuentasMovimientosPDO->setDescripcion($descripcion);
    $cuentasMovimientosPDO->setMonto($saldo);
    $cuentasMovimientosPDO->setConsolidado("FALSE");
    $cuentasMovimientosPDO->setIdCausal(4); // PAGOS
    $cuentasMovimientosPDO->create();

    header('location: inversiones.php');
    exit();

} else{

        // Check existence of id parameter before processing further
        if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    
            // Get URL parameter
            $idInversor = trim($_GET["id"]);
            $saldo = trim($_GET["saldo"]);
            $fecha = date("Y-m-d"); 
            
            // Prepare a select statement
            $nombreInversor = "";
            $rs = $inversoresPDO->getById($idInversor);
            if (!is_null($rs)){
                $nombreInversor = $rs['nombre'];
                $idCuentaDestino = $rs['id_cuenta'];
            }

            // cotizacion 
            $cotizacion = 1;
            $rsCotizacion = $inversionesPDO->getCotizacion();
            if (!is_null($rsCotizacion)){
                $cotizacion = $rsCotizacion['cotizacion'];
            } 
    
        } else{
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
        <title>Pagar Inversion</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
                        <h1 class="mt-4">Pagar Inversion</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="inversiones.php">Inversiones</a></li>
                            <li class="breadcrumb-item active">Pagar Inversion</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <input type="hidden" name="cuentaDestino" value="<?php echo $idCuentaDestino; ?>"/>
                                <input type="hidden" name="idInversor" value="<?php echo $idInversor; ?>"/>
                                <input type="hidden" name="cotizacion" value="<?php echo $cotizacion; ?>"/>

                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Inversor</label>
                                    <input type="text" name="nombre" value="<?php echo $nombreInversor;?>" class="form-control" readOnly>
                                </div>
                                <div class="form-group">
                                    <label>Saldo</label>
                                    <input type="number" step="0.1" name="saldo" value="<?php echo $saldo;?>" class="form-control" readOnly>
                                </div>
                                <div class="form-group">
                                    <label>Cuenta</label>
                                    <select name="cuentaOrigen" id="cuentaOrigen" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $cuentaPDO = new Cuentas();
                                            $cuentas = $cuentaPDO->getByType("A","nombre");
                                            foreach ($cuentas as $val) {
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$cuentaOrigen) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                            </option>
                                            <?php 
                                                }
                                            ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Importe</label>
                                    <input type="number" step=".01" name="importe" value="<?php echo $importe;?>" class="form-control" required="required">
                                </div>
                                <br>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="inversiones.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>