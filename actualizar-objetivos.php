<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "server.php";

function getNombreGasto($idGasto){

    if ($idGasto == 'F') 
        return "Gastos Fijo";

    if ($idGasto == 'V') 
        return "Costo por Ventas";

    if ($idGasto == 'C') 
        return "Pago Comisiones";

    if ($idGasto == 'D') 
        return "Pago Deudas";

    return "Otros Gastos";
}

?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">
        <?php include 'topBar.php';?>
        
        <div id="layoutSidenav">
            
            <?php include 'sidebar.php';?>
            
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h4 class="mt-4">Objetivos</h4>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Procesos de Objetivos para punto de Quiebre</li>
                        </ol>
                        <div class="row">

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-header">Gastos</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Detalle de Gastos
                                        </p>
                                    </div>
                                    <div class="list-group list-group-flush">

                                        <?php 
                                            
                                            $totalGastosFijos = 0;
                                            // Detalle de gastos
                                            $sql = "select g.tipo,
                                            sum(g.monto) as monto 
                                            from gastos g
                                            where g.tipo != 'D'
                                            group by g.tipo";

                                            if (mysqli_query($link,$sql))
                                            {
                                                echo "";
                                            }
                                            else
                                            {
                                                echo "Error: " . $sql . "<br>" . mysqli_error($link);
                                            }

                                            $rsPagos = mysqli_query($link,$sql);

                                            if (mysqli_num_rows($rsPagos) > 0) {

                                                while($rowPagos = mysqli_fetch_assoc($rsPagos)){

                                                    $amountPago = $rowPagos['monto'];

                                                    $tipoGasto =  getNombreGasto($rowPagos['tipo']);

                                                    $textoPago = $tipoGasto . ": $ " . number_format($amountPago, 2, ".", "");

                                                    $totalGastosFijos = $totalGastosFijos + $amountPago;
                                            
                                        ?>
                                            <a href="#" class="list-group-item list-group-item-action"><?php echo $textoPago; ?></a>

                                        <?php
                                                }
                                            }

                                            $textoPago = "Total : $ " . number_format($totalGastosFijos, 2, ".", "");
                                        ?>

                                    </div>
                                    <div class="card-footer">
                                        <a href="#" class="list-group-item list-group-item-action"><?php echo $textoPago; ?></a>
                                    </div>    
                                </div>
                            </div>


                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-header">Ventas</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Datos de Referencia de ventas
                                        </p>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <?php 
                                            $precioVentaReferencia = 0; 
                                            // Detalle de ingresos
                                            $sql = "SELECT  
                                            precio,
                                            COUNT(*) 
                                            FROM productos
                                            GROUP BY precio
                                            order by 2 desc
                                            LIMIT 1;";

                                            $rsRepartos = mysqli_query($link,$sql);
                                            if (mysqli_num_rows($rsRepartos) > 0) {

                                                while($rowRepartos = mysqli_fetch_assoc($rsRepartos)){

                                                    $precioVentaReferencia = $rowRepartos['precio'];
                                                    $textoRepartos = "Precio Venta: $ " . number_format($precioVentaReferencia, 2, ".", "");

                                                    $costoVentaReferencia = $precioVentaReferencia * 0.7 ; 
                                                    $textoCosto = "Costo Venta: $ " . number_format($costoVentaReferencia, 2, ".", "") . " (30%)";

                                            
                                        ?>
                                            <a href="#" class="list-group-item list-group-item-action"><?php echo $textoRepartos; ?></a>
                                            <a href="#" class="list-group-item list-group-item-action"><?php echo $textoCosto; ?></a>

                                        <?php
                                                }
                                            }
                                        ?>

                                    </div>
                                </div>
                            </div>


                            <?php
                                // calculos
                                //$totalUnidadesPC = $totalGastosFijos / ($precioVentaReferencia - $costoVentaReferencia);
                                $totalUnidadesPC = 10 * 300; // 10 meses

                                // unidades vendidas.
                                $totalUnidadesVendidas = 0;
                                $sql = "select count(unidades) unidades from ventas";
                                $rs = mysqli_query($link,$sql);

                                if (mysqli_num_rows($rs) > 0) {

                                    while($row = mysqli_fetch_assoc($rs)){

                                        $totalUnidadesVendidas = $row['unidades'];
                                    }
                                }   


                                // actualzo anual
                                $logrado = number_format(($totalUnidadesVendidas), 0, ".", "");
                                $objetivo = number_format(($totalUnidadesPC), 0, ".", "");

                                if ($totalUnidadesVendidas > $totalUnidadesPC) {
                                    $logrado = $objetivo;    
                                } 
                                $sql = "update objetivos set logrado = " . $logrado . ", objetivo = ".$objetivo." where prioridad = 5"; // anual
                                if($stmt = mysqli_prepare($link, $sql)){
                                    // Attempt to execute the prepared statement
                                    if(mysqli_stmt_execute($stmt)){
                                    } 
                                }   
                                
                                // semestral
                                $logrado = number_format(($logrado / 2), 0, ".", "");
                                $objetivo = number_format(($objetivo / 2), 0, ".", "");
                                $sql = "update objetivos set logrado = " . $logrado . ", objetivo = ".$objetivo." where prioridad = 4"; // semestral
                                if($stmt = mysqli_prepare($link, $sql)){
                                    // Attempt to execute the prepared statement
                                    if(mysqli_stmt_execute($stmt)){
                                    } 
                                }   

                                // mensual
                                $logrado = number_format(($logrado / 6), 0, ".", "");
                                $objetivo = number_format(($objetivo / 6), 0, ".", "");
                                $sql = "update objetivos set logrado = " . $logrado . ", objetivo = ".$objetivo." where prioridad = 3"; // mensual
                                if($stmt = mysqli_prepare($link, $sql)){
                                    // Attempt to execute the prepared statement
                                    if(mysqli_stmt_execute($stmt)){
                                    } 
                                }   

                                // Semanal
                                $logrado = number_format(($logrado / 4), 0, ".", "");
                                $objetivo = number_format(($objetivo / 4), 0, ".", "");
                                $sql = "update objetivos set logrado = " . $logrado . ", objetivo = ".$objetivo." where prioridad = 2"; // semanal
                                if($stmt = mysqli_prepare($link, $sql)){
                                    // Attempt to execute the prepared statement
                                    if(mysqli_stmt_execute($stmt)){
                                    } 
                                }   

                                // Diario
                                $logrado = number_format(($logrado / 7), 0, ".", "");
                                $objetivo = number_format(($objetivo / 7), 0, ".", "");
                                $sql = "update objetivos set logrado = " . $logrado . ", objetivo = ".$objetivo." where prioridad = 1"; // Diario
                                if($stmt = mysqli_prepare($link, $sql)){
                                    // Attempt to execute the prepared statement
                                    if(mysqli_stmt_execute($stmt)){
                                    } 
                                }   

                            ?>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-header">Punto de Quiebre</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Calculo Actual
                                        </p>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action">Unidades : <?php echo number_format(($totalUnidadesPC), 0, ".", "");?></a>
                                    </div>
                                </div>
                            </div>


                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-header">Objetivos</div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Detalle de objetivos
                                        </p>
                                    </div>
                                    <div class="list-group list-group-flush">

                                        <?php 
                                            $sql = "SELECT * FROM objetivos order by prioridad;";

                                            if (mysqli_query($link,$sql))
                                            {
                                                echo "";
                                            }
                                            else
                                            {
                                                echo "Error: " . $sql . "<br>" . mysqli_error($link);
                                            }

                                            $rsObjetivos = mysqli_query($link,$sql);

                                            if (mysqli_num_rows($rsObjetivos) > 0) {

                                                while($row = mysqli_fetch_assoc($rsObjetivos)){

                                                    $objetivo = $row['objetivo'];
                                                    $logrado = $row['logrado'];
                                                    if ($logrado < $objetivo){
                                                        $porcentaje = ($logrado * 100) / $objetivo;
                                                    } else {
                                                        $porcentaje = 100;
                                                    }
                                                    $texto = $row['nombre'] . " : " . $objetivo . " / " . $logrado. " : " . $porcentaje . " %";
                                                    // $textoIngresos = $rowIngresos['canal'] . ": $ " . number_format($amountIngresos, 2, ".", "") . " (".$rowIngresos['equipo'].")";

                                            
                                        ?>
                                            <a href="#" class="list-group-item list-group-item-action"><?php echo $texto; ?></a>

                                        <?php
                                                }
                                            }
                                        ?>

                                    </div>
                                </div>
                            </div>


                        </div>

                    </div>
                </main>
                
                <?php include 'footer.php';?>

            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
