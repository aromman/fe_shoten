<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// model
require_once "../app/models/clientes.php";
require_once "../app/models/medioPago.php";
require_once "../app/models/canal.php";
require_once "../app/models/producto.php";
require_once "../app/models/editoriales.php";
require_once "../app/models/productoSerie.php";
require_once "../app/models/productoFormato.php";


if(isset($_POST["saleAction"]) && !empty($_POST["saleAction"]) && $_POST["saleAction"]=="add" && 
    isset($_POST["id"]) && !empty($_POST["id"])){

    //error_log(PHP_EOL."Sumando Item ". $_POST["id"], 3, "my-errors.log");      

    $idItem = $_POST["id"];
  
    $precioLista = $_POST["precioVenta"];
    //$admiteDescuento = $_POST["admiteDescuento"];
    
    $precioActual = $_POST["precioActual"];
    //$admiteDescuentoActual = $_POST["admiteDescuentoActual"];

    $precioOfertaAplicada = isset($_POST["precioOfertaAplicada"]) ? $_POST["precioOfertaAplicada"] : "0";
    $precioOferta = isset($_POST["precioOferta"]) ? $_POST["precioOferta"] : "0";
    $actualizarPrecioOferta = false;

    if ($precioOfertaAplicada=="1") {
        $actualizarPrecioOferta = ((float)$precioLista != (float)$precioOferta);
    } else {
        $actualizarPrecioOferta = ((float)$precioLista != (float)$precioActual);
    }

    if ($actualizarPrecioOferta){
        //error_log(PHP_EOL."Actualizar Precio ", 3, "my-errors.log");         

        $productoUdate = new Producto();
        $productoUdate->setId($idItem);
        $productoUdate->precioOferta = $precioLista;
        $productoUdate->update();
        unset($productoUdate);

    }
    // agrego producto a session
    $titulo = $_POST["producto"];
    $sku = $_POST["sku"];
    $cantidad = $_POST["cantidad"];
    $costo = $_POST["costo"];

    $itemArray = array($idItem=>array('id'=>$idItem, 'titulo'=>$titulo, 'sku'=>$sku, 'cantidad'=>$cantidad, 'precio'=>$precioLista, 'costo'=>$costo));
    if(!empty($_SESSION["cart_item"])) {
        $found = FALSE;
        foreach($_SESSION["cart_item"] as $k => $v) {
            if($idItem == $v["id"]) {
                $found = TRUE;
                if(empty($_SESSION["cart_item"][$k]["cantidad"])) {
                    $_SESSION["cart_item"][$k]["cantidad"] = 0;
                }
                $_SESSION["cart_item"][$k]["cantidad"] += $cantidad;
            }
        }
        if (!$found){
            $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
        }
    } else {
        $_SESSION["cart_item"] = $itemArray;
    }


}

if(isset($_POST["fecha"]) && !empty($_POST["fecha"])){
    
    $postfecha = empty($_POST["fecha"]) ? "" : $_POST["fecha"];
    $postcliente = empty($_POST["cliente"]) ? "" : $_POST["cliente"];
    $postmedioPago = empty($_POST["medioPago"]) ? "" : $_POST["medioPago"];
    $postcanal = empty($_POST["canal"]) ? "" : $_POST["canal"];
    $postsorteo = empty($_POST["sorteo"]) ? false : $_POST["sorteo"];

    $itemArray = array('fecha'=>$postfecha, 'cliente'=>$postcliente, 'medioPago'=>$postmedioPago, 'canal'=>$postcanal, 'sorteo'=>$postsorteo);
    $_SESSION["cart_header"] = $itemArray;

    header('location: sale.php');
    exit();
} 

// Include config file
//require_once "../server.php";

$cliente = 1; // generico cliente
$medioPago = "";
$fecha = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d');
$grand_total = 0;

