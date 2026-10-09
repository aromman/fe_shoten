<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/productoFormato.php";
require_once "../app/models/ventas.php";
require_once "../app/models/producto.php";

$productoFormatoPDO = new ProductoFormato();
$ventasPDO = new Ventas();
$productoPDO = new Producto();

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""
    ){


        $id_formato =  trim($_GET["id"]);

        $rsProductoFormato = $productoFormatoPDO->getById($id_formato);

        $nombre = $rsProductoFormato["nombre"];
        $objetivo = $rsProductoFormato["monto_objetivo"];

        $rsVentas = $ventasPDO->getAllMonthByFormato($id_formato);

        $rsProductos = $productoPDO->getAllActiveByFormato($id_formato);
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
        <title>Ver Productos Formato</title>
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
                        <h1 class="mt-4">Productos Formato</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-formato.php">Formato</a></li>
                            <li class="breadcrumb-item active">Productos Formato</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <div  class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombreSerie" value="<?php echo $nombre;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Objetivo</label>
                                    <input type="number" step="0" name="cupoMaximo" value="<?php echo $objetivo;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div class="container-fluid px-4">
                                    <div class="table-responsive mt-2">
                                        <table class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr><th colspan="10">Ventas Mensuales</th></tr>
                                                <tr>
                                                    <th class="text-center">Fecha</th>
                                                    <th class="text-center">Canal</th>
                                                    <th class="text-center">Producto</th>
                                                    <th class="text-center">Unidades</th>
                                                    <th class="text-center">P.U</th>
                                                    <th class="text-center">Sub Total</th>
                                                    <th class="text-center">Descuento</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Costo</th>
                                                    <th class="text-center">Ganancia</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $cantidad_items = $grand_total = $grand_costo = $grand_ganancia = 0;
                                                if (!is_null($rsVentas)) {
                                                    foreach ($rsVentas as $row) {

                                                        $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                        $colCanal = $row['canal'];
                                                        $colProducto = $row['producto'];
                                                        $colUnidades = number_format($row['unidades'], 0, ".", ""); 
                                                        $colPrecioUnitario = number_format($row['precio_unitario'], 2, ".", ""); 
                                                        $colSubTotal = number_format($colUnidades * $colPrecioUnitario, 2, ".", ""); 
                                                        $colDescuento = number_format($row['descuento'], 2, ".", ""); 
                                                        $colTotal = number_format($row['total'], 2, ".", ""); 
                                                        $colCosto = number_format($colUnidades * $row['costo'], 2, ".", ""); 
                                                        $colGanancia = number_format($colTotal - $colCosto, 2, ".", ""); 


                                                        $cantidad_items += $colUnidades;
                                                        $grand_total += $colTotal;
                                                        $grand_costo += $colCosto;
                                                        $grand_ganancia +=$colGanancia;
                                            ?>
                                            <tr>
                                                <td align="center"><?php echo $colFecha; ?></td>
                                                <td align="left"><?php echo $colCanal;?></td>
                                                <td align="left"><?php echo $colProducto;?></td>
                                                <td align="right"><?php echo $colUnidades; ?></td>
                                                <td align="right"><?php echo $colPrecioUnitario; ?></td>
                                                <td align="right"><?php echo $colSubTotal; ?></td>
                                                <td align="right"><?php echo $colDescuento; ?></td>
                                                <td align="right"><?php echo $colTotal; ?></td>
                                                <td align="right"><?php echo $colCosto; ?></td>
                                                <td align="right"><?php echo $colGanancia; ?></td>
                                            </tr>
                                            <?php 
                                                   } 
                                                }
                                            ?>
                                            </tbody>
                                            <tfooter>
                                            <tr>
                                                <td colspan="3"><b>Cantidad Items</b></td>
                                                <td align="right"><?= $cantidad_items ?></td>
                                                <td colspan="3"><b>Montos Totales</b></td>
                                                <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                                <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_costo,2); ?></b></td>
                                                <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_ganancia,2); ?></b></td>
                                            </tr>
                                            </tfooter>
                                        </table>
                                    </div>
                                </div>
                                <br> 
                                <div class="container-fluid px-4">
                                    <div class="table-responsive mt-2">
                                        <table class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr><th colspan="6">Productos Disponibles</th></tr>
                                                <tr>
                                                    <th class="text-center">Sku</th>
                                                    <th class="text-center">Titulo</th>
                                                    <th class="text-center">Stock</th>
                                                    <th class="text-center">Precio</th>
                                                    <th class="text-center">Serie</th>

                                                    <th class="text-center">Valorizado</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $cantidad_items = $grand_total = 0;
                                                if (!is_null($rsProductos)) {
                                                    foreach ($rsProductos as $row) {

                                                        $colSku = $row['sku'];
                                                        $colTitulo = $row['titulo'];
                                                        $colStock = ($row['stock']>0) ? $row['stock'] : 'SIN STOCK';
                                                        $colPrecio = ($colStock > 0 ) ?  number_format($row['precio'], 2, ".", "") : '';
                                                        $colEditorial = $row['editorial'];
                                                        $colEsNuevo = $row['nuevo'];
                                                        $colSerie = $row['serie'];
                                                        $colValorizado = number_format($colStock * $colPrecio, 2, ".", "");
    
                                                        $className = ($colStock > 0 ) ? '' : 'table-danger';
    
                                                        $cantidad_items += $colStock;
                                                        $grand_total += $colValorizado;
                                            ?>
                                            <tr class="<?php echo $className; ?>">
                                                <td><?php echo $colSku; ?></td>
                                                <td><?php echo $colTitulo. ' (' . $colEditorial.')' . ($colEsNuevo==TRUE ? '' : ' - Usado'); ?></td>
                                                <td><?php echo $colStock; ?></td>
                                                <td><?php echo $colPrecio; ?></td>
                                                <td><?php echo $colSerie; ?></td>
                                                <td><?php echo $colValorizado; ?></td>        
                                            </tr>
                                            <?php 
                                                   } 
                                                }
                                            ?>
                                            </tbody>
                                            <tfooter>
                                            <tr>
                                                <td colspan="2"><b>Cantidad Items</b></td>
                                                <td align="right"><?= $cantidad_items ?></td>
                                                <td colspan="2"><b>Montos Totales</b></td>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
