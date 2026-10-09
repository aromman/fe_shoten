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
require_once "../app/models/ventas.php";
require_once "../app/models/producto.php";
require_once "../app/models/presupuesto.php";
require_once "../app/models/canal.php";
require_once "../app/models/productoFormato.php";

$presupuestoPDO = new Presupuesto();
$ventasPDO = new Ventas();
$canalPDO = new Canal();
$productoPDO = new Producto();

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Home</title>
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
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active"><? echo date('F Y'); ?></li>
                        </ol>
                        
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-5">
                        
                        <?php 
                                $canales = $canalPDO->getAllActive("nombre ASC");
                                foreach ($canales as $val) {
                                    $canal = $val['id'];
                                    $canalNombre = $val['nombre'];
                                    $totalCostosFijos = $presupuestoPDO->getTotalByCanal($canal);
                                    $totalVentas = $cantidadVentas = $totalUnidadesVendidas = $promedioVenta = $costoPromedioVentas = 0;
                                    $ventaMes = $ventasPDO->getTotalMonthByCanal($canal);
                                    if (!is_null($ventaMes)){
                                        $totalVentas = $ventaMes['total_ventas'];
                                        $cantidadVentas = $ventaMes['cantidad_ventas'];
                                        $totalUnidadesVendidas = $ventaMes['total_unidades'];
                                        $promedioVenta = $ventaMes['promedio'];
                                        $costoPromedioVentas = $ventaMes['costo_promedio'];

                                        $puntoEquilibrioUnidad = $totalCostosFijos / ($promedioVenta - $costoPromedioVentas);
                                        $puntoEquilibrioMonto = $puntoEquilibrioUnidad * $promedioVenta;

                                        $porcentajePEU = ($puntoEquilibrioUnidad > $totalUnidadesVendidas ) ? (($totalUnidadesVendidas * 100) / $puntoEquilibrioUnidad) : 100;
                                        $porcentajePEM = ($puntoEquilibrioMonto > $totalVentas ) ? (($totalVentas * 100) / $puntoEquilibrioMonto) : 100;

                                    }
                        ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <p class="card-text">Total Ventas <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">$ <?php echo number_format($totalVentas, 2, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                <div class="card-header bg-warning text-white">
                                        <p class="card-text">Cantidad Ventas <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo number_format($cantidadVentas, 0, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                        <p class="card-text">Unidades Vendidas <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo number_format($totalUnidadesVendidas, 0, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                <div class="card-header bg-danger text-white">
                                        <p class="card-text">Venta Promedio <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">$ <?php echo number_format($promedioVenta, 2, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- PUNTO EQUILIBRIO -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                <div class="card-header bg-danger text-white">
                                        <p class="card-text">Costos Fijos <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">$ <?php echo number_format($totalCostosFijos, 2, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                <div class="card-header bg-danger text-white">
                                        <p class="card-text">Costos Variables <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">$ <?php echo number_format($costoPromedioVentas, 2, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-danger text-white">
                                        <p class="card-text">P.Equilibrio x Unidad <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo number_format($puntoEquilibrioUnidad, 0, ".", ""); ?> - <?php echo number_format($porcentajePEU, 0, ".", ""); ?>%
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?=$porcentajePEU;?>%" aria-valuenow="<?=$porcentajePEU;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                <div class="card-header bg-danger text-white">
                                        <p class="card-text">P.Equilibrio por Monto <?php echo $canalNombre;?></p>
                                    </div>
                                    <div class="card-body">
                                        <p class ="card-text">$ <?php echo number_format($puntoEquilibrioMonto, 2, ".", ""); ?> - <?php echo number_format($porcentajePEM, 0, ".", ""); ?>%
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?=$porcentajePEM;?>%" aria-valuenow="<?=$porcentajePEM;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php 
                                }
                            ?>

                            <?php 
                                $stockDisponible = $stockValorizado = $objetivoVentas = 0;
                                
                                $formato = "";

                                $productoFormatoPDO = new ProductoFormato();
                                $formatos = $productoFormatoPDO->getAll("nombre");
                                foreach ($formatos as $formato) {
                                
                                    $stock = $productoPDO->getStockAvailableByFormato($formato['id']);

                                    $objetivoVentas = $formato['monto_objetivo'];
                                    $stockFormato = $formato['nombre'];
                                    
                                    $stockDisponible = $stock['stock'];
                                    $stockValorizado = $stock['valorizado'];

                                    $montoVentas = 0;
                                    $ventaMes = $ventasPDO->getTotalMonthByFormato($formato['id']);
                                    if (!is_null($ventaMes)){
                                        $montoVentas = $ventaMes['total_costo'];
                                    }
                                    
                                    if ($objetivoVentas > $montoVentas) {
                                        $porcentajeObjetivoVentas = ($montoVentas * 100) / $objetivoVentas;
                                    } else {
                                        $porcentajeObjetivoVentas = 100;
                                    }

                            ?>    
                            <div class="col-xl-3 col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info">
                                        <p class="card-text"><b><?php echo $stockFormato; ?></b></p>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Stock : <?php echo number_format($stockDisponible, 0, ".", ""); ?> unidades - $ <?php echo number_format($stockValorizado, 2, ".", ""); ?>
                                        <p>    
                                        <p class="card-text">
                                            Ventas : <?php echo number_format($montoVentas, 2, ".", ""); ?> - Objetivo $ <?php echo number_format($objetivoVentas, 2, ".", ""); ?>
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?=$porcentajeObjetivoVentas;?>%" aria-valuenow="<?=$porcentajeObjetivoVentas;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                }
                            ?>        

                        </div>                        

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Area Chart -->
                        <div class="col-lg-6 mb-4">

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Evolucion de Ganancia</h6>
                                    <h7 class="m-0 font-weight-bold text-primary">Gastos vs Ganancia</h7>
                                </div>
                                <?php

                                    $date = new DateTime();
                                    $diasMes = $date->format('t');
                                    $precioReferencia = 6900;
                                    /*
                                    $sql = "select round( sum(precio) / sum(stock) , 0) precio_promedio from productos where stock > 0;";
                                    $rs = mysqli_query($link,$sql);
                                    if (mysqli_num_rows($rs) > 0) {
                                        $row = mysqli_fetch_assoc($rs);
                                        $precioReferencia = $row['precio_promedio'];
                                    }
                                    */
                                    $totalGastos = 0;
                                    $sql = "select ROUND(((sum(g.monto) / ".$precioReferencia.")/".$diasMes."), 0) total_gastos
                                    from gastos g, medio_pago mp, cuentas c
                                    where g.tipo = 'F'
                                    and YEAR(g.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(g.fecha) = MONTH(CURRENT_DATE())
                                    and mp.id = g.id_medio_pago
                                    and mp.id_cuenta = c.id
                                    and c.tipo = 'A'";
                                    error_log(PHP_EOL.$sql, 3, "my-errors.log");                                                
                                    $rs = mysqli_query($link,$sql);
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            $total = $row['total_gastos'];
                                            if (!is_null($total)){
                                                if ($total > $totalGastos){
                                                    $totalGastos = $total;
                                                }
                                            }                                                
                                        }
                                    }
                                    mysqli_free_result($rs);

/*
                                    $totalCompras = 0;
                                    $sql = "select ROUND(((sum(cantidad * precio_costo) / ".$precioReferencia.")/".$diasMes."), 0) total_compras
                                    from compras
                                    where id_estado != 1
                                    and YEAR(fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(fecha) = MONTH(CURRENT_DATE());";

                                    $rs = mysqli_query($link,$sql);
                                    if (mysqli_num_rows($rs) > 0) {
                                        $row = mysqli_fetch_assoc($rs);
                                        $totalCompras = $row['total_compras'];
                                    }
                                    mysqli_free_result($rs);
                                    
                                    $idealUnidades = $totalGastos + $totalCompras;
*/                                  $idealUnidades = $totalGastos;  
                                    if ($idealUnidades <= 0) {
                                        $idealUnidades = 1;
                                    }

                                    $topXVentas = $diasMes * $idealUnidades;
                                    $idealValueArray = range($idealUnidades, $topXVentas, $idealUnidades);
                                    $idealArray = range(1, $diasMes, 1);
                                    $idealXArray = array();
                                    foreach ($idealArray as $value){
                                        $value = trim($value);
                                        $idealXArray[] = $value;
                                    }
                                    $actualArray = array();

                                    $sql = "Select 
                                    day(v.fecha) dia, 
                                    ROUND(sum((total - (costo * unidades)) / ".$precioReferencia."), 0) unidades
                                    from ventas v
                                    where 
                                    YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                    group by day(v.fecha);";
                                    $rs = mysqli_query($link,$sql);
                                    $cantidadVentas = 0;
                                    $diaIdx = 1;
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            while($diaIdx < $row['dia']){
                                                // avanzo dias hasta
                                                $actualArray[] = $cantidadVentas;        
                                                $diaIdx += 1;
                                            }
                                            // cuento las ventas a hoy
                                            $cantidadVentas = $cantidadVentas + $row['unidades'];
                                            if ($cantidadVentas > $topXVentas){
                                                $topXVentas = $cantidadVentas;
                                            }
                                            $actualArray[] = $cantidadVentas;
                                            $diaIdx = $row['dia'] + 1;
                                        }
                                    }
                                    mysqli_free_result($rs);
                                ?>  
                                <div class="card-body">
                                    <div id="container-burndown" style="max-width: 800px; height: 600px;"></div>
                                </div>
                            </div>
                        </div>                        

                        <div class="col-lg-6 mb-4">

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Ingresos Vs Egresos</h6>
                                </div>
                                <?php 

                                    // $diasMes = 31;
                                    $precioReferencia = 6900;
                                    $arrayEgresos = array_fill(1, $diasMes, 0);

                                    $sql = "select 
                                    day(g.fecha) dia, 
                                    ROUND((sum(g.monto) / ".$precioReferencia."), 0) unidades
                                    from gastos g, medio_pago mp, cuentas c
                                    where g.tipo = 'F'
                                    and YEAR(g.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(g.fecha) = MONTH(CURRENT_DATE())
                                    and mp.id = g.id_medio_pago
                                    and mp.id_cuenta = c.id
                                    and c.tipo = 'A'
                                    group by day(g.fecha);";


                                    $rs = mysqli_query($link,$sql);
                                    $dataEgresos = array();
                                    $cantidadGastos = 0;
                                    $diaIdx = 1;
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            $diaIdx = $row['dia'];
                                            $cantidadGastos = $row['unidades'];
                                            $arrayEgresos[$diaIdx] = $cantidadGastos;
                                        }
                                    }
                                    mysqli_free_result($rs);
                                    // compras
                                    /*
                                    $sql = "select 
                                    day(fecha) dia, 
                                    ROUND((sum(cantidad * precio_costo) / ".$precioReferencia."), 0) unidades
                                    from compras
                                    where id_estado != 1
                                    and YEAR(fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(fecha) = MONTH(CURRENT_DATE())
                                    group by day(fecha);";
                                    $rs = mysqli_query($link,$sql);
                                    $cantidadCompras = 0;
                                    $diaIdx = 1;
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            $diaIdx = $row['dia'];
                                            $cantidadCompras = $row['unidades'];
                                            $arrayEgresos[$diaIdx] = $arrayEgresos[$diaIdx] + $cantidadCompras;
                                        }
                                    }
                                    mysqli_free_result($rs);
                                    */
                                    // creo bidimensional
                                    // $dataEgresos[] = array('x' => $diaIdx, 'y' => $cantidadCompras);
                                    $cantidadEgresos = 0;
                                    
                                    for ($i = 1; $i <= $diasMes; ++$i){
                                        $cantidadEgresos = $cantidadEgresos + $arrayEgresos[$i];
                                        $dataEgresos[] = array('x' => $i, 'y' => $cantidadEgresos);
                                    }

                                    $sql = "Select 
                                    day(v.fecha) dia, 
                                    ROUND(sum((total - (costo * unidades)) / ".$precioReferencia."), 0) unidades
                                    from ventas v
                                    where 
                                    YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                    group by day(v.fecha);";
                                    $rs = mysqli_query($link,$sql);
                                    $dataIngresos = array();
                                    $cantidadVentas = 0;
                                    $diaIdx = 1;
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            while($diaIdx < $row['dia']){
                                                // avanzo dias hasta
                                                $dataIngresos[] = array('x' => $diaIdx, 'y' => $cantidadVentas);
                                                $diaIdx += 1;
                                            }
                                            // cuento las ventas a hoy
                                            $cantidadVentas = $cantidadVentas + $row['unidades'];
                                            $dataIngresos[] = array('x' => $diaIdx, 'y' => $cantidadVentas);
                                            $diaIdx = $row['dia'] + 1;
                                        }
                                        // completo el mes
                                        while($diaIdx <= $diasMes){
                                            // avanzo dias hasta
                                            $dataIngresos[] = array('x' => $diaIdx, 'y' => $cantidadVentas);
                                            $diaIdx += 1;
                                        }
                                    }
                                    mysqli_free_result($rs);

                                ?>
                                <div class="card-body">    
                                    <div id="chartContainer" style="max-width: 800px; height: 600px;"></div>
                                    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
                                </div>
                            </div>
                        </div>                        
                    </div>    
                    
                    <div class="row">
                        
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Ivrea</h6>
                                    <h7 class="m-0 font-weight-bold text-primary">Ventas vs KPI</h7>
                                </div>
                                <?php
                                    $date = new DateTime();
                                    $diasMes = $date->format('t');
                                    $precioReferencia = 6900;

                                    $idealUnidadesIvrea = 120 / $diasMes;

                                    $topXVentasIvrea = $diasMes * $idealUnidadesIvrea;
                                    $idealValueArrayIvrea = range($idealUnidadesIvrea, $topXVentasIvrea, $idealUnidadesIvrea);
                                    $idealArrayIvrea = range(1, $diasMes, 1);
                                    $idealXArrayIvrea = array();
                                    foreach ($idealArray as $value){
                                        $value = trim($value);
                                        $idealXArrayIvrea[] = $value;
                                    }
                                    $actualArrayIvrea = array();

                                    $sql = "Select 
                                    day(v.fecha) dia, 
                                    ROUND(sum(unidades)) unidades
                                    from ventas v, productos p
                                    where 
                                    YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                    and v.id_producto = p.id 
                                    and p.id_editorial = 2
                                    group by day(v.fecha);";
                                    $rs = mysqli_query($link,$sql);
                                    $cantidadVentasIvrea = 0;
                                    $diaIdx = 1;
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            while($diaIdx < $row['dia']){
                                                // avanzo dias hasta
                                                $actualArrayIvrea[] = $cantidadVentasIvrea;        
                                                $diaIdx += 1;
                                            }
                                            // cuento las ventas a hoy
                                            $cantidadVentasIvrea = $cantidadVentasIvrea + $row['unidades'];
                                            if ($cantidadVentasIvrea > $topXVentasIvrea){
                                                $topXVentasIvrea = $cantidadVentasIvrea;
                                            }
                                            $actualArrayIvrea[] = $cantidadVentasIvrea;
                                            $diaIdx = $row['dia'] + 1;
                                        }
                                    }
                                    mysqli_free_result($rs);
                                    ?>                                
                                <div class="card-body">
                                    <div id="container-burndown-ivrea" style="max-width: 800px; height: 600px;"></div>
                                </div>
                            </div>
                        </div>                    

                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Panini</h6>
                                    <h7 class="m-0 font-weight-bold text-primary">Ventas vs KPI</h7>
                                </div>
                                <?php
                                    $date = new DateTime();
                                    $diasMes = $date->format('t');
                                    $precioReferencia = 6900;

                                    $idealUnidadesPanini = 7 / $diasMes;

                                    $topXVentasPanini = $diasMes * $idealUnidadesPanini;
                                    $idealValueArrayPanini = range($idealUnidadesPanini, $topXVentasPanini, $idealUnidadesPanini);
                                    $idealArrayPanini = range(1, $diasMes, 1);
                                    $idealXArrayPanini = array();
                                    foreach ($idealArray as $value){
                                        $value = trim($value);
                                        $idealXArrayPanini[] = $value;
                                    }
                                    $actualArrayPanini = array();

                                    $sql = "Select 
                                    day(v.fecha) dia, 
                                    ROUND(sum(unidades)) unidades
                                    from ventas v, productos p
                                    where 
                                    YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                    and v.id_producto = p.id 
                                    and p.id_editorial = 3
                                    group by day(v.fecha);";
                                    $rs = mysqli_query($link,$sql);
                                    $cantidadVentasPanini = 0;
                                    $diaIdx = 1;
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            while($diaIdx < $row['dia']){
                                                // avanzo dias hasta
                                                $actualArrayPanini[] = $cantidadVentasPanini;        
                                                $diaIdx += 1;
                                            }
                                            // cuento las ventas a hoy
                                            $cantidadVentasPanini = $cantidadVentasPanini + $row['unidades'];
                                            if ($cantidadVentasPanini > $topXVentasPanini){
                                                $topXVentasPanini = $cantidadVentasPanini;
                                            }
                                            $actualArrayPanini[] = $cantidadVentasPanini;
                                            $diaIdx = $row['dia'] + 1;
                                        }
                                    }
                                    mysqli_free_result($rs);
                                ?>                                
                                <div class="card-body">
                                    <div id="container-burndown-panini" style="max-width: 800px; height: 600px;"></div>
                                </div>
                            </div>
                        </div>                    
                        
                    </div>                        


                    <div class="row">                
                        <!-- bar char -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Compras vs Ventas</h6>
                                </div>

                                <?php 

                                    $sql = "select 
                                    e.nombre,
                                    (select ROUND(sum((c.cantidad * c.precio_costo) / ".$precioReferencia."), 0) from compras c, productos p
                                                                        where c.id_producto = p.id
                                                                        and p.id_editorial = e.id
                                                                        and c.id_estado != 1
                                                                        and YEAR(c.fecha) = YEAR(CURRENT_DATE()) 
                                                                        AND MONTH(c.fecha) = MONTH(CURRENT_DATE())
                                    ) as total_compras,
                                    (select ROUND(sum((v.unidades * v.costo) / ".$precioReferencia."), 0) from ventas v, productos p
                                                                        where v.id_producto = p.id
                                                                        and p.id_editorial = e.id
                                                                        and YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                                                        AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                    ) as total_ventas
                                    from editoriales e
                                    where e.consignacion = false
                                    order by nombre;
                                    ";
                                    $rs = mysqli_query($link,$sql);
                                    $arrayDataCompras = array();
                                    $arrayDataVentas = array();
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            
                                            $montoCompras = empty($row['total_compras']) ? 0 : $row['total_compras']; 
                                            $montoVentas = empty($row['total_ventas'] )? 0 : $row['total_ventas'];
                                            
                                            if ($montoCompras > 0 or $montoVentas > 0) {
                                                $label = $row['nombre'];
                                                $arrayDataCompras[] = array('label' => $label, 'y' => $montoCompras);
                                                $arrayDataVentas[] = array('label' => $label, 'y' => $montoVentas);
                                            }
                                        }
                                    }
                                    mysqli_free_result($rs);

                                ?>
                                <div class="card-body">    
                                    <div id="barChartContainer" style="max-width: 800px; height: 600px;"></div>
                                    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
                                </div>
                            </div>
                        </div>        

                        <!-- calendario -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Ventas por Categoria</h6>
                                </div>
                                <?php

                                    $cantidadTotalVentas = 0;
                                
                                    $sql = "select count(v.unidades) as unidades
                                            from ventas v
                                            where 
                                            YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                            AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                            ;
                                    ";
                                    $rs = mysqli_query($link,$sql);
                                    if (mysqli_num_rows($rs) > 0) {
                                        $row = mysqli_fetch_assoc($rs);
                                        $cantidadTotalVentas = empty($row['unidades']) ? 0 : $row['unidades']; 
                                    }
                                    mysqli_free_result($rs);

                                    
                                    
                                    $sql = "Select 
                                    ps.nombre serie,
                                    count(v.unidades) as unidades
                                    from ventas v, productos p, productos_serie ps
                                    where 
                                    YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
                                    AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
                                    and v.id_producto = p.id
                                    and p.id_serie = ps.id
                                    group by ps.nombre
                                    order by 2 desc
                                    ;
                                    ";
                                    $rs = mysqli_query($link,$sql);
                                    
                                    $arrayDataVentasCategoria = array();
                                    if (mysqli_num_rows($rs) > 0) {
                                        while($row = mysqli_fetch_assoc($rs)){
                                            $unidades = empty($row['unidades']) ? 0 : $row['unidades']; 

                                            $porcentaje = ($unidades * 100) / $cantidadTotalVentas;

                                            $label = $row['serie'];
                                            $arrayDataVentasCategoria[] = array('label' => $label, 'y' => $porcentaje);
                                        }
                                    }
                                    mysqli_free_result($rs);

                                ?>
                                <div class="card-body">    
                                    <div id="pieChartContainer" style="height: 370px; width: 100%;"></div>
                                    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>                                
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">

                            <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Cupos por Categoria</h6>
                            </div>
                            <div class="card-body">

                                <?php                                         

                                    $sql = "select ps.nombre, 
                                    (sum(stock) * 100) / ps.cupo_maximo as porcentaje, 
                                    (ps.cupo_minimo * 100) / ps.cupo_maximo as minimo
                                    from productos p, productos_serie ps
                                    where p.stock > 0
                                    and p.id_serie = ps.id
                                    group by ps.nombre
                                    order by ps.nombre
                                    ";

                                    $rsObjetivos = mysqli_query($link,$sql);

                                    if (mysqli_num_rows($rsObjetivos) > 0) {
                                        while($row = mysqli_fetch_assoc($rsObjetivos)){
                                            $porcentaje = $row['porcentaje'] > 0 ? $row['porcentaje'] : 0;
                                            $minimo = $row['minimo'] > 0 ? $row['minimo'] : 30;
                                            if ($porcentaje < $minimo) {
                                                $className = "bg-danger";
                                            } else if ($porcentaje < 100){
                                                $className = "bg-warning";
                                            } else {
                                                $className = "bg-success";
                                            }
                                            $leyenda = ($porcentaje == 100) ? "Logrado !!" : number_format($porcentaje, 2, ".", "") . "%";
                                ?>
                                <h4 class="small font-weight-bold"><?php echo $row['nombre'];?> <span
                                        class="float-right"><?php echo $leyenda;?></span></h4>
                                <div class="progress mb-4">
                                    <div class="progress-bar <?php echo $className;?>" role="progressbar" style="width: <?php echo $porcentaje;?>%"
                                        aria-valuenow="<?php echo $porcentaje;?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <?php
                                        }
                                    }
                                ?>

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
            jQuery(document).ready(function() {
            var doc = $(document);
            $('#container-burndown').highcharts({
                title: {
                text: 'Objetivo de ventas diarias',
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
                        text: 'Cantidades'
                    },
                    type: 'linear',
                    max: <?php echo $topXVentas;?>,
                    min:0,
                    tickInterval : <?php echo $idealUnidades * 5; ?>
                },
                
                tooltip: {
                valueSuffix: ' unidades',
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

        <script>
        window.onload = function () {
        
        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            title:{
                text: "Ingresos vs Egresos",
                fontSize: 18,
                color: 'rgba(0,120,200,0.75)'
            },
            subtitles: [{
                text: "Mes Actual",
                fontSize: 15
            }],
            axisY: {
                title: 'Cantidades',
                interval: 20
            },
            axisX: {
                title: 'Dias',
                interval: 5
            },
            legend:{
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            toolTip: {
                shared: true
            },
            data: [
            {
                type: "area",
                name: "Ingresos",
                showInLegend: "true",
                xValueType: "numeric",
                // xValueFormatString: "MMM YYYY",
                //yValueFormatString: "#,##0.##",
                dataPoints: <?php echo json_encode($dataIngresos); ?>
            },
            {
                type: "area",
                name: "Egresos",
                showInLegend: "true",
                xValueType: "int",
                //xValueFormatString: "MMM YYYY",
                //yValueFormatString: "#,##0.##",
                dataPoints: <?php echo json_encode($dataEgresos); ?>
            }
            ]
        });
        
        chart.render();
        
        function toggleDataSeries(e){
            if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            }
            else{
                e.dataSeries.visible = true;
            }
            chart.render();
        }
        

            // barchar
            var barChart = new CanvasJS.Chart("barChartContainer", {
                animationEnabled: true,
                title:{
                    text: "Compras vs Ventas, <? echo date('F Y'); ?>"
                },	
                axisY: {
                    title: "Compras",
                    titleFontColor: "#4F81BC",
                    lineColor: "#4F81BC",
                    labelFontColor: "#4F81BC",
                    tickColor: "#4F81BC"
                },
                axisY2: {
                    title: "Ventas",
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
                    name: "Ventas",
                    legendText: "Ventas",
                    showInLegend: true,
                    dataPoints: <?php echo json_encode($arrayDataVentas, 32); ?>
                },
                {
                    type: "column",
                    name: "Compras",
                    legendText: "Compras",
                    showInLegend: true, 
                    dataPoints: <?php echo json_encode($arrayDataCompras, 32); ?>
                }]
            });
            barChart.render();

            function toggleBarCharDataSeries(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                }
                else {
                    e.dataSeries.visible = true;
                }
                barChart.render();
            }

            // pie chart
            var pieChart = new CanvasJS.Chart("pieChartContainer", {
                animationEnabled: true,
                title: {
                    text: "Cantidades por Serie - <? echo date('F Y'); ?>"
                },
                data: [{
                    type: "pie",
                    startAngle: 240,
                    yValueFormatString: "##0.00\"%\"",
                    indexLabel: "{label} {y}",
                    dataPoints: <?php echo json_encode($arrayDataVentasCategoria); ?>
                }]
            });
            pieChart.render();
        }
        </script>

        <script>
            jQuery(document).ready(function() {
            var docKpiIvrea = $(document);
            $('#container-burndown-ivrea').highcharts({
                title: {
                text: 'Objetivo Ivrea',
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
                    categories: <?php echo json_encode($idealXArrayIvrea);?>,
                    title: {
                        text: 'Dias'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Cantidades'
                    },
                    type: 'linear',
                    max: <?php echo $topXVentasIvrea;?>,
                    min:0,
                    tickInterval : <?php echo $idealUnidadesIvrea * 5; ?>
                },
                
                tooltip: {
                valueSuffix: ' unidades',
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
                
                data: <?php echo json_encode($idealValueArrayIvrea);?>
                }, {
                name: 'Actual',
                color: 'rgba(0,120,200,0.75)',
                marker: {
                    radius: 6
                },
                data: <?php echo json_encode($actualArrayIvrea);?>
                }]
            });
                });
        </script>

        <script>
            jQuery(document).ready(function() {
            var docKpiPanini = $(document);
            $('#container-burndown-panini').highcharts({
                title: {
                text: 'Objetivo Panini',
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
                    categories: <?php echo json_encode($idealXArrayPanini);?>,
                    title: {
                        text: 'Dias'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Cantidades'
                    },
                    type: 'linear',
                    max: <?php echo $topXVentasPanini;?>,
                    min:0,
                    tickInterval : 1
                },
                
                tooltip: {
                valueSuffix: ' unidades',
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
                
                data: <?php echo json_encode($idealValueArrayPanini);?>
                }, {
                name: 'Actual',
                color: 'rgba(0,120,200,0.75)',
                marker: {
                    radius: 6
                },
                data: <?php echo json_encode($actualArrayPanini);?>
                }]
            });
                });
        </script>


    </body>
</html>
