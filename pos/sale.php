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
require_once "../app/models/clientes.php";
require_once "../app/models/canal.php";
require_once "../app/models/descuento.php";
require_once "../app/models/producto.php";
require_once "../app/models/ventasHeader.php";
require_once "../app/models/ventas.php";
require_once "../app/models/cuentasMovimientos.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Processing form data when form is submitted
if(isset($_POST["fecha"]) && !empty($_POST["fecha"])){

    $submittedToken = $_POST['sale_token'] ?? '';
    $sessionToken = $_SESSION['sale_token'] ?? '';
    unset($_SESSION['sale_token']); // se consume siempre, sea válido o no

    if ($submittedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        // token ya usado o inválido: submit duplicado, no reprocesar
        header('location: ventas.php');
        exit();
    }

    try {
        //error_log(PHP_EOL."Insertando", 3, "my-errors.log");

        $fecha = $_POST["fecha"];
        $cliente = $_POST["cliente"];
        $medioPago = $_POST["medioPago"];
        $cantidad = $_POST["cantidad"];
        $subTotal = $_POST["subTotal"];
        $descuento = $_POST["descuento"]; 
        $total = $_POST["total"];
        $id_canal = isset($_POST["canal"]) && $_POST["canal"] !== '' ? (int) $_POST["canal"] : (int) ($_SESSION["user.canal"] ?? 0);
        $sorteo = $_POST["sorteo"];
        $porcentaje = $_POST["porcentaje"];
        
        if ($id_canal <= 0) {
            throw new \RuntimeException('CANAL_INVALIDO');
        }

        $username = $_SESSION["user.username"];
        
        $updateTime = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y/m/d H:i:sa');
        
        $ventasHeaderPDO = new VentasHeader();
        $ventasHeaderPDO->idCanal = $id_canal;
        $ventasHeaderPDO->fecha = $fecha;
        $ventasHeaderPDO->idCliente = $cliente;
        $ventasHeaderPDO->idMedioPago = $medioPago; 
        $ventasHeaderPDO->cantidad = $cantidad;
        $ventasHeaderPDO->subTotal = $subTotal;
        $ventasHeaderPDO->total = $total;
        $ventasHeaderPDO->updateDate = $updateTime;
        $ventasHeaderPDO->userName = $username;
        $ventasHeaderPDO->descuento = $descuento;
        $id_header = $ventasHeaderPDO->create();

        $sqlItemUpdate = array(); 
        $sql = array(); 

        $ventasPDO = new Ventas();
        $ventasPDO->createMasivo($id_header, $fecha, $id_canal, $cliente, $medioPago, $porcentaje, $_SESSION["cart_item"]);

        // actualizo stock
        $productoUdate = new Producto();
        $productoUdate->updateStock($_SESSION["cart_item"]);
        unset($productoUdate);

        // Incerto movimiento en cuenta
        $medioPagoPDO = new MedioPago();
        $mediosPago = $medioPagoPDO->getById($medioPago);
        if (!is_array($mediosPago) || !isset($mediosPago['id_cuenta']) || (int) $mediosPago['id_cuenta'] <= 0) {
            throw new \RuntimeException('MEDIO_PAGO_SIN_CUENTA');
        }
        $idCuenta = (int) $mediosPago['id_cuenta'];
        unset($medioPagoPDO);
        // inserto movimiento
        $idCausal = 1; // ventas
        $horaActual = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('H:i:s');
        $fechaMovimiento = $fecha . ' ' . $horaActual; // conserva la fecha de la venta (puede ser un día anterior) y agrega la hora actual

        $cuentaMovimientoPDO = new CuentasMovimientos();
        $cuentaMovimientoPDO->idCanal = $id_canal;
        $cuentaMovimientoPDO->idCuenta = $idCuenta;
        $cuentaMovimientoPDO->fecha = $fechaMovimiento;
        $cuentaMovimientoPDO->tipoMovimiento = "C";
        $cuentaMovimientoPDO->descripcion = "Venta-".$id_header;
        $cuentaMovimientoPDO->monto = $total;
        $cuentaMovimientoPDO->consolidado = FALSE;
        $cuentaMovimientoPDO->idCausal = $idCausal;
        $cuentaMovimientoPDO->origenTipo = 'venta';
        $cuentaMovimientoPDO->origenId = $id_header;
        $cuentaMovimientoPDO->create();

        unset($_SESSION["cart_item"]);
        unset($_SESSION["cart_header"]);

        header('location: ventas.php');
        exit();
    } catch (\Throwable $error) {
        error_log($error->getMessage());
        header('location: sale.php');
        exit();
    }
    
} else{

    if(!empty($_SESSION["cart_header"])) {
        if(isset($_SESSION["cart_header"]['fecha']) and $_SESSION["cart_header"]['fecha']!=""){
            $fecha = $_SESSION["cart_header"]['fecha'];
        } else {
            $fecha = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d');
        }
        if(isset($_SESSION["cart_header"]['cliente']) and $_SESSION["cart_header"]['cliente']!=""){
            $cliente = $_SESSION["cart_header"]['cliente'];
        } else {
            $cliente = "";
        }
        if(isset($_SESSION["cart_header"]['medioPago']) and $_SESSION["cart_header"]['medioPago']!=""){
            $medioPago = $_SESSION["cart_header"]['medioPago'];
        } else {
            $medioPago= "";
        }
        if(isset($_SESSION["cart_header"]['canal']) and $_SESSION["cart_header"]['canal']!=""){
            $canal = $_SESSION["cart_header"]['canal'];
        } else {
            $canal= "";
        }
        if(isset($_SESSION["cart_header"]['sorteo']) and $_SESSION["cart_header"]['sorteo']!=""){
            $sorteo = $_SESSION["cart_header"]['sorteo'];
        } else {
            $sorteo= FALSE;
        }

    }

    $saleToken = bin2hex(random_bytes(16));
    $_SESSION['sale_token'] = $saleToken;
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

        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();

                $('#formSale').on('submit', function() {
                    $(this).find('button[type="submit"]').prop('disabled', true).html('Procesando...');
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
                        <h1 class="mt-4">Confirmar Venta</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="ventas.php">Ventas</a></li>
                            <li class="breadcrumb-item active">Confirmar Venta</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <input type="hidden" name="sale_token" value="<?php echo htmlspecialchars($saleToken); ?>">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Cliente</label>
                                    <?php
                                        $clientePDO = new Cliente();
                                        $val = $clientePDO->getById($cliente);
                                        $nombreCliente = mb_strtoupper($val['apellido'] . ' '.$val['nombre'],'UTF-8');
                                        $id_descuento = $val['id_descuento'];
                                        unset($clientePDO);
                                    ?>
                                    <input type="text" name="nombreCliente" value="<?php echo $nombreCliente;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="cliente" value="<?php echo $cliente;?>">
                                </div>
                                <div  class="form-group">
                                    <label>Medio Pago</label>
                                    <?php
                                        $medioPagoPDO = new MedioPago();
                                        $val = $medioPagoPDO->getById($medioPago);
                                        $nombreMedioPago = mb_strtoupper($val['nombre'],'UTF-8');
                                        unset($medioPagoPDO);
                                    ?>
                                    <input type="text" name="nombreMedioPago" value="<?php echo $nombreMedioPago;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="medioPago" value="<?php echo $medioPago;?>">
                                </div>
                                <div  class="form-group">
                                    <label>Descuento</label>
                                    <?php
                                        $porcentaje = 0;
                                        $nombreDescuento = "";
                                        if (!empty($id_descuento)){
                                            $descuentoPDO = new Descuento();
                                            $val = $descuentoPDO->getById($id_descuento);
                                            $nombreDescuento = mb_strtoupper($val['nombre'],'UTF-8');
                                            $porcentaje = $val['porcentaje'];
                                            unset($descuentoPDO);
                                        }
                                    ?>
                                    <input type="text" name="nombreDescuento" value="<?php echo $nombreDescuento;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="descuento" value="<?php echo $id_descuento;?>">
                                </div>
                                <div  class="form-group">
                                    <label>Canal</label>
                                    <?php
                                        $canalPDO = new Canal();
                                        $val = $canalPDO->getById($canal);
                                        $nombreCanal = mb_strtoupper($val['nombre'],'UTF-8');
                                        unset($canalPDO);
                                    ?>
                                    <input type="text" name="nombreCanal" value="<?php echo $nombreCanal;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="canal" value="<?php echo $canal;?>">
                                </div>
                                <br>
                                <div  class="form-group">
                                    <label>Participa Sorteo</label>
                                    <input type="text" name="nombreSorteo" value="<?php echo $sorteo == 1 ? 'Si' : 'No'; ?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="sorteo" value="<?php echo $sorteo;?>">
                                </div>
                                <br>
                                <div class="container-fluid px-4">
                                <div class="table-responsive mt-2">
                                <table class="table table-bordered table-striped text-center">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th>Total (Descuento)</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        if(isset($_SESSION["cart_item"])){
                                            $total_quantity = 0;
                                            $total_price = 0;
                                            $total_descuento = 0;
                                            $grand_total = 0;
                                            foreach ($_SESSION["cart_item"] as $item){
                                                $item_quantity = $item["cantidad"];
                                                $item_price = $item["precio"];
                                                $item_subTotal = $item_quantity * $item_price;
                                                
                                                $item_descuento = 0;
                                                if ($porcentaje > 0){
                                                    $item_descuento = ($item_price * ($porcentaje / 100)) * $item_quantity;     
                                                }
                                                $item_total = $item_subTotal - $item_descuento;
                                                
                                                // totalizadores
                                                $total_price += $item_subTotal; 
                                                $total_quantity += $item_quantity;
                                                $total_descuento += $item_descuento;
                                                $grand_total += $item_total;
                                    ?>
                                    <tr>
                                        <td><?= $item["sku"]; ?></td>
                                        <input type="hidden" class="pid" value="<?= $item["id"]; ?>">
                                        <td><?= $item["titulo"]; ?></td>
                                        <td>
                                        <i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_price,2); ?>
                                        </td>
                                        <input type="hidden" class="pprice" value="<?= $item_price ?>">
                                        <td><?= $item_quantity ?></td>
                                        <td align="right"><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_subTotal,2); ?> (<?= number_format($item_descuento,2); ?>)</td>
                                        
                                    </tr>
                                    <?php 
                                            }
                                        }
                                    ?>

                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="2"><b>Sub Total</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($total_price,2); ?></b></td>
                                        <input type="hidden" name="subTotal" value="<?php echo $total_price;?>">
                                    </tr>

                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="2"><b>Descuento</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($total_descuento,2); ?></b></td>
                                        <input type="hidden" name="descuento" value="<?php echo $total_descuento;?>">
                                        <input type="hidden" name="porcentaje" value="<?php echo $porcentaje;?>">
                                    </tr>
                                    <tr>
                                        <td>
                                            <b>Cantidad Items</b></td>
                                            <td><?= $total_quantity ?></td>
                                            <input type="hidden" name="cantidad" value="<?php echo $total_quantity;?>">
                                        </td>
                                        <td colspan="2"><b>Total A Pagar</b></td>
                                        <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                        <input type="hidden" name="total" value="<?php echo $grand_total;?>">
                                    </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                                <br> 
                                <button type="submit" class="btn btn-primary"><i class="far fa-credit-card"></i>&nbsp;&nbsp;Confirmar</button>
                                <a href="pos.php" class="btn btn-warning"><i class="fa fa-fw fa-pencil"></i>&nbsp;&nbsp;Modificar</a>
                                <a href="action-cart.php?action=empty&forwardOk=ventas.php" class="btn btn-danger" onClick="return confirm('Esta seguro de querer cancelar la venta?');"><i class="fa fa-fw fa-trash"></i>&nbsp;&nbsp;Cancelar</a>
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
