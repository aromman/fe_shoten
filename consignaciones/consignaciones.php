<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/medioPago.php";
require_once "../app/models/compras.php";
require_once "../app/models/cuentasMovimientos.php";
require_once "../app/models/consignaciones.php";

if(
    (isset($_REQUEST['id']) and $_REQUEST['id']!="" ) and 
    (isset($_REQUEST['accionPagar']) and $_REQUEST['accionPagar']=="pagar" )
){
    // recupero la venta
    
    $id_consignatario = $_REQUEST['id'];
    $id_medioPago = $_REQUEST['medioPago'];
    $totalCompra = $_REQUEST['monto'];
    $nombre = $_REQUEST['nombre'];

    $comprasPDO = new Compra();
    $comprasPDO->updateEstadoByConsignacion($id_consignatario, 6); // 6 = PAGADO

    $fecha = date("Y-m-d"); 

    // Incerto movimiento en cuenta
    $medioPagoPDO = new MedioPago();
    $mediosPago = $medioPagoPDO->getById($id_medioPago);
    $idCuenta = $mediosPago['id_cuenta'];
    // inserto movimiento
    $cuentasMovimientosPDO = new CuentasMovimientos();
    $idCausal = 2; // COMPRAS
    $cuentasMovimientosPDO->idCuenta = $idCuenta;
    $cuentasMovimientosPDO->fecha = $fecha;
    $cuentasMovimientosPDO->tipoMovimiento = "D";
    $cuentasMovimientosPDO->descripcion = "Compra a ".$nombre;
    $cuentasMovimientosPDO->monto = $totalCompra;
    $cuentasMovimientosPDO->consolidado = "FALSE";
    $cuentasMovimientosPDO->idCausal = $idCausal; // COMPRAS
    $cuentasMovimientosPDO->create();

    header('location: consignaciones.php');
    exit;
}

$consignacionesPDO = new Consignaciones();
$rsventas = $consignacionesPDO->getAllALiquidar();
$rsCompras = $consignacionesPDO->getAllAPagar();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Consignaciones</title>
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
                        <h1 class="mt-4">Consignaciones</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Ventas a Liquidar
                            </div>
                            <div class="card-body">
                                <form id="formLiquidaciones" method="post" onSubmit="return false;">
                                    <table class="table table-striped table-bordered" id="tbConsignaciones">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Nombre</th>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Monto</th>
                                                <th class="text-center">Comision</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Liquidar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                if (!is_null($rsventas) && count($rsventas) > 0) {
                                                    foreach($rsventas as $row){
                                                        $colId = date('Ymd', strtotime($row['fecha']));
                                                        $colNombre = $row['nombre'];
                                                        $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                        $colMonto = number_format($row['monto'], 2, ".", ""); 
                                                        $colComision = number_format($row['comision'], 2, ".", ""); 
                                                        $colCosto = number_format($colMonto - $colComision, 2, ".", ""); 
                                                
                                            ?>

                                                    <tr>
                                                        <td><?php echo $colNombre; ?></td>
                                                        <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo $colFecha; ?></td>
                                                        <td align="right"><?php echo $colMonto; ?></td>
                                                        <td align="right"><?php echo $colComision; ?></td>
                                                        <td align="right"><?php echo $colCosto; ?></td>

                                                        <td align="center">
                                                            <a href="liquidar.php?id=<?php echo $row['id'];?>&fecha=<?php echo $row['fecha'];?>&forwardOk=consignaciones.php" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
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

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Pagos Pendientes
                            </div>
                            <div class="card-body">
                                <form id="formPagos" method="post" onSubmit="return false;">
                                    <table class="table table-striped table-bordered" id="tbConsignaciones">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Nombre</th>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Monto</th>
                                                <th class="text-center">Comision</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Pagar</th>
                                                <th class="text-center">Reversar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                if (!is_null($rsCompras) && count($rsCompras) > 0) {
                                                    $nombreAnt = "";
                                                    $totalNombre = 0;
                                                    $editorialAnt = 0;
                                                    
                                                    foreach($rsCompras as $row){
                                                        $colId = date('Ymd', strtotime($row['fecha']));
                                                        $colNombre = $row['nombre'];
                                                        $colEditorial = $row['editorial'];
                                                        $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                        $colMonto = number_format($row['monto'], 2, ".", ""); 
                                                        $colCosto = number_format($row['costo'], 2, ".", ""); 
                                                        $colComision = number_format($colMonto - $colCosto, 2, ".", ""); 
                                                        
                                                        if (!empty($nombreAnt) and $nombreAnt!=$colNombre){
                                                            // corte de control

                                                        ?>
                                                        <tr class="bg-info">
                                                            <td colspan="4" align="right"><b>Total a Pagar a <?php echo $nombreAnt; ?></b></td>
                                                            <td align="right"><b><?php echo number_format($totalNombre, 2, ".", ""); ?></b></td>
                                                            <td align="center">
                                                                <a href="ver.php?id=<?php echo $editorialAnt;?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                                <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#pagarModal" data-id="<?php echo $editorialAnt;?>" data-nombre="<?php echo $nombreAnt;?>" data-monto="<?php echo $totalNombre;?>" ><i class="fa fa-fw fa-credit-card"></i></a>
                                                            </td>
                                                            <td></td>
                                                        </tr>

                                                        <?php     
                                                            $totalNombre = 0;
                                                        } 
                                                        // totalizo
                                                        $nombreAnt = $colNombre;
                                                        $totalNombre += $colCosto;
                                                        $editorialAnt = $colEditorial;
                                                
                                            ?>

                                                    <tr>
                                                        <td><?php echo $colNombre; ?></td>
                                                        <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo $colFecha; ?></td>
                                                        <td align="right"><?php echo $colMonto; ?></td>
                                                        <td align="right"><?php echo $colComision; ?></td>
                                                        <td align="right"><?php echo $colCosto; ?></td>
                                                        <td></td>
                                                        <td align="center">    
                                                            <a href="reversar.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-rotate-left"></i></a>
                                                        </td>                            

                                                    </tr>

                                            <?php
                                                    }
                                                    // final
                                                    ?>
                                                        <tr class="bg-info">
                                                            <td colspan="4" align="right"><b>Total a Pagar a <?php echo $nombreAnt; ?></b></td>
                                                            <td align="right"><b><?php echo number_format($totalNombre, 2, ".", ""); ?></b></td>
                                                            <td align="center">
                                                                <a href="ver.php?id=<?php echo $editorialAnt;?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                                <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#pagarModal" data-id="<?php echo $editorialAnt;?>" data-nombre="<?php echo $nombreAnt;?>" data-monto="<?php echo $totalNombre;?>" ><i class="fa fa-fw fa-credit-card"></i></a>
                                                            </td>
                                                            <td></td>    
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        <?php include 'pagarModal.php'; ?>
        
        <script>
        $('#pagarModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var nombre = applicant.data('nombre');
            var monto = applicant.data('monto');
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="nombre"]').val(nombre);
            modal.find('input[name="monto"]').val(monto);
        });
        </script> 

    </body>
    
</html>
