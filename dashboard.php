<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "app/models/ventas.php";
require_once "app/models/gastos.php";
require_once "app/models/compras.php";
require_once "app/models/cuentas.php";
require_once "app/models/consignaciones.php";
require_once "app/models/presupuesto.php";

$id_canal = $_SESSION["user.canal"];

$total_ventas = $total_gastos = $total_compras = $total_cuentas = $total_debo = $total_consignacion = 0;

$ventasPDO = new Ventas();
$rsVentas = $ventasPDO->getTotalMonth();
if (!is_null($rsVentas)){
    $total_ventas = $rsVentas['total_ventas'];
}

$comprasPDO = new Compra();
$rsCompras = $comprasPDO->getTotalMonth();
if (!is_null($rsCompras)){
    $total_compras = $rsCompras['total_compras'];
}

$gastosPDO = new Gastos();
$rsGastos = $gastosPDO->getTotalByCanal($id_canal);
if (!is_null($rsGastos)){
    $total_gastos = $rsGastos;
}

$cuentasPDO = new Cuentas();
$rsCuentas = $cuentasPDO->getTotalByType("A");
if (!is_null($rsCuentas)){
    $total_cuentas = $rsCuentas['monto'];
}
$rsDeudas = $cuentasPDO->getTotalByType("P");
if (!is_null($rsDeudas)){
    $total_debo = $rsDeudas['monto'];
    if ($total_debo > 0){
        $total_debo = 0;
    } else {
        $total_debo = $total_debo * -1;
    }
}

$consignacionesPDO = new Consignaciones();
$rsConsignaciones = $consignacionesPDO->getTotalPendientePago();
if (!is_null($rsConsignaciones)){
    $total_consignacion = $rsConsignaciones['monto'];
}

$presupuestoPDO = new Presupuesto(); 
$presupuestoValue = $presupuestoPDO->getTotalByCanal($id_canal);

$puntoEquilibrio = $presupuestoValue;
$gananciaReal  = $total_ventas - $presupuestoValue;

$dias = date("t");


