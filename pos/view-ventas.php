<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/ventasHeader.php";
require_once "../app/models/ventas.php";

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){

        $id_ventas_header =  trim($_GET["id"]);

        $ventaHeaderPDO = new VentasHeader();
        $rsVentasHeader = $ventaHeaderPDO->getById($id_ventas_header);
        if (!is_null($rsVentasHeader)){
            $row =$rsVentasHeader;
            $fecha = $row['fecha'];
            $nombreCliente = $row['cliente'];
            $nombreMedioPago = $row['medioPago'];
            $total_price = $row['subTotal']; 
            $descuento = $row['descuento']; 
            $grand_total = $row['total']; 
            $total_quantity = $row['unidades'];
        }
        
        $ventasPDO = new Ventas();
        $rsVentas = $ventasPDO->getAllByHeader($id_ventas_header);

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
                            <li class="breadcrumb-item"><a href="ventas.php">Ventas</a></li>
                            <li class="breadcrumb-item active">Detalle Venta</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Cliente</label>
                                    <input type="text" name="nombreCliente" value="<?php echo $nombreCliente;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Medio Pago</label>
                                    <input type="text" name="nombreMedioPago" value="<?php echo $nombreMedioPago;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div class="container-fluid px-4">
                                <div class="table-responsive mt-2">
                                <table class="table table-bordered table-striped text-center">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php

                                        if (!is_null($rsVentas) ) {
                                            foreach ($rsVentas as $item){

                                                $item_quantity = $item["unidades"];
                                                $item_price = $item["precioUnitario"];
                                                $item_total = $item_quantity * $item_price;
                                                
                                    ?>
                                    <tr>
                                        <td><?= $item["sku"]; ?></td>
                                        <td><?= $item["producto"]; ?></td>
                                        <td>
                                        <i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_price,2); ?>
                                        </td>
                                        <input type="hidden" class="pprice" value="<?= $item_price ?>">
                                        <td><?= $item_quantity ?></td>
                                        <td align="right"><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_total,2); ?></td>
                                    </tr>
                                    <?php 
                                            } 
                                        }
                                    ?>
                                    </tbody>
                                    <tfooter>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="2"><b>Sub Total</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($total_price,2); ?></b></td>
                                        <input type="hidden" name="subTotal" value="<?php echo $total_price;?>">
                                    </tr>

                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="2"><b>Descuento</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($descuento,2); ?></b></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <b>Cantidad Items</b></td>
                                            <td><?= $total_quantity ?></td>
                                        </td>
                                        <td colspan="2"><b>Total Venta</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
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

    </body>
</html>
