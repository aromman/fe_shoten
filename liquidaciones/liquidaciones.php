<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "../server.php";

$sql = "select 
id,
fecha,
total_caja,
total_mercado,
total_dni,
total_stock
from liquidacion 
order by fecha desc";
$result = mysqli_query($link,$sql);

?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Liquidaciones</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Liquidaciones</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Historial de liquidaciones</li>
                        </ol>
                        
                        <div class="row">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <a href="borrar-liquidacion.php" 
                                       class="btn btn-primary btn-lg btn-block" 
                                       onClick="return confirm('Esta seguro de querer eliminar la ultima liquidacion?');">Eliminar ultima Liquidacion</a>
                                </div>
                            </form>
                        </div>    
                        
                        <div class="row">
                        <div class="card mb-4">
                            <div class="card-body">
                                <table class="table table-striped table-bordered" id="tb-cobros">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Caja</th>
                                            <th class="text-center">Mercado</th>
                                            <th class="text-center">Dni</th>
                                            <th class="text-center">Saldo Total</th>
                                            <th class="text-center">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                while($row = mysqli_fetch_assoc($result))
                                                {

                                                    $fecha = date('d/m/Y', strtotime($row['fecha']));

                                                    $totalCaja = $row['total_caja'];
                                                    $totalMercado = $row['total_mercado'];
                                                    $totalDni = $row['total_dni'];
                                                    $totalStock = $row['total_stock'];

                                                    $totalSaldo = $totalCaja + $totalMercado + $totalDni;
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $fecha; ?></td>
                                                    <td align="right"><?php echo "$ " . number_format($totalCaja, 2, ".", ""); ?></td>
                                                    <td align="right"><?php echo "$ " . number_format($totalMercado, 2, ".", ""); ?></td>
                                                    <td align="right"><?php echo "$ " . number_format($totalDni, 2, ".", ""); ?></td>
                                                    <td align="right"><?php echo "$ " . number_format($totalSaldo, 2, ".", ""); ?></td>
                                                    <td align="right"><?php echo number_format($totalStock, 0, ".", ""); ?></td>

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
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
