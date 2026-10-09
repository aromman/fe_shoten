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
year(v.fecha) ano,
month(v.fecha) mes,
c.nombre canal,
v.id_canal,
pf.nombre serie,
sum(v.unidades) unidades,
sum(v.total) monto,
sum(v.total - v.costo) comision
from ventas v, productos p, editoriales e, canal c, productos_formato pf
where v.id_producto = p.id
and p.id_editorial = e.id
and v.id_canal = c.id
and p.id_formato = pf.id
group by year(v.fecha), month(v.fecha), c.nombre, pf.nombre
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
        <title>Ventas Mensuales</title>
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

        <?php include_once '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include_once '../sidebar.php';?>

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

                                <table class="table table-striped table-bordered" id="tbConsignaciones">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Ano</th>
                                            <th class="text-center">Mes</th>
                                            <th class="text-center">Canal</th>
                                            <th class="text-center">Serie</th>
                                            <th class="text-center">Unidades</th>
                                            <th class="text-center">Ventas</th>
                                            <th class="text-center">Ganancias</th>
                                            <th class="text-center">Costo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if (mysqli_num_rows($result) > 0) {

                                                $anoAnt = 0;
                                                $mesAnt = '';
                                                $totalMontoMes = 0;
                                                $totalComisionMes = 0;
                                                $totalGananciaMes = 0;
                                                $totalCantidadMes = 0;

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colAno = $row['ano'];
                                                    $colMes = $mesesN[$row['mes']];
                                                    $colCanal = $row['canal'];
                                                    $colSerie = $row['serie'];
                                                    $colUnidades = number_format($row['unidades'], 0, ".", "");
                                                    $colMonto = number_format($row['monto'], 2, ".", "");
                                                    $colComision = number_format($row['comision'], 2, ".", "");
                                                    $colTotal = number_format($colMonto - $colComision, 2, ".", "");
                                                    $colIdCanal = $row['id_canal'];

                                                    if (($anoAnt!=0 && $anoAnt!=$colAno) || (!empty($mesAnt) && $mesAnt!=$colMes)){
                                                        // corte de control
                                        ?>
                                                <tr class="bg-info">
                                                    <td class="text-center" colspan="4"><b>Total <?php echo $mesAnt . ' ' . $anoAnt; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalCantidadMes; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalMontoMes; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalComisionMes; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalGananciaMes; ?></b></td>
                                                </tr>
                                                <?php
                                                        // inicializo contadores
                                                        $totalMontoMes = 0;
                                                        $totalComisionMes = 0;
                                                        $totalGananciaMes = 0;
                                                        $totalCantidadMes = 0;

                                                        }
                                                        // totalizo
                                                        $anoAnt = $colAno;
                                                        $mesAnt = $colMes;
                                                        $totalMontoMes += $colMonto;
                                                        $totalComisionMes += $colComision;
                                                        $totalGananciaMes += $colTotal;
                                                        $totalCantidadMes += $colUnidades;

                                                ?>

                                                <tr>
                                                    <td class="text-center"></span><?php echo $colAno; ?></td>
                                                    <td class="text-start"><?php echo $colMes; ?></td>
                                                    <td class="text-start"><?php echo $colCanal; ?></td>
                                                    <td class="text-start"><?php echo $colSerie; ?></td>
                                                    <td class="text-end"><?php echo $colUnidades; ?></td>
                                                    <td class="text-end"><?php echo $colMonto; ?></td>
                                                    <td class="text-end"><?php echo $colComision; ?></td>
                                                    <td class="text-end"><?php echo $colTotal; ?></td>
                                                </tr>
                                        <?php
                                                // corte final
                                                }
                                        ?>
                                                <tr class="bg-info">
                                                    <td colspan="4" class="text-center"><b>Total <?php echo $mesAnt . ' ' . $anoAnt; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalCantidadMes; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalMontoMes; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalComisionMes; ?></b></td>
                                                    <td class="text-end"><b><?php echo $totalGananciaMes; ?></b></td>
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

                <?php include_once '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
