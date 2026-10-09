<?php
// Initialize the session

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/canal.php";
require_once "../app/models/turnos.php";
require_once "../app/models/turnoParcial.php";
require_once "../app/models/ventas.php";
require_once "../app/models/gastos.php";
require_once "../app/models/cuentasMovimientos.php";
require_once "../app/models/compras.php";
require_once "../app/models/presupuesto.php";
require_once "../app/models/producto.php";
require_once "../app/models/alertas.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

$id_canal = $_SESSION["user.canal"];
$rol = $_SESSION["user.rol"];
$nombreUsuario = $_SESSION["user.username"];

$nombre_canal = '';
$fechaHoy = date("Y-m-d");
$fechaCaja = date("Y-m-d");
$estado = "Cerrado";
$montoApertura = 0;
$montoCierre = 0;
$montoIngresos = 0;

$montoIngresosVentas = 0;
$montoIngresosDepositos = 0;

$montoEngresosCompras = 0;
$montoEgresosGastos = 0;
$montoEgresosExtracciones = 0;

$canalPDO = new Canal();
$canalFisico = $canalPDO->getById($id_canal);
if (!is_null($canalFisico)){
    $nombre_canal = $canalFisico['nombre'];
}

$turnosPDO = new Turnos();

$turno = $turnosPDO->getActiveByCanal($id_canal); // canals por defecto
if (!is_null($turno)){
    $fechaCaja = $turno['fecha'];
    $estado = $turnosPDO->getNombreEstado($turno['estado']);
    $montoApertura = number_format($turno['monto_apertura'], 2, ".", ""); 
    $montoCierre = number_format($turno['monto_cierre'], 2, ".", ""); 
}
        
// ventas
$ventasPDO = new Ventas();
        
$ventaEfectivo = $ventasPDO->getTotalByCanalAndDate($id_canal, $fechaCaja);
if (!is_null($ventaEfectivo)){
    $montoIngresosVentas = $ventaEfectivo['monto'];
}

// gastos efectivo
$gastosPDO = new Gastos();

$gastoEfectivo = $gastosPDO->getTotalByCanalAndDate($id_canal, $fechaCaja);
if (!is_null($gastoEfectivo)){
    $montoEgresosGastos = $gastoEfectivo['monto'];
}

$comprasPDO = new Compra();
$compraEfectivo = $comprasPDO->getTotalByDate($fechaCaja);
if(!is_null($compraEfectivo)){
    $montoEngresosCompras = $compraEfectivo['monto'];
}

$cuentasMovimientosPDO = new CuentasMovimientos();

$movimientoIngresado = $cuentasMovimientosPDO->getTotalByCanalDateAndType($id_canal,$fechaCaja,'C');
if (!is_null($movimientoIngresado)){
    $montoIngresosDepositos = $movimientoIngresado['monto'];
}
$movimientoEgresado = $cuentasMovimientosPDO->getTotalByCanalDateAndType($id_canal,$fechaCaja,"D");
if (!is_null($movimientoEgresado)){
    $montoEgresosExtracciones = $movimientoEgresado['monto'];
}

$montoIngresos = $montoIngresosVentas + $montoIngresosDepositos;
$montoEgresos = $montoEgresosGastos + $montoEngresosCompras + $montoEgresosExtracciones;

$montoTotal = $montoApertura + $montoIngresos - $montoEgresos;

$rsDetalle = $turnosPDO->getDetailsActive($fechaHoy,$id_canal);

$turnoParcialPDO = new TurnoParcial();
$rsParciales = $turnoParcialPDO->getDetailsActive($fechaCaja,$id_canal);

$userCurrent = ($rol == 0 ? null : $nombreUsuario);

$rsUltimoParcial = $turnoParcialPDO->getDetailLastActive($userCurrent);

// recupero Alertas
$alertasPDO = new Alerta();
$rsAlerta = $alertasPDO->getCurrentActive();
$showAlerta = false;
$alertaClass = $leyendaAlerta = $alertaTitulo = "";

if (!is_null($rsAlerta)){
    
    $alertaClass = $rsAlerta['criticidad'];
    if ($alertaClass != 'primary'){
        $showAlerta = true;
        $leyendaAlerta = $rsAlerta['mensaje'];
        $alertaTitulo = $rsAlerta['titulo'];
    }
}

