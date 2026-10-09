<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../server.php";

$totalNominaMesAnterior = $sueldoMaximoMesAnterior = $sueldoMinimoMesAnterior = $sueldoPromedioMesAnterior = 0;
$porcentajeNominaMesAnterior = $porcentajeSueldoMaximoMesAnterior = $porcentajeSueldoMinimoMesAnterior = $porcentajeSueldoPromedioMesAnterior = 0;
$totalNominaMesActual = $sueldoMaximoMesActual = $sueldoMinimoMesActual = $sueldoPromedioMesActual = 0;
$porcentajeNominaMesActual = $porcentajeSueldoMaximoMesActual = $porcentajeSueldoMinimoMesActual = $porcentajeSueldoPromedioMesActual = 0;

$sql = "select 
sum(monto) total_nomina,
max(monto) monto_maximo,
min(monto) monto_minimo,
avg(monto) promedio
 from gastos g
where g.id_clase = 2
and YEAR(g.fecha) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
and MONTH(g.fecha) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";

$rs = mysqli_query($link,$sql);
if (mysqli_num_rows($rs) > 0) {
    $row = mysqli_fetch_assoc($rs);
    $totalNominaMesAnterior = empty($row['total_nomina']) ? 0 : $row['total_nomina']; 
    $sueldoMaximoMesAnterior = empty($row['monto_maximo']) ? 0 : $row['monto_maximo']; 
    $sueldoMinimoMesAnterior = empty($row['monto_minimo']) ? 0 : $row['monto_minimo']; 
    $sueldoPromedioMesAnterior = empty($row['promedio']) ? 0 : $row['promedio']; 

    $porcentajeNominaMesAnterior = 100;
    if ($totalNominaMesAnterior > 0) {
        $porcentajeSueldoMaximoMesAnterior = ($sueldoMaximoMesAnterior * 100) / $totalNominaMesAnterior;
        $porcentajeSueldoMinimoMesAnterior = ($sueldoMinimoMesAnterior * 100) / $totalNominaMesAnterior;
        $porcentajeSueldoPromedioMesAnterior = ($sueldoPromedioMesAnterior * 100) / $totalNominaMesAnterior;
    }

}


$sql = "select 
sum(monto) total_nomina,
max(monto) monto_maximo,
min(monto) monto_minimo,
avg(monto) promedio
 from gastos g
where g.id_clase = 2
    and YEAR(g.fecha) = YEAR(CURRENT_DATE())
    and MONTH(g.fecha) = MONTH(CURRENT_DATE())";

$rs = mysqli_query($link,$sql);
if (mysqli_num_rows($rs) > 0) {
    $row = mysqli_fetch_assoc($rs);
    $totalNominaMesActual = empty($row['total_nomina']) ? 0 : $row['total_nomina']; 
    $sueldoMaximoMesActual = empty($row['monto_maximo']) ? 0 : $row['monto_maximo']; 
    $sueldoMinimoMesActual = empty($row['monto_minimo']) ? 0 : $row['monto_minimo']; 
    $sueldoPromedioMesActual = empty($row['promedio']) ? 0 : $row['promedio']; 

    $porcentajeNominaMesActual = 100;
    if ($totalNominaMesActual > 0) {
        $porcentajeSueldoMaximoMesActual = ($sueldoMaximoMesActual * 100) / $totalNominaMesActual;
        $porcentajeSueldoMinimoMesActual = ($sueldoMinimoMesActual * 100) / $totalNominaMesActual;
        $porcentajeSueldoPromedioMesActual = ($sueldoPromedioMesActual * 100) / $totalNominaMesActual;
    }

}
    
