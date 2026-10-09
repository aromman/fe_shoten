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
v.id id,
v.fecha fecha,
c.nombre canal,
p.titulo producto,
v.unidades unidades,
v.precio_unitario precioUnitario,
v.descuento descuento,
v.motivo_descuento motivoDescuento,
v.id_producto,
v.total total,
mp.nombre medioPago,
CONCAT (cli.apellido,' ',cli.nombre)  cliente,
ps.nombre serie
from ventas v, canal c, productos p, clientes cli, medio_pago mp, productos_serie ps
where v.id_canal = c.id
and p.id = v.id_producto
and cli.id = v.id_cliente 
and mp.id = v.id_medio_pago
and p.id_serie = ps.id
order by v.fecha desc, c.nombre, p.titulo"; 

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
        <title>Ventas</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
                
                $("#addmoreventas").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-ventas.ajax.php',
                        data:{'action':'addDataRowVentas'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveVentas').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formVentas").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-ventas.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveVentas').addClass("disabled");
                        }
                    });
                });
                
                $('#tbVentas').dataTable( {
                    "order": [[ 0, 'desc' ]]
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
                        <h1 class="mt-4">Ventas</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Ventas
                            </div>
                            <div class="card-body">
                                <form id="formVentas" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveVentasAddMore">
                                <div class="table-responsive-sm">
                                <table class="table table-striped table-bordered" id="tbVentas">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Canal</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Serie</th>
                                            <th class="text-center">Unidades</th>
                                            <th class="text-center">P.U</th>
                                            <th class="text-center">Descuento</th>
                                            <th class="text-center">Motivo Descuento</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Medio Pago</th>
                                            <th class="text-center">Cliente</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colCanal = $row['canal'];
                                                    $colProducto = $row['producto'];
                                                    $colUnidades = number_format($row['unidades'], 0, ".", ""); 
                                                    $colPrecioUnitario = number_format($row['precioUnitario'], 2, ".", ""); 
                                                    $colDescuento = number_format($row['descuento'], 2, ".", ""); 
                                                    $colMotivoDescuento = $row['motivoDescuento'];
                                                    $colTotal = number_format($row['total'], 2, ".", ""); 
                                                    $colMedioPago = $row['medioPago'];
                                                    $colCliente = $row['cliente'];
                                                    $colSerie = $row['serie'];
                                            
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colCanal;?></td>
                                                    <td>
                                                        <?php echo $colProducto;?>
                                                        <a href="productos/view-productos.php?forwardOk=/ventas.php&id=<?php echo $row['id_producto'];?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                    </td>
                                                    <td><?php echo $colSerie;?></td> 
                                                    <td align="right"><?php echo $colUnidades; ?></td>
                                                    <td align="right"><?php echo $colPrecioUnitario; ?></td>
                                                    <td align="right"><?php echo $colDescuento; ?></td>
                                                    <td><?php echo $colMotivoDescuento; ?></td>
                                                    <td align="right"><?php echo $colTotal; ?></td>
                                                    <td><?php echo $colMedioPago; ?></td>
                                                    <td><?php echo $colCliente; ?></td>
                                                    
                                                    <td align="center">
                                                        <a href="update-ventas.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="delete-entity.php?entityName=ventas&forwardOk=ventas.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta venta?');"><i class="fa fa-fw fa-trash"></i></a>
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
                                            <td colspan="6">
                                                <a href="javascript:;" class="btn btn-danger" id="addmoreventas"><i class="fa fa-fw fa-plus-circle"></i> Nueva Venta</a>
                                                <button type="submit" name="saveVentas" id="saveVentas" value="saveVentas" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
