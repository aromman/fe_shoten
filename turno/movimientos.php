<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/turnos.php";

$turnosPDO = new Turnos();

function getNombreEstado($idEstado){
    if ($idEstado == "A")
        return "Abierto";

    if ($idEstado == "C")
        return "Cerrado";

    return $idEstado;
}


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Movimientos de Caja</title>
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

                $('#tbStock').dataTable( {
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
                        <h1 class="mt-4">Movimientos de Caja</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Movimientos de Caja
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">

                                <table class="table table-striped table-bordered" id="tbStock">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Canal</th>
                                            <th class="text-center">Apertura</th>
                                            <th class="text-center">Cierre</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Usuario</th>
                                            <th class="text-center">Actualizado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $turnos = $turnosPDO->getAllLastDays(30,'fecha desc'); // canals por defecto
                                            if (!is_null($turnos)){
                                                foreach ($turnos as $row) {
                                                    $colFecha = $row['fecha'];
                                                    $colCanal = $row['canal'];
                                                    $colId = date('Ymd', strtotime($row['fecha']));
                                                    $colApertura = number_format($row['monto_apertura'], 2, ".", ""); 
                                                    $colCierre = number_format($row['monto_cierre'], 2, ".", ""); 
                                                    $colEstado = getNombreEstado($row['estado']);
                                                    $colUsername = $row['username'];
                                                    $colUpdateDate = date('d/m/Y H:i:s', strtotime($row['updateDate']));

                                        ?>
                                                <tr>
                                                    <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo date('d/m/Y', strtotime($colFecha)); ?></td>
                                                    <td><?php echo $colCanal; ?></td>
                                                    <td align="right"><?php echo $colApertura; ?></td>
                                                    <td align="right"><?php echo $colCierre; ?></td>
                                                    <td><?php echo $colEstado; ?></td>
                                                    <td><?php echo $colUsername; ?></td>
                                                    <td><?php echo $colUpdateDate; ?></td>
                                                    <td>
                                                        <a href="action-turno.php?estado=A&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-unlock"></i></a>
                                                        <a href="action-turno.php?estado=C&id=<?php echo $row['id'];?>" class="text-warning"><i class="fa fa-fw fa-lock"></i></a>
                                                        <a href="action-turno.php?estado=D&id=<?php echo $row['id'];?>" class="text-danger"><i class="fa fa-fw fa-trash"></i></a>
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
                                </form>
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

    </body>
</html>