// chequeo si la session esta inicializada.
if(!empty($_SESSION["cart_header"])) {
    if(isset($_SESSION["cart_header"]['fecha']) and $_SESSION["cart_header"]['fecha']!=""){
        $fecha = $_SESSION["cart_header"]['fecha'];
    } 
    if(isset($_SESSION["cart_header"]['cliente']) and $_SESSION["cart_header"]['cliente']!=""){
        $cliente = $_SESSION["cart_header"]['cliente'];
    }
    
    if(isset($_SESSION["cart_header"]['medioPago']) and $_SESSION["cart_header"]['medioPago']!=""){
        $medioPago = $_SESSION["cart_header"]['medioPago'];
    } 

    if(isset($_SESSION["cart_header"]['canal']) and $_SESSION["cart_header"]['canal']!=""){
        $canal = $_SESSION["cart_header"]['canal'];
    } 

    if(isset($_SESSION["cart_header"]['sorteo']) and $_SESSION["cart_header"]['sorteo']!=""){
        $sorteo = $_SESSION["cart_header"]['sorteo'];
    } 

} else {
    $canal = $_SESSION["user.canal"];
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
        <title>POS</title>
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
                    "order": [[ 0, 'asc' ]],
                    "lengthMenu": [[50, 75, 100, 150, -1], [50, 75, 100, 150, "All"]]
                });

                $('.selectpicker').selectpicker();
            });


        </script>

    </head>

    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>
        
        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>

                    <!-- Begin Page Content -->
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">POS</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="/index.php">POS</a></li>
                        </ol>
                        <!-- Content Row -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                    <form id="formFactura" method="post">

                                        <input type="hidden" name="canal" value="<?php echo $canal; ?>">
                                        <!-- CLIENTE --> 
                                        <div id="container-cliente">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                            </div>
                                            <div  class="form-group">
                                                <label>Cliente</label>
                                                <select name="cliente" id="cliente" class="form-control" data-live-search="true" data-size="10" required="required">
                                                    <option value="">Seleccione</option>
                                                    <?php
                                                        $clientePDO = new Cliente();
                                                        $clientes = $clientePDO->getAllActiveForSales("apellido, nombre ASC");
                                                        foreach ($clientes as $val) {
                                                        ?>
                                                        <option 
                                                            value="<?php echo $val['id']?>" 
                                                            data-subtext="(<?php echo $val['id']?>)"
                                                            <?php if($val['id']==$cliente) echo 'selected="selected"'; ?>
                                                            >
                                                            <?php echo mb_strtoupper($val['apellido'] . ' '.$val['nombre'],'UTF-8')?>
                                                        </option>
                                                        <?php 
                                                            }
                                                            unset($clientePDO);
                                                        ?>
                                                </select>
                                            </div>
                                        
                                        <br>                    
                                        <!-- PRODUCTOS --> 
                                        <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                    <th>
                                                        <a href="action-cart.php?action=empty&forwardOk=pos.php" onclick="return confirm('Seguro que quiere limpiar el carrito?');"><i class="fas fa-broom"></i></a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                if(isset($_SESSION["cart_item"])){
                                                    $total_quantity = 0;
                                                    $total_price = 0;
                                                    foreach ($_SESSION["cart_item"] as $item){
                                                        $item_price = $item["cantidad"] * $item["precio"];
                                            ?>
                                                <tr>
                                                    <td><?= $item["sku"]; ?></td>
                                                    <input type="hidden" class="pid" value="<?= $item["id"]; ?>">
                                                    <td><?= $item["titulo"]; ?></td>
                                                    <td>
                                                    <i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item["precio"],2); ?>
                                                    </td>
                                                    <input type="hidden" class="pprice" value="<?= $item["precio"] ?>">
                                                    <td><?= $item["cantidad"] ?></td>
                                                    <td><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_price,2); ?></td>
                                                    <td>
                                                    <a href="action-cart.php?action=remove&id=<?= $item['id'] ?>&forwardOk=pos.php" class="text-danger lead" onclick="return confirm('Are you sure want to remove this item?');"><i class="fas fa-trash-alt"></i></a>
                                                    </td>
                                                </tr>
                                            <?php 
                                                        $grand_total += $item_price; 
                                                    } 
                                                }
                                            ?>
                                                <tr>
                                                    <td colspan="4"><b>Total Venta</b></td>
                                                    <td><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                        <BR>        
                                        <!-- FORMA DE PAGO -->

                                        <div class="form-group">
                                            <label>Medio de Pago</label>
                                            <select name="medioPago" id="medioPago" class="form-control" data-live-search="true" data-size="10" required="required">
                                                <option value="">Seleccione</option>
                                                <?php
                                                    $medioPagoPDO = new MedioPago();
                                                    //$mediosPago = $medioPagoPDO->getAllByCanal($canal, "nombre ASC");
                                                    $mediosPago = $medioPagoPDO->getAll("nombre ASC");
                                                    foreach ($mediosPago as $val) {
                                                    ?>
                                                    <option 
                                                        value="<?php echo $val['id']?>" 
                                                        data-subtext="(<?php echo $val['id']?>)"
                                                        <?php if($val['id']==$medioPago) echo 'selected="selected"'; ?>
                                                        >
                                                        <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                                    </option>
                                                    <?php 
                                                        }
                                                        unset($medioPagoPDO);
                                                    ?>
                                            </select>
                                        </div>
                                        <BR>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary <?= ($grand_total > 0) ? '' : 'disabled'; ?>"><i class="far fa-credit-card"></i>&nbsp;&nbsp;Pagar</button>
                                        </div>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>                        

                            <div class="col-lg-6 mb-4">

                                <div class="card shadow mb-4">
                                    <div class="card-body">

                                        <form id="formProductos" method="post" onSubmit="return false;">
                                            <div class="table-responsive">            
                                            <table class="table table-striped table-bordered" id="tbProductos">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">ID</th>
                                                        <th class="text-center">Titulo</th>
                                                        <th class="text-center">Precio</th>
                                                        <th class="text-center">Vender</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php 
                                                    $productoPDO = new Producto();
                                                    $productos = $productoPDO->getAllActive();
                                                    foreach ($productos as $row) {

                                                        $colId = $row['id'];
                                                        $colSku = $row['sku'];
                                                        $colTitulo = $row['titulo'];
                                                        $colPrecio = $row['precio'];
                                                        $colPrecioOferta = isset($row['precio_oferta']) ? $row['precio_oferta'] : 0;
                                                        $colPrecioOfertaAplicada = 0;
                                                        $colEditorial = $row['editorial'];
                                                        $colNuevo = $row['nuevo'];
                                                        $colCosto = $row['precio_costo'];
                                                        $colAdmiteDescuento = $row['admite_descuento'];
                                                    
                                                ?>

                                                    <tr>
                                                        <td><?php echo $colSku; ?></td>
                                                        <td><?php echo $colTitulo. ' (' . $colEditorial.')' . ($colNuevo==TRUE ? '' : ' - Usado'); ?></td>
                                                        <td>
                                                            <?php if ((float)$colPrecioOferta > 0) { ?>
                                                                <span class="text-muted"><del><?= number_format($colPrecio, 2); ?></del></span>
                                                                <br>
                                                                <span class="text-success font-weight-bold"><?= number_format($colPrecioOferta, 2); ?></span>
                                                            <?php } else { ?>
                                                                <?= number_format($colPrecio, 2); ?>
                                                            <?php } ?>
                                                        </td>
                                                        <td align="center">
                                                            <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#addModal" data-id="<?php echo $colId;?>" data-sku="<?php echo $colSku;?>" data-producto="<?php echo $colTitulo;?>" data-precio="<?php echo $colPrecio;?>" data-precio-actual="<?php echo $colPrecio;?>" data-precio-oferta="<?php echo $colPrecioOferta;?>" data-oferta-aplicada="<?php echo $colPrecioOfertaAplicada;?>" data-descuento="<?php echo $colAdmiteDescuento;?>" data-costo="<?php echo  $colCosto;?>"><i class="fa fa-fw fa-cart-plus"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    }
                                                    unset($productoPDO);
                                                ?>
                                                </tbody>
                                            </table>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>                        
                        </div>
                    </div>        
                </main>
                <?php include '../footer.php';?>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        <?php include 'addModal.php'; ?>
    </body>


    <script>
        $('#addModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var sku = applicant.data('sku');
            var producto = applicant.data('producto');
            var precio = applicant.data('precio');
            var precioActual = applicant.data('precio-actual');
            var precioOferta = applicant.data('precio-oferta');
            var admiteDescuento = applicant.data('descuento');
            var costo = applicant.data('costo');
            var precioVenta = parseFloat(precioOferta) > 0 ? precioOferta : precio;
            var ofertaAplicada = parseFloat(precioOferta) > 0 ? '1' : '0';
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="sku"]').val(sku);
            modal.find('input[name="producto"]').val(producto);
            modal.find('input[name="precioLista"]').val(precio);
            modal.find('input[name="precioVenta"]').val(precioVenta);
            modal.find('input[name="precioActual"]').val(precioActual);
            modal.find('input[name="precioOfertaAplicada"]').val(ofertaAplicada);
            modal.find('input[name="precioOferta"]').val(precioOferta);
            if (parseFloat(precioOferta) > 0) {
                modal.find('input[name="precioOfertaDisplay"]').val(precioOferta);
                modal.find('#precioOfertaContainer').show();
            } else {
                modal.find('input[name="precioOfertaDisplay"]').val('');
                modal.find('#precioOfertaContainer').hide();
            }
            modal.find('input[name="costo"]').val(costo);
            //modal.find('input[name="admiteDescuentoActual"]').val(admiteDescuento);
            //modal.find('select[name="admiteDescuento"]').val(admiteDescuento).attr('selected', true);

        });
    </script> 

</html>
