<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../server.php";

$total_clientes = $total_pozo = $total_1er_premio = $total_2do_premio = $total_3er_premio = 0;

$sql = "select 
count(distinct(v.id)) total_clientes,
sum(v.total) * 5 / 100  total_pozo
from 
clientes c, ventas v, productos p, editoriales e, ventas_header vh
where 
v.id_ventas_header = vh.id 
and v.id_cliente = c.id
and v.id_producto = p.id
and p.id_editorial = e.id
and e.consignacion = false
and p.nuevo = true
and v.fecha > '2023-11-26'
and vh.sorteo = true
;";
$rs = mysqli_query($link,$sql);
if (mysqli_num_rows($rs) > 0) {
    $row = mysqli_fetch_assoc($rs);

    $total_clientes = $row['total_clientes']; 
    $total_pozo = $row['total_pozo']; 
}

// 15 % para el 3er puesto
$total_3er_premio = $total_pozo * 15 / 100;
// 28 % para el 2do puesto
$total_2do_premio = $total_pozo * 28 / 100;
// 57 % para el 1er puesto
$total_1er_premio = $total_pozo - $total_2do_premio - $total_3er_premio;

?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Sorte Fin de Año</title>
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
                        <h4 class="mt-4">Sorteo Fin de Año</h4>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Clientes</li>
                        </ol>
                        <div class="row">

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Clientes Participantes</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_clientes ?> clientes</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                        <div class="row">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">1er Premio</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Orden Compra x $ <?php echo number_format($total_1er_premio, 2, ".", ""); ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                        <div class="row">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">2do Premio</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Orden Compra x $ <?php echo number_format($total_2do_premio, 2, ".", ""); ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                        <div class="row">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">3er Premio</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Orden Compra x $ <?php echo number_format($total_3er_premio, 2, ".", ""); ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                </main>
                
                <?php include '../footer.php';?>

            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    </body>
</html>