function calcularObjetivos(&$objResto,&$objPresupuesto, &$objPresupuestoClass,$presupuestoValue){
    if ($objResto > 0) {
        if ($objResto >= $presupuestoValue){
            $objPresupuesto = 100;
            $objResto = $objResto - $presupuestoValue; 
            $objPresupuestoClass = 'success';    
        } else {
            $objPresupuesto = ($objResto * 100) / $presupuestoValue;
            $objPresupuestoClass = $objPresupuesto < 30 ? "danger" : ($objPresupuesto < 60 ? "warning" :"primary");
            $objResto = 0;
        }
    } else {
        $objPresupuestoClass = 'danger';    
    }


}

$totalVentasMes = 0;
$ventasMesActual = $ventasPDO->getTotalMonth();
if (!is_null($ventasMesActual)){
    $totalVentasMes = $ventasMesActual['total_ventas'];
}
// objetivos 
$objResto = $totalVentasMes;
$presupuestoPDO = new Presupuesto(); 
// alquiler
$objAlquiler = 0;
$presupuestoValue = $presupuestoPDO->getTotalByClaseGasto(8); 
calcularObjetivos($objResto,$objAlquiler, $objAlquilerClass,$presupuestoValue);
// Materias Primas
$objMateriasPrimas = 0; 
$presupuestoValue = $presupuestoPDO->getTotalByClaseGasto(10);
calcularObjetivos($objResto,$objMateriasPrimas, $objMateriasPrimasClass,$presupuestoValue);
// Sueldos
$objSueldos = 0;
$presupuestoValue = $presupuestoPDO->getTotalByClaseGasto(2); 
calcularObjetivos($objResto,$objSueldos, $objSueldosClass,$presupuestoValue);
// Servicios
$objServicios = 0;
$presupuestoValue = $presupuestoPDO->getTotalByClaseGasto(6); 
calcularObjetivos($objResto,$objServicios, $objServiciosClass,$presupuestoValue);
// Impuestos
$objImpuestos = 0;
$presupuestoValue = $presupuestoPDO->getTotalByClaseGasto(7);
calcularObjetivos($objResto,$objImpuestos, $objImpuestosClass,$presupuestoValue);

// todos
$objTodos = 0;
$objResto = $totalVentasMes;
$presupuestoValue = $presupuestoPDO->getTotalByCanal($id_canal);
calcularObjetivos($objResto,$objTodos, $objTodosClass,$presupuestoValue);

$stockControlado = 0;
$fechaControl = null;
$productoPDO = new Producto();
$productoObj = $productoPDO->getLastStockValidateGroupByDate();
if (!is_null($productoObj)){
    $stockControlado = $productoObj['cantidad'];
    $fechaControl = $productoObj['fecha'];
}
$objStock = $stockControlado;
$objInventario = 0;
calcularObjetivos($objStock,$objInventario, $objInventarioClass,20);

