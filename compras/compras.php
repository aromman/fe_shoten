<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/compras.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Compras</title>
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
                $('#tbCompras').dataTable( {
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
                        <h1 class="mt-4">Compras</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Compras
                            </div>
                            <div class="card-body">
                                <form id="formCompras" method="post" onSubmit="return false;">

                                <div class="row justify-content-end">
                                    <div class="col-auto">
                                        <a href="buy.php" class="btn btn-primary"><i class="fa-solid fa-circle-plus"></i> Nueva Compra</a>
                                    </div>
                                </div>

                                <table class="table table-striped table-bordered" id="tbCompras">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Proveedor</th>
                                            <th class="text-center">Unidades</th>
                                            <th class="text-center">Monto</th>
                                            <th class="text-center">Fecha Entrega</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Crear Venta</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $compraPDO = new Compra();
                                            $compras = $compraPDO->getAllActive();
                                            if  (!is_null($compras)){
                                                foreach ($compras as $row) {

                                                    $colId = date('Ymd', strtotime($row['fecha']));
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colProveedor = $row['proveedor'];
                                                    $colUnidades = number_format($row['cantidad'], 0, ".", ""); 
                                                    $colMonto = number_format($row['monto'], 2, ".", ""); 
                                                    $colFechaEntrega = $row['fecha_entrega'];
                                                    if (!empty($colFechaEntrega)){
                                                        $colFechaEntrega = date('d/m/Y', strtotime($row['fecha_entrega']));
                                                    } elseif (!empty($row['fecha_entrega_calculada'])){
                                                        $colFechaEntrega = date('d/m/Y', strtotime($row['fecha_entrega_calculada']));
                                                    } else {
                                                        $colFechaEntrega = '';
                                                    }
                                                    $colEstado = $row['estado'];
                                                    $colIdEstado = $row['id_estado'];
                                        ?>

                                                <tr>
                                                    <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colProveedor; ?></td>
                                                    <td align="right"><?php echo $colUnidades; ?></td>
                                                    <td align="right"><?php echo $colMonto; ?></td>
                                                    <td align="center"><?php echo $colFechaEntrega; ?></td>
                                                    <td><?php echo $colEstado; ?></td>
                                                    <td align="center">
                                                        <a href="crear-venta.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-copy"></i></a>
                                                    </td>
                                                    <td align="center">
                                                        <?php if ($colIdEstado==1){
                                                        ?>    
                                                            <a href="recibir-compra.php?forwardOk=compras.php&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                        <?php
                                                        } 
                                                        ?>    
                                                        <a href="view-compra.php?forwardOk=compras.php&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>                                                        
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
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
