<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/editoriales.php";
require_once "../app/models/consignaciones.php";

$rsCompras = null;

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

    $id_editorial =  trim($_GET["id"]);

    $editorialDBO = new Editorial();
    $rsEditorial = $editorialDBO->getById($id_editorial);
    if($rsEditorial){
        $nombreEditorial = $rsEditorial['nombre'];
    } else {
        $nombreEditorial = "";
    }

    $consignacionDBO = new Consignaciones();
    $rsCompras = $consignacionDBO->getAllAPagarByEditorial($id_editorial);
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
        <title>Ver Consignacion</title>
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
                        <h1 class="mt-4">Ver Consignacion</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="consignaciones.php">Consignaciones</a></li>
                            <li class="breadcrumb-item active">Ver Consignacion</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <div  class="form-group">
                                    <label>Proveedor</label>
                                    <input type="text" name="nombreEditorial" value="<?php echo $nombreEditorial;?>" class="form-control" required="required" readOnly>
                                </div>
                                <br>
                                <div class="container-fluid px-4">
                                <div class="table-responsive mt-2">
                                <table class="table table-bordered table-striped text-center">
                                    <thead>
                                        <tr>
                                            <th>fecha</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Monto</th>
                                            <th>Total</th>
                                            <th>Comision</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $total_quantity = $total_price = $total_comision = 0;
                                        if(!is_null($rsCompras)){
                                            foreach($rsCompras as $item){
                                                $id_compra = $item["id"];
                                                $item_quantity = $item["cantidad"];
                                                $item_price = $item["precio_lista"];
                                                $item_costo = $item["precio_costo"];
                                                $item_total = $item_quantity * $item_price;
                                                $item_comision = $item_quantity * ($item_price - $item_costo);

                                                // totalizadores
                                                $total_quantity += $item_quantity;
                                                $total_price +=$item_total;
                                                $total_comision += $item_comision;
                                                $grand_total = $total_price - $total_comision;
                                                
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($item["fecha"])); ?></td>
                                        <td><?= $item["titulo"]; ?></td>
                                        <td><?= $item_quantity ?></td>
                                        <td>
                                        <i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_price,2); ?>
                                        </td>
                                        <input type="hidden" class="pprice" value="<?= $item_price ?>">
                                        
                                        <td align="right"><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_total,2); ?></td>
                                        <td align="right"><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_comision,2); ?></td>
                                    </tr>
                                    <?php 
                                            } 
                                        }
                                    ?>
                                    </tbody>
                                    <tfooter>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2"><b>Sub Total</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($total_price,2); ?></b></td>
                                        <input type="hidden" name="subTotal" value="<?php echo $total_price;?>">
                                    </tr>

                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2"><b>Comision</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($total_comision,2); ?></b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Cantidad Items</b></td>
                                        <td><?= $total_quantity ?></td>
                                        <td colspan="2"><b>Total a Pagar</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                    </tr>
                                    </tfooter>
                                </table>
                                </div>
                            </div>
                                <br> 
                                <a href="consignaciones.php" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-plus-circle"></i>Volver</a>
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
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>


    </body>
</html>
