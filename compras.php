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

// detalles
$sql = "select 
c.id,
c.fecha,
p.titulo producto,
c.id_producto,
c.cantidad,
c.precio_lista,
c.precio_costo,
c.id_orden_compra,
ep.estado estado
from compras c, productos p, estado_pedido ep
where c.id_producto = p.id
and c.id_estado = ep.id
and ep.id = 1
order by c.fecha, c.id_orden_compra, p.titulo;"; 

$rsComprasPendientes = mysqli_query($link,$sql);

$sql = "select 
c.id,
c.fecha,
p.titulo producto,
c.id_producto,
c.cantidad,
c.precio_lista,
c.precio_costo,
c.id_orden_compra,
ep.estado estado
from compras c, productos p, estado_pedido ep
where c.id_producto = p.id
and c.id_estado = ep.id
and ep.id = 2
order by c.fecha, c.id_orden_compra, p.titulo;"; 
$rsComprasSolicitado = mysqli_query($link,$sql);

$sql = "select 
c.id,
c.fecha,
p.titulo producto,
c.id_producto,
c.cantidad,
c.precio_lista,
c.precio_costo,
c.id_orden_compra,
ep.estado estado
from compras c, productos p, estado_pedido ep
where c.id_producto = p.id
and c.id_estado = ep.id
and ep.id = 5
order by c.fecha, c.id_orden_compra, p.titulo;"; 
$rsComprasEntregado = mysqli_query($link,$sql);


abstract class EstadoPedido
{
    const Pendiente = 1;
    const Solicitado = 2;
    const Disponible = 3;
    const Pagado = 4;
    const Entregado = 5;
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
        <title>Compras</title>
        
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">

        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();
                
