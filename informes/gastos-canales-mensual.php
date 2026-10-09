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

$sql = "select 
year(g.fecha) ano,
month(g.fecha) mes,
c.nombre canal,
gc.nombre clase,
sum(g.monto) monto
from gastos g, gastos_clase gc, canal c
where g.id_clase = gc.id
and g.id_canal = c.id
group by year(g.fecha), month(g.fecha), gc.nombre
order by 1 desc ,2 desc,3;"; 

$result = mysqli_query($link,$sql);

$mesesN=array(1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Gastos Mensuales</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">

        <script>
            $(document).ready(function(e) {

                $('#tbConsignaciones').dataTable( {
                    "order": [[ 0, 'desc' ]]
                });

            });
        </script>

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Gastos Canales</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Gastos Canales
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">

                                <table class="table table-striped table-bordered" id="tbConsignaciones">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Ano</th>
                                            <th class="text-center">Mes</th>
                                            <th class="text-center">Canal</th>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                $anoAnt = 0;
                                                $mesAnt = '';
                                                $totalMontoMes = 0;

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colAno = $row['ano'];
                                                    $colMes = $mesesN[$row['mes']];
                                                    $colCanal = $row['canal'];
                                                    $colClase = $row['clase'];
                                                    $colMonto = number_format($row['monto'], 2, ".", ""); 

                                                    if (($anoAnt!=0 and $anoAnt!=$colAno) or (!empty($mesAnt) and $mesAnt!=$colMes)){
                                                        // corte de control
                                        ?>
                                                <tr class="bg-info">
                                                    <td colspan="4" align="right"><b>Total <?php echo $mesAnt . ' ' . $anoAnt; ?></b></td>
                                                    <td align="right"><b><?php echo $totalMontoMes; ?></b></td>
                                                </tr>
                                                <?php     
                                                        // inicializo contadores
                                                        $totalMontoMes = 0;

                                                        } 
                                                        // totalizo 
                                                        $anoAnt = $colAno;
                                                        $mesAnt = $colMes;
                                                        $totalMontoMes += $colMonto;

                                                ?>

                                                <tr>
                                                    <td align="center"></span><?php echo $colAno; ?></td>
                                                    <td><?php echo $colMes; ?></td>
                                                    <td><?php echo $colCanal; ?></td>
                                                    <td><?php echo $colClase; ?></td>
                                                    <td align="right"><?php echo $colMonto; ?></td>
                                                </tr>
                                        <?php
                                                // corte final
                                                }
                                        ?>                                                
                                                <tr class="bg-info">
                                                    <td colspan="4" align="right"><b>Total <?php echo $mesAnt . ' ' . $anoAnt; ?></b></td>
                                                    <td align="right"><b><?php echo $totalMontoMes; ?></b></td>
                                                </tr>
                                        <?php
                                            }
                                            else
                                            {
                                            echo '0 results';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
