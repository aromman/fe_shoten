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

if(isset($_GET["fecha"]) && !empty(trim($_GET["fecha"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){

        $fecha =  trim($_GET["fecha"]);
        $id_canal =  trim($_GET["canal"]);

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
        mp.nombre medioPago
        from ventas v, canal c, productos p, medio_pago mp
        where v.id_canal = c.id
        and p.id = v.id_producto
        and mp.id = v.id_medio_pago
        and v.fecha = '".$fecha."'
        and c.id = ".$id_canal."
        order by v.id desc, p.titulo";
        //error_log(PHP_EOL.$sql, 3, "my-errors.log");
        $rsVentas = $db->query($sql);
        
        $colFecha = date('d/m/Y', strtotime($fecha));

        $sql = "select
        c.nombre,
        sum(v.total)  monto
        from ventas v, medio_pago mp, cuentas c
        where
            mp.id = v.id_medio_pago
            and v.fecha = '".$fecha."'
            and v.id_canal = ".$id_canal."
            and mp.id_cuenta = c.id
        group by c.nombre;";
        $rsMediosPago = $db->query($sql);

        $sql = "select
        ps.nombre,
        sum(v.unidades)  unidades
        from ventas v, productos_serie ps, productos p
        where v.fecha = '".$fecha."'
            and v.id_canal = ".$id_canal."
            and v.id_producto = p.id
            and p.id_serie = ps.id
        group by ps.nombre;";
        $rsSeries = $db->query($sql);


        $sql = "select
        v.id_producto,
        p.titulo producto,
        sum(v.unidades) unidades,
        sum(v.total)  monto
        from ventas v, canal c, productos p
        where v.id_canal = c.id
        and p.id = v.id_producto
        and v.fecha = '".$fecha."'
        and c.id = ".$id_canal."
        group by v.id_producto
        order by unidades desc, monto desc, p.titulo";
        $rsTopVentas =  $db->query($sql);

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
        <title>Ventas</title>
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

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Detalle Venta</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="ventas-canales.php">Ventas Canales</a></li>
                            <li class="breadcrumb-item active">Detalle Venta dia <?php echo $colFecha; ?></li>
                        </ol>
                        <div class="row">

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-header">Ventas por Tipo de Pago</div>
                                    <div class="list-group list-group-flush">
                                    <?php
                                        $totalMediosPago = 0;
                                    if (mysqli_num_rows($rsMediosPago   ) > 0) {

                                        while($row = mysqli_fetch_assoc($rsMediosPago))
                                        {
                                            $colMedioPago = $row['nombre'];
                                            $colMontoMedioPago = $row['monto']; 
                                            $totalMediosPago += $colMontoMedioPago;

                                      ?>
                                        <a href="#" class="list-group-item list-group-item-action"><?php echo $colMedioPago;?>: $ <?php echo number_format($colMontoMedioPago, 2, ".", "")?></a>
                                    <?php
                                        }
                                    }
                                    ?>  
                                      
                                    </div>
                                    <div class="card-footer">
                                        <p class="card-text">Total : $ <?php echo number_format($totalMediosPago, 2, ".", "");?></p>
                                    </div>                                    
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-info text-white mb-4">
                                    <div class="card-header">Stock Afectado</div>
                                    <div class="list-group list-group-flush">
                                    <?php
                                        $totalSerie = 0;
                                    if (mysqli_num_rows($rsSeries) > 0) {

                                        while($row = mysqli_fetch_assoc($rsSeries))
                                        {
                                            $colSerie = $row['nombre'];
                                            $colUnidades = $row['unidades'];
                                            $totalSerie += $colUnidades;

                                      ?>
                                        <a href="#" class="list-group-item list-group-item-action"><?php echo $colSerie;?>: <?php echo number_format($colUnidades, 0, ".", "")?></a>
                                    <?php
                                        }
                                    }
                                    ?>  

                                    </div>
                                    <div class="card-footer">
                                        <p class="card-text">Total : <?php echo number_format($totalSerie, 0, ".", "");?></p>
                                    </div>                                    
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-header">Unidades Vendidas</div>
                                    <div class="list-group list-group-flush">
                                    <?php
                                        $totalUnidades = 0;
                                        $totalMonto = 0;
                                    if (mysqli_num_rows($rsTopVentas) > 0) {

                                        while($row = mysqli_fetch_assoc($rsTopVentas))
                                        {
                                            $colProducto = $row['producto'];
                                            $colUnidades = $row['unidades'];
                                            $colMonto = $row['monto'];
                                            $totalUnidades += $colUnidades;
                                            $totalMonto += $colMonto;

                                      ?>
                                        <a href="#" class="list-group-item list-group-item-action"><?php echo $colProducto;?>: <?php echo number_format($colUnidades, 0, ".", "")?> - $ <?php echo number_format($colMonto, 2, ".", "")?></a>
                                    <?php
                                        }
                                    }
                                    ?>
                                    </div>
                                    <div class="card-footer">
                                        <p class="card-text">Total : <?php echo number_format($totalUnidades, 0, ".", "");?> - $ <?php echo number_format($totalMonto, 2, ".", "")?></p>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="card-body">
                                <form id="formSale" method="post">

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                    <th>Medio Pago</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php

                                                if (mysqli_num_rows($rsVentas) > 0) {
                                                    $colOrden = $grand_items = $grand_total = 0;

                                                    while($row = mysqli_fetch_assoc($rsVentas))
                                                    {
                                                        $colOrden = $colOrden + 1;
                                                        $colProducto = $row['producto'];
                                                        $colPrecioUnitario = number_format($row['precioUnitario'], 2, ".", ""); 
                                                        $colUnidades = number_format($row['unidades'], 0, ".", ""); 
                                                        $colTotal = number_format($row['total'], 2, ".", ""); 
                                                        $colMedioPago = $row['medioPago'];
                                                        
                                                        $grand_items += $row['unidades'];
                                                        $grand_total += $row['total'];
                                            ?>
                                            <tr>
                                                <td><?= $colOrden; ?></td>
                                                <td><?php echo $colProducto;?></td>
                                                <td align="right"><?php echo $colPrecioUnitario; ?></td>
                                                <td align="right"><?php echo $colUnidades; ?></td>
                                                <td align="right"><?php echo $colTotal; ?></td>
                                                <td><?php echo $colMedioPago; ?></td>

                                            </tr>
                                            <?php
                                                    }
                                                }
                                            ?>
                                            </tbody>
                                            <tfooter>
                                            <tr>
                                                <td colspan="3"></td>
                                                <td colspan="2"><b>Cantidad Items</b></td>
                                                <td align="right"><b><?= $grand_items; ?></b></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"></td>
                                                <td colspan="2"><b>Total Venta</b></td>
                                                <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                            </tr>
                                            </tfooter>
                                        </table>
                                    </div>
                                    <br>
                                    <a href="<?php echo $_REQUEST['forwardOk'];?>" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-plus-circle"></i>Volver</a>
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
