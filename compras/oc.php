<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/medioPago.php";
require_once "../app/models/proveedores.php";
require_once "../app/models/estadoPedido.php";

$fecha = date("Y-m-d");
$proveedor = "";
$estado = "";
$medioPago = "";
$total_cost = 0;
$total_quantity = 0;
$itemsCompra = isset($_SESSION["oc_item"]) && is_array($_SESSION["oc_item"]) ? $_SESSION["oc_item"] : array();

if(!empty($_SESSION["oc_header"])) {
    if(isset($_SESSION["oc_header"]['fecha']) and $_SESSION["oc_header"]['fecha']!=""){
        $fecha = $_SESSION["oc_header"]['fecha'];
    } else {
        $fecha = date("Y-m-d");
    }
    if(isset($_SESSION["oc_header"]['proveedor']) and $_SESSION["oc_header"]['proveedor']!=""){
        $proveedor = $_SESSION["oc_header"]['proveedor'];
    } else {
        $proveedor = "";
    }
    if(isset($_SESSION["oc_header"]['estado']) and $_SESSION["oc_header"]['estado']!=""){
        $estado = $_SESSION["oc_header"]['estado'];
    } else {
        $estado = "";
    }
    if(isset($_SESSION["oc_header"]['medioPago']) and $_SESSION["oc_header"]['medioPago']!=""){
        $medioPago = $_SESSION["oc_header"]['medioPago'];
    } else {
        $medioPago = "";
    }
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

        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();
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
                        <h1 class="mt-4">Confirmar Compra</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="compras.php">Compras</a></li>
                            <li class="breadcrumb-item active">Confirmar Compra</li>
                        </ol>
                        <div class="card-body">
                            <form id="fromPedido" method="post" action="../bff/compras/confirmar.php">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Proveedor</label>
                                    <?php
                                        $nombreProveedor = "";
                                        if ($proveedor !== "") {
                                            $proveedorPDO = new Proveedor();
                                            $proveedorObj = $proveedorPDO->getById($proveedor);
                                            if (is_array($proveedorObj) && isset($proveedorObj['nombre'])) {
                                                $nombreProveedor = mb_strtoupper($proveedorObj['nombre'],'UTF-8');
                                            }
                                        }
                                    ?>
                                    <input type="text" name="nombreProveedor" value="<?php echo $nombreProveedor;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="proveedor" value="<?php echo $proveedor;?>">
                                </div>
                                <div  class="form-group">
                                    <label>Estado</label>
                                    <?php
                                        $nombreEstado = "";
                                        if ($estado !== "") {
                                            $estadoPedidoPDO = new EstadoPedido();
                                            $estadoPedidoObj = $estadoPedidoPDO->getById($estado);
                                            if (is_array($estadoPedidoObj) && isset($estadoPedidoObj['estado'])) {
                                                $nombreEstado = mb_strtoupper($estadoPedidoObj['estado'],'UTF-8');
                                            }
                                        }
                                    ?>
                                    <input type="text" name="nombreEstado" value="<?php echo $nombreEstado;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="estado" value="<?php echo $estado;?>">
                                </div>
                                <div  class="form-group">
                                    <label>Medio Pago</label>
                                    <?php
                                        $nombreMedioPago = "";
                                        if ($medioPago !== "") {
                                            $medioPagoPDO = new MedioPago();
                                            $medioPagoObj = $medioPagoPDO->getById($medioPago);
                                            if (is_array($medioPagoObj) && isset($medioPagoObj['nombre'])) {
                                                $nombreMedioPago = mb_strtoupper($medioPagoObj['nombre'],'UTF-8');
                                            }
                                        }
                                    ?>
                                    <input type="text" name="nombreMedioPago" value="<?php echo $nombreMedioPago;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="medioPago" value="<?php echo $medioPago;?>">
                                </div>
                                <div class="container-fluid px-4">
                                    <div class="table-responsive mt-2">
                                        <table class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Venta</th>
                                                    <th>Precio Compra</th>
                                                    <th>Total Compra</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                    <?php
                                        if(!empty($itemsCompra)){
                                            foreach ($itemsCompra as $item){
                                                $item_quantity = isset($item["cantidad"]) ? (float) $item["cantidad"] : 0;
                                                $item_precio_costo = isset($item["precioCosto"]) ? (float) $item["precioCosto"] : 0;
                                                $item_cost = $item_quantity * $item_precio_costo;
                                    ?>
                                                <tr>
                                                    <td><?= isset($item["sku"]) ? $item["sku"] : ""; ?></td>
                                                    <input type="hidden" class="pid" value="<?= isset($item["id"]) ? $item["id"] : ""; ?>">
                                                    <td><?= isset($item["titulo"]) ? $item["titulo"] : ""; ?></td>
                                                    <td><?= $item_quantity ?></td>
                                                    <td><?= isset($item["precioLista"]) ? $item["precioLista"] : 0; ?></td>
                                                    <td><?= $item_precio_costo; ?></td>
                                                    <td><?= $item_cost; ?></td>
                                                </tr>
                                    <?php
                                                $total_cost += $item_cost;
                                                $total_quantity += $item_quantity;
                                            }
                                        } else {
                                    ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No hay productos agregados</td>
                                                </tr>
                                    <?php
                                        }
                                    ?>

                                                <tr>
                                                    <td colspan=2 align="right">
                                                        <b>Totales</b></td>
                                                        <td><?= $total_quantity; ?></td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td><?= $total_cost; ?></td>
                                                        <input type="hidden" name="cantidad" value="<?php echo $total_quantity;?>">
                                                        <input type="hidden" name="monto" value="<?php echo $total_cost;?>">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <br>
                                <button type="submit" class="btn btn-primary"><i class="far fa-credit-card"></i>&nbsp;&nbsp;Confirmar</button>
                                <a href="buy.php" class="btn btn-warning"><i class="fa fa-fw fa-pencil"></i>&nbsp;&nbsp;Modificar</a>
                                <a href="action-cart.php?action=empty&forwardOk=compras.php" class="btn btn-danger" onClick="return confirm('Esta seguro de querer cancelar la compra?');"><i class="fa fa-fw fa-trash"></i>&nbsp;&nbsp;Cancelar</a>
                            </form>
                        </div>
                    </div>
                </main>
                <?php include_once '../footer.php';?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
