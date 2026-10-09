<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

include 'topBar.php'; 

// Include config file
require_once "server.php";

$sql = "select 
oc.id,
oc.fecha,
pv.nombre proveedor,
oc.cantidad,
oc.monto,
oc.fecha_entrega,
ep.estado
from orden_compra oc, proveedores pv, estado_pedido ep
where oc.id_proveedor = pv.id
and oc.id_estado_pedido = ep.id
order by oc.fecha"; 

//error_log(PHP_EOL.$sql, 3, "my-errors.log");

if (mysqli_query($link,$sql)){
    //error_log(PHP_EOL.'Consulta OK', 3, "my-errors.log");
} else {
    //error_log(PHP_EOL.mysqli_error($link), 3, "my-errors.log");
}
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
        <title>ordenes</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();
                
                $('body').on('mousemove',function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });
                
                $("#addMoreOrdenes").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-ordenes.ajax.php',
                        data:{'action':'addDataRowOrdenes'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveOrdenes').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formOrdenes").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-ordenes.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveOrdenes').addClass("disabled");
                        }
                    });
                });
                
            });
        </script>

    </head>
    <body class="sb-nav-fixed">

        <?php include 'topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include 'sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Ordenes</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Ordenes
                            </div>
                            <div class="card-body">
                                <form id="formOrdenes" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveOrdenesAddMore">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Proveedor</th>
                                            <th class="text-center">Unidades</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Fecha Entrega</th>
                                            <th class="text-center">Estado</th>                                            
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colProveedor = $row['proveedor'];
                                                    $colCantidad = number_format($row['cantidad'], 0, ".", ""); 
                                                    $colMonto = number_format($row['monto'], 2, ".", ""); 
                                                    $colFechaEntrega = date('d/m/Y', strtotime($row['fecha_entrega']));
                                                    $colEstado = $row['estado'];
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colProveedor;?></td>
                                                    <td align="right"><?php echo $colCantidad; ?></td>
                                                    <td align="right"><?php echo $colMonto; ?></td>
                                                    <td align="center"><?php echo $colFechaEntrega; ?></td>
                                                    <td><?php echo $colEstado;?></td>

                                                    <td align="center">
                                                        
                                                        <a href="update-ordenes.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="delete-entity.php?entityName=orden_compra&forwardOk=orden-compra.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta Orden?');"><i class="fa fa-fw fa-trash"></i></a>

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
                                    <tfoot>
                                        <tr>
                                            <td colspan="7">
                                                <a href="javascript:;" class="btn btn-danger" id="addMoreOrdenes"><i class="fa fa-fw fa-plus-circle"></i> Nueva Orden</a>
                                                <button type="submit" name="saveOrdenes" id="saveOrdenes" value="saveOrdenes" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </form>
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