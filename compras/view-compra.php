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

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){

        $id_header =  trim($_GET["id"]);

        $sql = "select
        oc.id,
        oc.fecha,
        oc.id_proveedor,
        p.nombre proveedor,
        oc.cantidad,
        oc.monto,
        oc.fecha_entrega,
        ep.estado,
        mp.nombre medioPago
        from orden_compra oc, proveedores p, estado_pedido ep, medio_pago mp
        where oc.id_proveedor = p.id
        and oc.id_estado_pedido = ep.id
        and oc.id_medio_pago = mp.id
        and oc.id = ".$id_header.";";
        
        //error_log(PHP_EOL.$sql, 3, "my-errors.log");  
        $rsHeader = $db->query($sql);

        if (mysqli_num_rows($rsHeader) > 0) {
            $row = mysqli_fetch_assoc($rsHeader);

            $fecha = $row['fecha'];
            $nombreProveedor = $row['proveedor'];
            $fecha_entrega = $row['fecha_entrega'];
            $estado = $row['estado'];
            $total_quantity = $row['cantidad'];
            $total_amount = $row['monto'];
            $medio_pago = $row['medioPago']; 
        }    

        $sql = "select 
        p.sku,
        p.titulo,
        c.precio_lista,
        c.precio_costo,
        c.cantidad,
        (c.precio_costo * c.cantidad) costo_total
        from compras c, productos p
        where c.id_orden_compra = ".$id_header."
        and c.id_producto = p.id
        order by p.titulo
        ;";
        //error_log(PHP_EOL.$sql, 3, "my-errors.log");  
        $rsDetalle = $db->query($sql);

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
                        <h1 class="mt-4">Detalle Compra</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="ventas.php">Compras</a></li>
                            <li class="breadcrumb-item active">Detalle Compra</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Proveedor</label>
                                    <input type="text" name="nombreCliente" value="<?php echo $nombreProveedor;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div  class="form-group">
                                    <label>Fecha Entrega</label>
                                    <input type="date" id="fechaEntrega" name="fechaEntrega" value="<?php echo $fecha_entrega;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div  class="form-group">
                                    <label>Estado</label>
                                    <input type="text" name="estado" value="<?php echo $estado;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div  class="form-group">
                                    <label>Medio Pago</label>
                                    <input type="text" name="medioPago" value="<?php echo $medio_pago;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div class="container-fluid px-4">
                                <div class="table-responsive mt-2">
                                <table class="table table-bordered table-striped text-center">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Producto</th>
                                            <th>Precio Lista</th>
                                            <th>Cantidad</th>
                                            <th>Precio Costo</th>
                                            <th>Costo Item</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        if (mysqli_num_rows($rsDetalle) > 0) {
                                            while($item = mysqli_fetch_assoc($rsDetalle))
                                            {
                                    ?>
                                    <tr>
                                        <td><?= $item["sku"]; ?></td>
                                        <td><?= $item["titulo"]; ?></td>
                                        <td><?= number_format($item["precio_lista"], 2, ".", ""); ?></td>
                                        <td><?= $item["cantidad"] ?></td>
                                        <td><?= number_format($item["precio_costo"], 2, ".", ""); ?></td>
                                        <td><?= number_format($item["costo_total"], 2, ".", ""); ?></td>
                                    </tr>
                                    <?php 
                                            } 
                                        }
                                    ?>
                                    </tbody>
                                    <tfooter>
                                        <tr>
                                            <td colspan=5 align="right">
                                                <b>Cantidad Items</b></td>
                                                <td><?= $total_quantity ?></td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan=5 align="right">
                                                <b>Costo Total</b></td>
                                                <td><?= $total_amount ?></td>
                                            </td>
                                        </tr>
                                    </tfooter>    
                                </table>
                                </div>
                            </div>
                                <br> 
                                <a href="<?php echo $_REQUEST['forwardOk'];?>" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-plus-circle"></i>Volver</a>
                            </form>
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
