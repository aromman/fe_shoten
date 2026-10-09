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
s.id id,
c.nombre canal,
p.sku sku,
p.titulo producto,
s.cantidad,
s.precio,
(s.cantidad * s.precio) total
from stock s, productos p, estado_pedido ep, canal c
where s.id_estado_pedido = ep.id
and s.id_producto = p.id
and s.id_canal = c.id
order by s.id_canal, p.titulo
;"; 

$result = mysqli_query($link,$sql);

abstract class Pagos
{
    const Efectivo = 1;
    const Transferencia = 2;
    const MercadoPago = 3;
    const CuentaDni = 4;
    const Tarjeta = 6;
    const Cancelado = 9;
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
        <title>Finalizar Evento</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
                $('#tbProductos').dataTable( {
                    "order": [[ 0, 'asc' ]]
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
                        <h1 class="mt-4">Eventos</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Detalle eventos</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Evento en Curso
                            </div>
                            <div class="card-body">
                                <form id="formPedidos" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="savePedidosAddMore">
                                <table class="table table-striped table-bordered" id="tbProductos">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Efectivo</th>
                                            <th class="text-center">Transferencia</th>
                                            <th class="text-center">Mercado Pago</th>
                                            <th class="text-center">Cuenta DNI</th>
                                            <th class="text-center">Tarjeta</th>
                                            <th class="text-center">No Vendido</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colSku = $row['sku'];
                                                    $colProducto = $row['producto'];
                                                    $colCantidad = $row['cantidad'];
                                                    $colPrecio = $row['precio'];
                                                    $colTotal = $row['total'];
                                        ?>

                                                <tr>
                                                    <td><?php echo $colSku;?></td>
                                                    <td><?php echo $colProducto;?></td>
                                                    <td><?php echo $colCantidad;?></td>
                                                    <td><?php echo $colPrecio;?></td>
                                                    <td><?php echo $colTotal;?></td>
                       
                                                    <!-- Efectivo -->
                                                    <td align="center">
                                                        <a href="action-eventos.php?id_pago=<?php echo Pagos::Efectivo; ?>&cantidad=<?php echo $colCantidad;?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-usd"></i></a>
                                                    </td>                            
                                                    <!-- Transferencia -->
                                                    <td align="center">
                                                        <a href="action-eventos.php?id_pago=<?php echo Pagos::Transferencia; ?>&cantidad=<?php echo $colCantidad;?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-credit-card"></i></a>
                                                    </td>                            
                                                    <!-- Mercado Pago -->
                                                    <td align="center">
                                                        <a href="action-eventos.php?id_pago=<?php echo Pagos::MercadoPago; ?>&cantidad=<?php echo $colCantidad;?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-credit-card"></i></a>
                                                    </td>                            
                                                    <!-- Cuenta DNI -->
                                                    <td align="center">
                                                        <a href="action-eventos.php?id_pago=<?php echo Pagos::CuentaDni; ?>&cantidad=<?php echo $colCantidad;?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-credit-card"></i></a>
                                                    </td>                            
                                                    <!-- Tarjeta -->
                                                    <td align="center">
                                                        <a href="action-eventos.php?id_pago=<?php echo Pagos::Tarjeta; ?>&cantidad=<?php echo $colCantidad;?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-credit-card"></i></a>
                                                    </td>                            
                                                    <!-- No Vendido -->
                                                    <td align="center">
                                                        <a href="action-eventos.php?id_pago=<?php echo Pagos::Cancelado; ?>&cantidad=<?php echo $colCantidad;?>&id=<?php echo $row['id'];?>" class="text-danger"><i class="fa fa-fw fa-thumbs-down"></i></a>
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

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