mysqli_free_result($rs);

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>RRHH</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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

        <?php include '../topBar.php';?>
        
        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>

                    <!-- Begin Page Content -->
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Tablero</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Presupuesto</a></li>
                        <li class="breadcrumb-item active"><? echo date('F Y'); ?></li>
                    </ol>
                    

                    <div class="row">

                        <!-- Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Costo Total Nomina Mes Anterior
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$ <?=$totalNominaMesAnterior;?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?=$porcentajeNominaMesAnterior;?>%" aria-valuenow="<?=$porcentajeNominaMesAnterior;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        

                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Sueldo Promedio
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$<?=$sueldoPromedioMesAnterior;?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?=$porcentajeSueldoPromedioMesAnterior;?>%" aria-valuenow="<?=$porcentajeSueldoPromedioMesAnterior;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                        
                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sueldo Maximo
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$<?=$sueldoMaximoMesAnterior;?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?=$porcentajeSueldoMaximoMesAnterior;?>%" aria-valuenow="<?=$porcentajeSueldoMaximoMesAnterior;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        

                        <!-- Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sueldo Minimo
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$<?=$sueldoMinimoMesAnterior;?>%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?=$porcentajeSueldoMinimoMesAnterior;?>%" aria-valuenow="<?=$porcentajeSueldoMinimoMesAnterior;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-thumbs-down fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>    

                    <div class="row">

                        <!-- Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Costo Total Nomina Mes Actual
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$ <?=$totalNominaMesActual;?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?=$porcentajeNominaMesActual;?>%" aria-valuenow="<?=$porcentajeNominaMesActual;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        

                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Sueldo Promedio
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$<?=$sueldoPromedioMesActual;?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?=$porcentajeSueldoPromedioMesActual;?>%" aria-valuenow="<?=$porcentajeSueldoPromedioMesActual;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                        
                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sueldo Maximo
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$<?=$sueldoMaximoMesActual;?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?=$porcentajeSueldoMaximoMesActual;?>%" aria-valuenow="<?=$porcentajeSueldoMaximoMesActual;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        

                        <!-- Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sueldo Minimo
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$<?=$porcentajeSueldoMinimoMesActual;?>%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?=$porcentajeSueldoMinimoMesActual;?>%" aria-valuenow="<?=$porcentajeSueldoMinimoMesActual;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-thumbs-down fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>

                    <div class="row">                
                        
                        <!-- Gastos -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Gastos</h6>
                            </div>

                            <div class="card-body">    
                                <div id="barChartGastosContainer" style="max-width: 800px; height: 600px;"></div>
                                <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
                            </div>
                        </div>
                        
                    </div>
                </main>
                <?php include '../footer.php';?>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script type="text/javascript" charset="utf8" src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>


        <script>
            window.onload = function () {

            // barchar
            var barChartGastos = new CanvasJS.Chart("barChartGastosContainer", {
                animationEnabled: true,
                title:{
                    text: "Gastos, <? echo date('F Y'); ?>"
                },	
                axisY: {
                    title: "Estimado",
                    titleFontColor: "#4F81BC",
                    lineColor: "#4F81BC",
                    labelFontColor: "#4F81BC",
                    tickColor: "#4F81BC"
                },
                axisY2: {
                    title: "Real",
                    titleFontColor: "#C0504E",
                    lineColor: "#C0504E",
                    labelFontColor: "#C0504E",
                    tickColor: "#C0504E"
                },	
                toolTip: {
                    shared: true
                },
                legend: {
                    cursor:"pointer",
                    itemclick: toggleBarCharDataSeries
                },
                data: [{
                    type: "column",	
                    name: "Estimado",
                    legendText: "Estimado",
                    showInLegend: true,
                    dataPoints: <?php echo json_encode($arrayDataGastosEstimados, 32); ?>
                },
                {
                    type: "column",
                    name: "Real",
                    legendText: "Real",
                    showInLegend: true, 
                    dataPoints: <?php echo json_encode($arrayDataGastosReales, 32); ?>
                }]
            });
            barChartGastos.render();

            function toggleBarCharDataSeries(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                }
                else {
                    e.dataSeries.visible = true;
                }
                barChartGastos.render();
            }

            var barChartStock = new CanvasJS.Chart("barChartStockContainer", {
                animationEnabled: true,
                title:{
                    text: "Stock, <? echo date('F Y'); ?>"
                },	
                toolTip: {
                    shared: true
                },
                legend: {
                    cursor:"pointer",
                    itemclick: toggleBarCharStockDataSeries
                },
                data: [{
                    type: "column",	
                    name: "Minimo",
                    legendText: "Minimo",
                    showInLegend: true,
                    dataPoints: <?php echo json_encode($arrayDataStockMinimo, 32); ?>
                },
                {
                    type: "column",
                    name: "Actual",
                    legendText: "Actual",
                    showInLegend: true, 
                    dataPoints: <?php echo json_encode($arrayDataStock, 32); ?>
                },
                {
                    type: "column",
                    name: "Maximo",
                    legendText: "Maximo",
                    showInLegend: true, 
                    dataPoints: <?php echo json_encode($arrayDataStockMaximo, 32); ?>
                }]
            });

            barChartStock.render();

            function toggleBarCharStockDataSeries(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                }
                else {
                    e.dataSeries.visible = true;
                }
                barChartStock.render();
            }

        }
        </script>
    </body>
</html>