?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Tablero</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <style>
            .row-striped:nth-of-type(odd){
            background-color: #efefef;
            border-left: 4px #000000 solid;
            }

            .row-striped:nth-of-type(even){
            background-color: #ffffff;
            border-left: 4px #efefef solid;
            }

            .row-striped {
                padding: 15px 0;
            } 
            
            .badge-secondary {
                color: #fff;
                background-color: #6c757d;
            }            
        </style>

    </head>

    <body class="sb-nav-fixed">

        <?php include 'topBar.php';?>
        
        <div id="layoutSidenav">
            
            <?php include 'sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>

                    <!-- Begin Page Content -->
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Control Evolucion</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>


                        <!-- Content Row -->
                        <div class="row">
                            <div class="container py-5">
                                <div class="row row-cols-1 row-cols-md-4 g-4">    

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                            <div class="display-4 text-warning mb-2">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </div>
                                            <h2 class="card-title mb-3">$ <?php echo number_format($total_ventas, 2, ".", ""); ?></h2>
                                            <p class="card-text text-muted">Ingreso</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                            <div class="display-4 text-danger mb-2">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </div>
                                            <h2 class="card-title mb-3">$ <?php echo number_format($total_gastos, 2, ".", ""); ?></h2>
                                            <p class="card-text text-muted">Gaste</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                            <div class="display-4 text-success mb-2">
                                                <i class="fa-solid fa-chart-line"></i>
                                            </div>
                                            <h2 class="card-title mb-3">$ <?php echo number_format($total_compras, 2, ".", ""); ?></h2>
                                            <p class="card-text text-muted">Inverti - Compre</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                    <div class="card h-100 text-center shadow">
                                        <div class="card-body">
                                        <div class="display-4 text-primary mb-2">
                                            <i class="fa-solid fa-sack-dollar"></i>
                                        </div>
                                        <h2 class="card-title mb-3">$ <?php echo number_format($total_cuentas, 2, ".", ""); ?></h2>
                                        <p class="card-text text-muted">Tengo</p>
                                        </div>
                                    </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                            <div class="display-4 text-info mb-2">
                                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                            </div>
                                            <h2 class="card-title mb-3">$ <?php echo number_format($total_debo, 2, ".", ""); ?></h2>
                                            <p class="card-text text-muted">Debo</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-2 col-md-5">
                                        <div class="card h-100 text-center shadow">
                                            <div class="card-body">
                                            <div class="display-4 text-secondary mb-2">
                                                <i class="fa-solid fa-person-praying"></i>
                                            </div>
                                            <h2 class="card-title mb-3">$ <?php echo number_format($total_consignacion, 2, ".", ""); ?></h2>
                                            <p class="card-text text-muted">Consignaciones</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-lg-6 mb-4">
                            
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Evolucion de Ventas</h6>
                                    <h7 class="m-0 font-weight-bold text-primary">Presupuesto sobre Ventas</h7>
                                </div>
                                <?php

                                    $actualArray = array();
                                    $rs = $ventasPDO->getAllMonthGrouByDay();
                                    $cantidadVentas = 0;
                                    $topXVentas = 0;
                                    $diaIdx = 1;
                                    if (!is_null($rs)) {
                                        foreach ($rs as $row) {
                                            while($diaIdx < $row['dia']){
                                                // avanzo dias hasta
                                                $actualArray[] = $cantidadVentas;        
                                                $diaIdx += 1;
                                            }
                                            // cuento las ventas a hoy
                                            $puntosVentas = $row['total_ventas'];
                                            $cantidadVentas = $cantidadVentas + $puntosVentas;
                                            if ($cantidadVentas > $topXVentas){
                                                $topXVentas = $cantidadVentas;
                                            }
                                            $actualArray[] = $cantidadVentas;
                                            $diaIdx = $row['dia'] + 1;
                                        }
                                    }

                                    $idealUnidades = $puntoEquilibrio / $dias;
                                    if ($idealUnidades <= 0) {
                                        $idealUnidades = 1;
                                    }
                                    $topXVentas = $puntoEquilibrio;
                                    $idealValueArray = range($idealUnidades, $topXVentas, $idealUnidades);
                                    $idealXArray = range(1, $dias,1);

                                ?>  
                                <div class="card-body">
                                    <div id="container-burndown" style="max-width: 800px; height: 600px;"></div>
                                </div>
                            </div>
                            
                        </div>                        
                    
                    </div>

                </main>

                <?php include 'footer.php';?>

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>

        <script type="text/javascript" charset="utf8" src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>

        <script>
            jQuery(document).ready(function() {
            var doc = $(document);
            $('#container-burndown').highcharts({
                title: {
                text: 'Objetivo de Ventas Diarias',
                x: -10 //center
                },
                scrollbar: {
                            barBackgroundColor: 'gray',
                            barBorderRadius: 7,
                            barBorderWidth: 0,
                            buttonBackgroundColor: 'gray',
                            buttonBorderWidth: 0,
                            buttonBorderRadius: 7,
                            trackBackgroundColor: 'none',
                            trackBorderWidth: 1,
                            trackBorderRadius: 8,
                            trackBorderColor: '#CCC'
                        },
                colors: ['blue', 'red'],
                plotOptions: {
                line: {
                    lineWidth: 3
                },
                tooltip: {
                    hideDelay: 200
                }
                },
                subtitle: {
                text: 'Mes Actual',
                x: -10
                },
                xAxis: {
                    categories: <?php echo json_encode($idealXArray);?>,
                    title: {
                        text: 'Dias'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Monto'
                    },
                    type: 'linear',
                    max: <?php echo $topXVentas;?>,
                    min:0,
                    tickInterval :250000
                },
                
                tooltip: {
                valuePrefix: '$ ',
                crosshairs: true,
                shared: true
                },
                legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom',
                borderWidth: 0
                },
                series: [{
                name: 'Ideal',
                color: 'rgba(255,0,0,0.25)',
                lineWidth: 2,
                
                data: <?php echo json_encode($idealValueArray);?>
                }, {
                name: 'Actual',
                color: 'rgba(0,120,200,0.75)',
                marker: {
                    radius: 6
                },
                data: <?php echo json_encode($actualArray);?>
                }]
            });
                });
        </script>

    </body>
</html>