                $('body').on('mousemove',function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });
                
                $("#addmorecompras").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-compras.ajax.php',
                        data:{'action':'addDataRowCompras'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveCompras').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formCompras").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-compras.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveCompras').addClass("disabled");
                        }
                    });
                });

                // agrupacion de tabla                
                $('#tbEnEspera').dataTable( {
                    "order": [[ 0, 'asc' ]]
                });

                $('#tbEntregadas').dataTable( {
                    "order": [[ 0, 'asc' ]]
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
                        <h1 class="mt-4">Compras</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <!-- PENDIENTES -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Compras Pendientes de Solicitar 
                            </div>
                            <div class="card-body">
                                <form id="formCompras" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveComprasAddMore">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Precio Lista</th>
                                            <th class="text-center">Precio Costo</th>
                                            <th class="text-center">Orden</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($rsComprasPendientes) > 0) {

                                            $sumCantidad = $sumPrecioLista = $sumPrecioCosto = 0;

                                            while($row = mysqli_fetch_assoc($rsComprasPendientes)) 
                                            {
                                            
                                                $colId = date('Ymd', strtotime($row['fecha']));
                                                $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                $colProducto = $row['producto'];
                                                $colCantidad = number_format($row['cantidad'], 0, ".", ""); 
                                                $colPrecioLista = "$ " . number_format($row['precio_lista'], 2, ".", "");
                                                $colPrecioCosto = "$ " . number_format($row['precio_costo'], 2, ".", "");
                                                $colEstado = $row['estado'];
                                                $colOrden = $row['id_orden_compra'];

                                                // totalizo
                                                $sumCantidad = $sumCantidad + $row['cantidad'];     
                                                $sumPrecioLista = $sumPrecioLista + ($row['precio_lista'] * $row['cantidad']);
                                                $sumPrecioCosto = $sumPrecioCosto + ($row['precio_costo'] * $row['cantidad']);
            
                                        ?>                                            
                                        <tr>
                                            <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo $colFecha; ?></td>
                                            <td>
                                                <?php echo $colProducto; ?>
                                                <a href="view-productos.php?forwardOk=compras.php&id=<?php echo $row['id_producto'];?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                            </td>
                                            <td align="right"><?php echo $colCantidad; ?></td>
                                            <td align="right"><?php echo $colPrecioLista; ?></td>
                                            <td align="right"><?php echo $colPrecioCosto; ?></td>
                                            <td align="right"><?php echo $colOrden; ?></td>
                                            <td align="right"><?php echo $colEstado; ?></td>
                                            
                                            <td align="center">
                                                <a href="update-compras.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                <a href="solicitar-compras.php?forwardOk=compras.php&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-shopping-cart"></i></a>
                                                <a href="delete-entity.php?entityName=compras&forwardOk=compras.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta compra?');"><i class="fa fa-fw fa-trash"></i></a>
                                            </td>                            
                                        </tr>
                                    <?php
                                            }

                                            // FORMATEO TOTALES
                                            $colCantidad = number_format($sumCantidad, 0, ".", ""); 
                                            $colPrecioLista = "$ " . number_format($sumPrecioLista, 2, ".", "");
                                            $colPrecioCosto = "$ " . number_format($sumPrecioCosto, 2, ".", "");

                                    ?>
                                        <tr>
                                            <td colspan="2" align="center">TOTALES</td>
                                            <td align="right"><?php echo $colCantidad; ?></td>
                                            <td align="right"><?php echo $colPrecioLista; ?></td>
                                            <td align="right"><?php echo $colPrecioCosto; ?></td>
                                            <td colspan="2"></td>
                                        </tr>

                                    <?php
                                        }
                                        else
                                        {
                                        echo '0 results';
                                        }
                                    ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <a href="javascript:;" class="btn btn-danger" id="addmorecompras"><i class="fa fa-fw fa-plus-circle"></i> Nueva Compra</a>
                                                <button type="submit" name="saveCompras" id="saveCompras" value="saveCompras" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </form>
                            </div>
                        </div>
                        <!-- EN ESPERA -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Compras Solicitadas esperando entrega 
                            </div>
                            <div class="card-body">
                                <form id="formComprasSolicitadas" method="post" onSubmit="return false;">
                                <table class="table table-striped table-bordered" id="tbEnEspera" >
                                    <thead>
                                        <tr>
                                            <th data-field="shape" class="text-center">Fecha</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Precio Lista</th>
                                            <th class="text-center">Precio Costo</th>
                                            <th class="text-center">Orden</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($rsComprasSolicitado) > 0) {

                                            while($row = mysqli_fetch_assoc($rsComprasSolicitado)) 
                                            {
                                                $colId = date('Ymd', strtotime($row['fecha']));
                                                $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                $colProducto = $row['producto'];
                                                $colCantidad = number_format($row['cantidad'], 0, ".", ""); 
                                                $colPrecioLista = "$ " . number_format($row['precio_lista'], 2, ".", "");
                                                $colPrecioCosto = "$ " . number_format($row['precio_costo'], 2, ".", "");
                                                $colEstado = $row['estado'];
                                                $colOrden = $row['id_orden_compra'];
                                        ?>                                            
                                        <tr>
                                            <td align="center"><span style="display:none;"><?php echo $colId;?></span><?php echo $colFecha; ?></td>
                                            <td>
                                                <?php echo $colProducto; ?>
                                                <a href="view-productos.php?forwardOk=compras.php&id=<?php echo $row['id_producto'];?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                            </td>
                                            <td align="right"><?php echo $colCantidad; ?></td>
                                            <td align="right"><?php echo $colPrecioLista; ?></td>
                                            <td align="right"><?php echo $colPrecioCosto; ?></td>
                                            <td align="right"><?php echo $colOrden; ?></td>
                                            <td align="right"><?php echo $colEstado; ?></td>
                                            
                                            <td align="center">
                                                <a href="recibir-compras.php?forwardOk=compras.php&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                <a href="delete-entity.php?entityName=compras&forwardOk=compras.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta compra?');"><i class="fa fa-fw fa-trash"></i></a>
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
                        <!-- ENTREGADAS --> 
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Compras Entregadas
                            </div>
                            <div class="card-body">
                                <form id="formComprasEntregadas" method="post" onSubmit="return false;">
                                <table class="table table-striped table-bordered" id="tbEntregadas">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Precio Lista</th>
                                            <th class="text-center">Precio Costo</th>
                                            <th class="text-center">Orden</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($rsComprasEntregado) > 0) {

                                            while($row = mysqli_fetch_assoc($rsComprasEntregado)) 
                                            {
                                            
                                                $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                $colProducto = $row['producto'];
                                                $colCantidad = number_format($row['cantidad'], 0, ".", ""); 
                                                $colPrecioLista = "$ " . number_format($row['precio_lista'], 2, ".", "");
                                                $colPrecioCosto = "$ " . number_format($row['precio_costo'], 2, ".", "");
                                                $colEstado = $row['estado'];
                                                $colOrden = $row['id_orden_compra'];
                                        ?>                                            
                                        <tr>
                                            <td align="center"><?php echo $colFecha; ?></td>
                                            <td>
                                                <?php echo $colProducto; ?>
                                                <a href="view-productos.php?forwardOk=compras.php&id=<?php echo $row['id_producto'];?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                            </td>
                                            <td align="right"><?php echo $colCantidad; ?></td>
                                            <td align="right"><?php echo $colPrecioLista; ?></td>
                                            <td align="right"><?php echo $colPrecioCosto; ?></td>
                                            <td align="right"><?php echo $colOrden; ?></td>
                                            <td align="right"><?php echo $colEstado; ?></td>
                                            
                                            <td align="center">
                                                <a href="update-compras.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                <a href="delete-entity.php?entityName=compras&forwardOk=compras.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta compra?');"><i class="fa fa-fw fa-trash"></i></a>
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

                <?php include 'footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="./js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>

    </body>
</html>
