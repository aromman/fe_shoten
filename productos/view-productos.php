<?php
// Include config file
require_once "../app/models/producto.php";
 
// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){
    
        // Get URL parameter
    $id =  trim($_GET["id"]);
    
    $productoPDO = new Producto();
    $result = $productoPDO->getById($id);
    $rsKardek = $productoPDO->getKardek($id);
    
}  else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
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
        <title>Productos</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <!--Producto -->    
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Productos</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Productos
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sku</th>
                                            <th class="text-center">Titulo</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Editorial</th>
                                            <th class="text-center">Metodo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (!is_null($result)) {
                                                $row = $result;

                                                $colSku = $row['sku'];
                                                $colTitulo = $row['titulo'];
                                                $colStock = $row['stock'];
                                                $colPrecio = $row['precio'];;
                                                $colEditorial = $row['editorial'];
                                            
                                        ?>

                                                <tr>
                                                    <td><?php echo $colSku; ?></td>
                                                    <td><?php echo $colTitulo; ?></td>
                                                    <td><?php echo $colStock; ?></td>
                                                    <td><?php echo $colPrecio; ?></td>
                                                    <td><?php echo $colEditorial; ?></td>
                                                    <td>PEPS</td>

                                                </tr>

                                        <?php
                                            }
                                            else
                                            {
                                            echo '0 results';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>

                        <!-- Ventas -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Kardek
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center" rowspan = "2">Fecha</th>
                                            <th class="text-center" rowspan = "2">Detalle</th>
                                            <th class="text-center" colspan = "3">Entrada</th>
                                            <th class="text-center" colspan = "3">Salida</th>
                                            <th class="text-center" colspan = "3">Saldo</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Valor Unitario</th>
                                            <th class="text-center">Valor Total</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Valor Unitario</th>
                                            <th class="text-center">Valor Total</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Valor Unitario</th>
                                            <th class="text-center">Valor Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (!is_null($rsKardek)){
                                           

                                                $colSaldoUnidades= $colSaldoPrecio= $colSaldoTotal = 0;

                                                foreach ($rsKardek as $row) {
                                                
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));

                                                    $colComprasUnidades = $colComprasPrecio = $colComprasTotal = "";
                                                    $colVentasUnidades= $colVentasPrecio= $colVentasTotal = "";

                                                    if ($row['tipo_movimiento'] == "v") {
                                                        $colDetalle = "Ventas";
                                                        $colVentasUnidades = number_format($row['unidades'], 0, ".", ""); 
                                                        $colVentasPrecio = number_format($row['precio'], 2, ".", ""); 
                                                        $colVentasTotal = number_format($colVentasUnidades * $colVentasPrecio, 2, ".", ""); 
                                                        $colSaldoUnidades = $colSaldoUnidades - $colVentasUnidades;
                                                        $colSaldoPrecio = $colVentasPrecio;
                                                        

                                                    } else {
                                                        $colDetalle = "Compras";
                                                        $colComprasUnidades = number_format($row['unidades'], 0, ".", ""); 
                                                        $colComprasPrecio = number_format($row['precio'], 2, ".", ""); 
                                                        $colComprasTotal = number_format($colComprasUnidades * $colComprasPrecio, 2, ".", ""); 

                                                        $colSaldoUnidades = $colSaldoUnidades + $colComprasUnidades;
                                                        $colSaldoPrecio = $colComprasPrecio;
                                                    }
                                                    $colSaldoTotal = number_format($colSaldoUnidades * $colSaldoPrecio, 2, ".", ""); ;
                                            
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colDetalle;?></td>
                                                    <td align="right"><?php echo $colComprasUnidades; ?></td>
                                                    <td align="right"><?php echo $colComprasPrecio; ?></td>
                                                    <td align="right"><?php echo $colComprasTotal; ?></td>
                                                    <td align="right"><?php echo $colVentasUnidades; ?></td>
                                                    <td align="right"><?php echo $colVentasPrecio; ?></td>
                                                    <td align="right"><?php echo $colVentasTotal; ?></td>
                                                    <td align="right"><?php echo $colSaldoUnidades; ?></td>
                                                    <td align="right"><?php echo $colSaldoPrecio; ?></td>
                                                    <td align="right"><?php echo $colSaldoTotal; ?></td>
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
                                </div>
                            </div>
                        </div>

                        <form id="formViewProductos" method="post" onSubmit="return false;">
                            <a href="<?php echo $_REQUEST['forwardOk'];?>" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-plus-circle"></i>Volver</a>
                        </form>

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