$leyendaBotonCierre = ($rol == 0 ? "Cerrar Turno" : "Cerrar Turno  Parcial");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Gestion de Caja</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script>
        $(document).ready(function(){
            $('input').keyup(function() {

                var montoTotal = $("#montoTotal").val();

                var v10 = $("#monto10").val();
                var v20 = $("#monto20").val();
                var v50 = $("#monto50").val();
                var v100 = $("#monto100").val();
                var v200 = $("#monto200").val();
                var v500 = $("#monto500").val();
                var v1000 = $("#monto1000").val();
                var v2000 = $("#monto2000").val();
                var v10000 = $("#monto10000").val();
                var v20000 = $("#monto20000").val();

                var monto = 0;

                if (v10) {
                    monto = monto + (v10 * 10);
                }
                if (v20) {
                    monto = monto + (v20 * 20);
                }
                if (v50) {
                    monto = monto + (v50 * 50);
                }
                if (v100) {
                    monto = monto + (v100 * 100);
                }
                if (v200) {
                    monto = monto + (v200 * 200);
                }
                if (v500) {
                    monto = monto + (v500 * 500);
                }
                if (v1000) {
                    monto = monto + (v1000 * 1000);
                }
                if (v2000) {
                    monto = monto + (v2000 * 2000);
                }
                if (v10000) {
                    monto = monto + (v10000 * 10000);
                }
                if (v20000) {
                    monto = monto + (v20000 * 20000);
                }
                
                $("#montoCierre").val(Number.parseFloat(monto).toFixed(2));

                $("#montoResto").val(Number.parseFloat(montoTotal - monto).toFixed(2));

            });

        });
        </script>


    </head>
    <body class="sb-nav-fixed">
        <?php include_once '../topBar.php';?>
        
        <div id="layoutSidenav">
            
            <?php include_once '../sidebar.php';?>
            
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h4 class="mt-4">Resumen Caja</h4>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Turno Actual</li>
                        </ol>

                        <div class="row">
                            <?php 
                                if ($showAlerta){
                            ?>
                            <div class="alert alert-<?php echo $alertaClass;?>" role="alert">
                                <h4 class="alert-heading"><?php echo $alertaTitulo;?></h4>
                                <p><?php echo $leyendaAlerta;?></p>
                            </div>                        
                            <?php 
                            }     
                            ?>   
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row justify-content-end">                                    
                                        <div class="col-auto">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <?php
                                                if ($estado == "Abierto"){
                                                ?>
                                                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cerrarModal"><i class="fa-solid fa-circle-plus"></i> <?php echo $leyendaBotonCierre;?></a>
                                                <?php
                                                    } else {
                                                ?>
                                                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#abrirModal"><i class="fa-solid fa-circle-plus"></i> Abrir Turno</a>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="container py-6">
                            <div class="row row-cols-1 row-cols-md-4 g-4">

                                <div class="col-xl-2 col-md-5">
                                <div class="card h-100 text-center shadow">
                                    <div class="card-body">
                                    <div class="display-4 text-primary mb-2">
                                        <i class="fa-solid fa-sack-dollar"></i>
                                    </div>
                                    <h2 class="card-title mb-3">$ <?php echo number_format($montoTotal, 2, ".", ""); ?></h2>
                                    <p class="card-text text-muted">Saldo Actual</p>
                                    </div>
                                </div>
                                </div>

                                <div class="col-xl-2 col-md-5">
                                <div class="card h-100 text-center shadow">
                                    <div class="card-body">
                                    <div class="display-4 text-info mb-2">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <h2 class="card-title mb-3">$ <?php echo number_format($montoApertura, 2, ".", ""); ?></h2>
                                    <p class="card-text text-muted">Apertura <?php echo date('d/m/Y', strtotime($fechaCaja));?></p>
                                    </div>
                                </div>
                                </div>
                                
                                <div class="col-xl-2 col-md-5">
                                <div class="card h-100 text-center shadow">
                                    <div class="card-body">
                                    <div class="display-4 text-success mb-2">
                                        <i class="fa-solid fa-chart-line"></i>
                                    </div>
                                    <h2 class="card-title mb-3">$ <?php echo number_format($montoIngresosVentas, 2, ".", ""); ?></h2>
                                    <p class="card-text text-muted">Total Ventas</p>
                                    </div>
                                </div>
                                </div>
                                
                                <div class="col-xl-2 col-md-5">
                                <div class="card h-100 text-center shadow">
                                    <div class="card-body">
                                    <div class="display-4 text-warning mb-2">
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </div>
                                    <h2 class="card-title mb-3">$ <?php echo number_format($montoIngresosDepositos, 2, ".", ""); ?></h2>
                                    <p class="card-text text-muted">Ingresos</p>
                                    </div>
                                </div>
                                </div>
                                
                                <div class="col-xl-2 col-md-5">
                                <div class="card h-100 text-center shadow">
                                    <div class="card-body">
                                    <div class="display-4 text-danger mb-2">
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </div>
                                    <h2 class="card-title mb-3">$ <?php echo number_format($montoEgresos, 2, ".", ""); ?></h2>
                                    <p class="card-text text-muted">Egresos</p>
                                    </div>
                                </div>
                                </div>

                                <div class="col-xl-2 col-md-5">
                                <div class="card h-100 text-center shadow">
                                    <div class="card-body">
                                        <div class="display-4 text-<?php echo $objInventarioClass;?> mb-2">
                                            <i class="fa-solid fa-cart-flatbed"></i>
                                        </div>
                                        <h2 class="card-title mb-3"><?php echo number_format($stockControlado, 0, ".", ""); ?> Articulos</h2>
                                        <p class="card-text">Control: <?php echo date('d/m/Y', strtotime($fechaControl));?>
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-<?php echo $objInventarioClass;?>" 
                                                    role="progressbar" 
                                                    style="width: <?php echo $objInventario;?>%" aria-valuenow="<?php echo $objInventario;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </p>                                        
                                    </div>
                                </div>
                                </div>

                            </div>
                            </div>

                        </div>
                        
                        <br/>

                        <div class="row">

                            <div class="container py-6">
                                <div class="row row-cols-1 row-cols-md-4 g-4">

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                                <div class="display-4 text-primary mb-2">
                                                    <i class="fa-solid fa-house"></i>
                                                </div>
                                                <h2 class="card-title mb-3"><?php echo number_format($objAlquiler, 2, ".", ""); ?>%</h2>
                                                <p class="card-text">Objetivo Alquiler
                                                    <div class="col">
                                                        <div class="progress progress-sm mr-2">
                                                            <div class="progress-bar bg-<?php echo $objAlquilerClass;?>" 
                                                            role="progressbar" 
                                                            style="width: <?php echo $objAlquiler;?>%" aria-valuenow="<?php echo $objAlquiler;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                                <div class="display-4 text-danger mb-2">
                                                    <i class="fa-solid fa-file-circle-question"></i>
                                                </div>
                                                <h2 class="card-title mb-3"><?php echo number_format($objMateriasPrimas, 2, ".", ""); ?>%</h2>
                                                <p class="card-text">Objetivo Reposicion
                                                    <div class="col">
                                                        <div class="progress progress-sm mr-2">
                                                            <div class="progress-bar bg-<?php echo $objMateriasPrimasClass;?>" 
                                                            role="progressbar" 
                                                            style="width: <?php echo $objMateriasPrimas;?>%" aria-valuenow="<?php echo $objMateriasPrimas;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                                <div class="display-4 text-success mb-2">
                                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                                </div>
                                                <h2 class="card-title mb-3"><?php echo number_format($objSueldos, 2, ".", ""); ?>%</h2>
                                                <p class="card-text">Objetivo Sueldos 
                                                    <div class="col">
                                                        <div class="progress progress-sm mr-2">
                                                            <div class="progress-bar bg-<?php echo $objSueldosClass;?>" 
                                                            role="progressbar" 
                                                            style="width: <?php echo $objSueldos;?>%" aria-valuenow="<?php echo $objSueldos;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </p>

                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                                <div class="display-4 text-info mb-2">
                                                    <i class="fa-solid fa-plug-circle-check"></i>
                                                </div>
                                                <h2 class="card-title mb-3"><?php echo number_format($objServicios, 2, ".", ""); ?>%</h2>
                                                <p class="card-text">Objetivo Servicios
                                                    <div class="col">
                                                        <div class="progress progress-sm mr-2">
                                                            <div class="progress-bar bg-<?php echo $objServiciosClass;?>" 
                                                            role="progressbar" 
                                                            style="width: <?php echo $objServicios;?>%" aria-valuenow="<?php echo $objAlquiler;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                                <div class="display-4 text-warning mb-2">
                                                    <i class="fa-solid fa-file-invoice"></i>
                                                </div>
                                                <h2 class="card-title mb-3"><?php echo number_format($objImpuestos, 2, ".", ""); ?>%</h2>
                                                <p class="card-text">Objetivo Impuestos
                                                    <div class="col">
                                                        <div class="progress progress-sm mr-2">
                                                            <div class="progress-bar bg-<?php echo $objImpuestosClass;?>" 
                                                            role="progressbar" 
                                                            style="width: <?php echo $objImpuestos;?>%" aria-valuenow="<?php echo $objImpuestos;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                                <div class="display-4 text-secondary mb-2">
                                                    <i class="fa-solid fa-person-praying"></i>
                                                </div>
                                                <h2 class="card-title mb-3"><?php echo number_format($objTodos, 2, ".", ""); ?>%</h2>
                                                <p class="card-text">Objetivo Todos
                                                    <div class="col">
                                                        <div class="progress progress-sm mr-2">
                                                            <div class="progress-bar bg-<?php echo $objTodosClass;?>" role="progressbar" style="width: <?php echo $objTodos;?>%" aria-valuenow="<?php echo $objTodos;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>


                        <br/>                       
                        
                        <div class="row">
                            <div class="col-lg-6 mb-4">

                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Detalle Movimientos de Hoy
                                </div>

                                <div class="card-body">
                                    <br>            
                                    <div class="row justify-content-end">                                    
                                        <div class="col-auto">
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pagarModal"><i class="fa-solid fa-circle-plus"></i> Nuevo Movimiento</a>
                                        </div>
                                    </div>
                                    <br>

                                    <div class="table-responsive">

                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Detalle</th>
                                                <th class="text-center">Ingreso</th>
                                                <th class="text-center">Egreso</th>
                                                <th class="text-center">Saldo</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                                if ( !is_null($rsDetalle) ) {
                                                    $saldo = 0; // inicializo con Mondo de Apertura
                                                    foreach ($rsDetalle as $row) {

                                                        // recupero datos 
                                                        $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                        $colDetalle = $row['detalle'];
                                                        $colColTipo = $row['tipo'];
                                                        $colMonto = $row['monto'];

                                                        $colMontoIngreso = $colMontoEgreso = ""; // inicializo 
                                                        
                                                        if ($colColTipo == 'I'){
                                                            // INGRESO
                                                            $colMontoIngreso = number_format($colMonto, 2, ".", "");
                                                            $saldo += $colMonto;  
                                                        } else {
                                                            // INGRESO
                                                            $colMontoEgreso = number_format($colMonto, 2, ".", "");
                                                            $saldo -= $colMonto;  
                                                        }
                                                ?>
                                                    <tr>
                                                        <td><?php echo $colFecha;?></td>
                                                        <td><?php echo $colDetalle; ?></td>
                                                        <td><?php echo $colMontoIngreso; ?></td>
                                                        <td><?php echo $colMontoEgreso; ?></td>
                                                        <td><?php echo number_format($saldo, 2, ".", ""); ?></td>
                                                    </tr>
                                            <?php
                                                    }
                                                }
                                                else
                                                {
                                                echo '0 results';
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-6 mb-4">

                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Detalle Billetes
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Billete</th>
                                                    <th class="text-center">Cantidad</th>
                                                    <th class="text-center">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $saldo = 0; // inicializo con Mondo de Apertura
                                                if ( !is_null($rsUltimoParcial) ) {
                                                    
                                                    $colCant10 = $rsUltimoParcial['monto10'];
                                                    if (!is_null($colCant10) and $colCant10 >0){
                                                        $colMonto10 = $colCant10 * 10;
                                                        $saldo+=$colMonto10;
                                            ?>
                                                        <tr>
                                                            <td>$ 10</td>
                                                            <td align="right"><?php echo number_format($colCant10, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto10, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant20 = $rsUltimoParcial['monto20'];
                                                    if (!is_null($colCant20) and $colCant20 >0){
                                                        $colMonto20 = $colCant20 * 20;
                                                        $saldo+=$colMonto20;
                                            ?>
                                                        <tr>
                                                            <td>$ 20</td>
                                                            <td align="right"><?php echo number_format($colCant20, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto20, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant50 = $rsUltimoParcial['monto50'];
                                                    if (!is_null($colCant50) and $colCant50 >0){
                                                        $colMonto50 = $colCant50 * 50;
                                                        $saldo+=$colMonto50;
                                            ?>
                                                        <tr>
                                                            <td>$ 50</td>
                                                            <td align="right"><?php echo number_format($colCant50, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto50, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php            
                                                    }

                                                    $colCant100 = $rsUltimoParcial['monto100'];
                                                    if (!is_null($colCant100) and $colCant100 >0){
                                                        $colMonto100 = $colCant100 * 100;
                                                        $saldo+=$colMonto100;
                                            ?>
                                                        <tr>
                                                            <td>$ 100</td>
                                                            <td align="right"><?php echo number_format($colCant100, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto100, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant200 = $rsUltimoParcial['monto200'];
                                                    if (!is_null($colCant200) and $colCant200 >0){
                                                        $colMonto200 = $colCant200 * 200;
                                                        $saldo+=$colMonto200;
                                            ?>
                                                        <tr>
                                                            <td>$ 200</td>
                                                            <td align="right"><?php echo number_format($colCant200, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto200, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant500 = $rsUltimoParcial['monto500'];
                                                    if (!is_null($colCant500) and $colCant500 >0){
                                                        $colMonto500 = $colCant500 * 500;
                                                        $saldo+=$colMonto500;
                                            ?>
                                                        <tr>
                                                            <td>$ 500</td>
                                                            <td align="right"><?php echo number_format($colCant500, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto500, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }
                                                    $colCant1000 = $rsUltimoParcial['monto1000'];
                                                    if (!is_null($colCant1000) and $colCant1000 >0){
                                                        $colMonto1000 = $colCant1000 * 1000;
                                                        $saldo+=$colMonto1000;
                                            ?>
                                                        <tr>
                                                            <td>$ 1000</td>
                                                            <td align="right"><?php echo number_format($colCant1000, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto1000, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant2000 = $rsUltimoParcial['monto2000'];
                                                    if (!is_null($colCant2000) and $colCant2000 >0){
                                                        $colMonto2000 = $colCant2000 * 2000;
                                                        $saldo+=$colMonto2000;
                                            ?>
                                                        <tr>
                                                            <td>$ 2000</td>
                                                            <td align="right"><?php echo number_format($colCant2000, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto2000, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant10000 = $rsUltimoParcial['monto10000'];
                                                    if (!is_null($colCant10000) and $colCant10000 >0){
                                                        $colMonto10000 = $colCant10000 * 10000;
                                                        $saldo+=$colMonto10000;
                                            ?>
                                                        <tr>
                                                            <td>$ 10000</td>
                                                            <td align="right"><?php echo number_format($colCant10000, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto10000, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }

                                                    $colCant20000 = $rsUltimoParcial['monto20000'];
                                                    if (!is_null($colCant20000) and $colCant20000 >0){
                                                        $colMonto20000 = $colCant20000 * 20000;
                                                        $saldo+=$colMonto20000;
                                            ?>
                                                        <tr>
                                                            <td>$ 20000</td>
                                                            <td align="right"><?php echo number_format($colCant20000, 0, ".", ""); ?></td>
                                                            <td align="right">$ <?php echo number_format($colMonto20000, 2, ".", ""); ?></td>
                                                        </tr>
                                            <?php
                                                    }
                                                }
                                            ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">Total Ultimo Cierre</td>
                                                    <td align="right">$ <?php echo number_format($saldo, 2, ".", ""); ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Cierre de Turnos Parciales
                                </div>
                                <div class="card-body">

                                    <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tbTurnosParciales">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Monto Apertura</th>
                                                <th class="text-center">Monto Cierre</th>
                                                <th class="text-center">Usuario</th>
                                                <th class="text-center">Fecha Registo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                if ( !is_null($rsParciales) ) {
                                                    $saldo = $montoApertura; // inicializo con Mondo de Apertura
                                                    foreach ($rsParciales as $row) {

                                                        // recupero datos 
                                                        $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                        $colMontoApertura = $row['monto_apertura'];
                                                        $colMontoCierre = $row['monto_cierre'];
                                                        $colUsuario = $row['username'];
                                                        $colRegistro = date('d/m/Y H:i:s', strtotime($row['updateDate']));
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $colFecha;?></td>
                                                        <td align="right">$ <?php echo number_format($colMontoApertura, 2, ".", ""); ?></td>
                                                        <td align="right">$ <?php echo number_format($colMontoCierre, 2, ".", ""); ?></td>
                                                        <td class="text-center"><?php echo $colUsuario; ?></td>
                                                        <td class="text-center"><?php echo $colRegistro; ?></td>
                                                    </tr>
                                            <?php
                                                    }
                                                }
                                                else
                                                {
                                                echo '0 results';
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </main>
                
                <?php include_once '../footer.php';?>

            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        <?php include_once 'abrirModal.php'; ?>
        <?php include_once 'cerrarModal.php'; ?>
        <?php include_once 'pagarModal.php'; ?>

        <script>
        $('#cerrarModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var tipo = applicant.data('tipo');
            var modal = $(this);
            modal.find('input[name="tipo"]').val(tipo);
        });
        </script>


    </body>
</html>
