<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/producto.php";

$fechaCaja = date("Y-m-d"); 
$id_canal = $_SESSION["user.canal"];

$porcentajePresupuestoSobreTotalVentas = 60;
$utilidadSobreGastosMinima = 10;
$utilidadSobreGastosMedia = 15;
$utilidadSobreGastosIdeal = 30;

$classEstado = array(1=>"table-light",2=>"table-info",3=>"table-warning",4=>"table-danger");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Analisis de Precios</title>
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
        $(document).ready(function(){
            $('input').keyup(function() {

                var precioCompra = $("#precioCompra").val();
                var costoVariable = $("#costoVariable").val();

                var costoTotal = 0;
                var precioMinimo = 0;
                var precioMedio = 0;
                var precioIdeal = 0;
                var costoFijo = precioCompra * (<?php echo $porcentajePresupuestoSobreTotalVentas; ?> / 100);
                
                costoTotal = Number(precioCompra) + Number(costoVariable) + Number(costoFijo);
                precioMinimo = costoTotal / (1 - (<?php echo $utilidadSobreGastosMinima; ?> / 100));
                precioMedio = costoTotal / (1 - (<?php echo $utilidadSobreGastosMedia; ?> / 100));
                precioIdeal = costoTotal / (1 - (<?php echo $utilidadSobreGastosIdeal; ?> / 100));
                
                $("#costoFijo").val(Number.parseFloat(costoFijo).toFixed(2));
                $("#costoTotal").val(Number.parseFloat(costoTotal).toFixed(2));
                $("#precioMinimo").val(Number.parseFloat(precioMinimo).toFixed(2));
                $("#precioMedio").val(Number.parseFloat(precioMedio).toFixed(2));
                $("#precioIdeal").val(Number.parseFloat(precioIdeal).toFixed(2));

            });

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
                        <h1 class="mt-4">Analisis de Precios</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Mes Actual</li>
                        </ol>

                        <div class="row">

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-header">Analisis</div>
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action">Costo Fijo : <?php echo number_format($porcentajePresupuestoSobreTotalVentas, 0, ".", "");?> %</a>
                                        <a href="#" class="list-group-item list-group-item-action">Ganancia Esperada Ideal:  <?php echo number_format($utilidadSobreGastosIdeal, 0, ".", ""); ?> %</a>
                                        <a href="#" class="list-group-item list-group-item-action">Ganancia Esperada Media:  <?php echo number_format($utilidadSobreGastosMedia, 0, ".", ""); ?> %</a>
                                        <a href="#" class="list-group-item list-group-item-action">Ganancia Esperada Minina: <?php echo number_format($utilidadSobreGastosMinima, 0, ".", ""); ?> %</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row justify-content-end">                                    
                                        <div class="col-auto">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#precioModal">
                                                    <i class="fa-solid fa-circle-plus"></i> Calcular Precio
                                                </a>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Mas Vendidos Ultimos 30 Dias
                            </div>
                            <div class="card-body">
                                <form id="formProductos" method="post" onSubmit="return false;">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered table-responsive" id="tbMasVendidos">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-center">Producto</th>
                                            <th rowspan="2" class="text-center">Precio</th>
                                            <th colspan="3" class="text-center">Costo</th>
                                            <th colspan="3"  class="text-center">Precios</th>
                                            <th rowspan="2"  class="text-center">Acciones</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Variable</th>
                                            <th class="text-center">Fijo <?php echo $porcentajePresupuestoSobreTotalVentas?> %</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Minimo</th>
                                            <th class="text-center">Medio</th>
                                            <th class="text-center">Ideal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 

                                            $productoPDO = new Producto();
                                            $productos =  $productoPDO->getTotalSaleByDays(30); // ultimos 30 dias
                                            if (!is_null($productos)){
                                                foreach ($productos as $row) {

                                                                                                    $className = '';
                                                    $prod = $row['id'];
             
                                                    $colTitulo = $row['titulo'];
                                                    $colPrecio = number_format($row['precio'], 0, ".", "");
                                                    $colCosto = number_format($row['precio_costo'], 2, ".", "");

                                                    $costoBase = $colPrecio * 0.5; // 50% del precio

                                                    $colCostoFijo = number_format( $colCosto * ($porcentajePresupuestoSobreTotalVentas / 100) , 2, ".", "");
                                                    $colCostoTotal = number_format($colCosto+$colCostoFijo, 2, ".", "");

                                                    $colPrecioMinimo = number_format($colCostoTotal / (1 - ($utilidadSobreGastosMinima / 100)), 0, ".", "");
                                                    $colPrecioMedio = number_format($colCostoTotal / (1 - ($utilidadSobreGastosMedia / 100)), 0, ".", "");
                                                    $colPrecioIdeal = number_format($colCostoTotal / (1 - ($utilidadSobreGastosIdeal / 100)), 0, ".", "");

                                                    $colAccion = "";

                                                    if ($colPrecio<$colPrecioMinimo){
                                                        $colAccion = "Subir Precio";
                                                        $className = 'table-danger';
                                                    } else if ($colPrecio>$colPrecioIdeal){
                                                        $colAccion = "Bajar Precio";
                                                        $className = 'table-warning';
                                                    } else if ($colCosto < $costoBase ){
                                                        $colAccion = "Revisar Costos";
                                                        $className = 'table-warning';
                                                    } 

                                                    ?>
                                                    <tr class="<?php echo $className; ?>">
                                                        <td><?php echo $colTitulo; ?></td>
                                                        <td align="right"><?php echo $colPrecio; ?></td>
                                                        <td align="right"><?php echo $colCosto; ?></td>
                                                        <td align="right"><?php echo $colCostoFijo; ?></td>
                                                        <td align="right"><?php echo $colCostoTotal; ?></td>
                                                        <td align="right"><?php echo $colPrecioMinimo; ?></td>
                                                        <td align="right"><?php echo $colPrecioMedio; ?></td>
                                                        <td align="right"><?php echo $colPrecioIdeal; ?></td>
                                                        <td align="center"><?php echo $colAccion; ?></td>
                                                    </tr>
                                        <?php 
                                                }
                                            } else {
                                                echo '0 results';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                </div>
                                </form>
                            </div>
                        </div>
                        
                        </div>

                    </div>
                </main>

                <?php include_once '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        <?php include_once 'precioModal.php'; ?>

        <script>
        $('#precioModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var modal = $(this);
        });
        </script>


    </body>
</html>