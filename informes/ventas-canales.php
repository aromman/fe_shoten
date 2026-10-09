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
v.fecha,
c.nombre,
c.id id_canal,
sum(v.total) monto,
sum(v.unidades * v.costo) costo
from ventas v, productos p, editoriales e, canal c
where v.id_producto = p.id
and p.id_editorial = e.id
and v.id_canal = c.id
and YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
group by v.fecha, c.nombre, c.id
order by 1 desc,2;"; 

$result = mysqli_query($link,$sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Ventas Canales</title>
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
                        <h1 class="mt-4">Ventas Canales</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Ventas Canales
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tbConsignaciones">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Ventas</th>
                                            <th class="text-center">Ganancias</th>
                                            <th class="text-center">Costo</th>
                                            <th class="text-center">Ver Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colId = date('Ymd', strtotime($row['fecha']));
                                                    $colNombre = $row['nombre'];
                                                    $colMonto = number_format($row['monto'], 2, ".", ""); 
                                                    $colCosto = number_format($row['costo'], 2, ".", ""); 
                                                    $colGanancia = number_format($colMonto - $colCosto, 2, ".", ""); 
                                                    $colIdCanal = $row['id_canal'];
                                            
                                        ?>

                                                <tr>
                                                    <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colNombre; ?></td>
                                                    <td align="right"><?php echo $colMonto; ?></td>
                                                    <td align="right"><?php echo $colGanancia; ?></td>
                                                    <td align="right"><?php echo $colCosto; ?></td>
                                                    <td align="center">
                                                        <a href="view-ventas.php?forwardOk=ventas-canales.php&fecha=<?php echo $row['fecha'];?>&canal=<?php echo $colIdCanal;?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                    </td>
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
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>

    </body>
</html>
