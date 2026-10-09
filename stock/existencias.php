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

$productoPDO = new Producto();

$id_canal = $_SESSION["user.canal"];

// productos agrupados por serie
$result = $productoPDO->getAllActiveStockNotValidate("serie, titulo");

// total productos
$cantidadTotalProductos = $productoPDO->getTotalCountStockNotValidate();

// por serie
$rsProductosPorSerie = $productoPDO->getStockNotValidateGroupBySerie();

// por editorial
$rsProductosPorEditorial = $productoPDO->getStockNotValidateGroupByEditorial();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Existencias</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();
                
                $('body').on('mousemove',function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });
                
                $("#addmoreproductos").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-productos.ajax.php',
                        data:{'action':'addDataRowProductos'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveProductos').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formProductos").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-productos.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveProductos').addClass("disabled");
                        }
                    });
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
                        <h1 class="mt-4">Control Existencias</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>

                        <div class="row">

                        <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-header">Control Existencias al <?php echo date('d/m/Y', strtotime($fechaCaja));?></div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            Cantidad Productos : <?php echo number_format($cantidadTotalProductos, 0, ".", ""); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-header">Existencias por Serie</div>
                                    <div class="list-group list-group-flush">

                                        <?php 
                                            $cantidadProductos = 0;
                                            if (!is_null($rsProductosPorSerie)) {

                                                foreach ($rsProductosPorSerie as $row){

                                                    $nombre = $row['serie'];
                                                    $cantidad = $row['stock'];
                                                    $cantidadProductos += $cantidad;
                                                    $textoIngreso = $nombre . ": " . number_format($cantidad, 0, ".", "");

                                            
                                        ?>
                                            <a href="#" class="list-group-item list-group-item-action"><?php echo $textoIngreso; ?></a>

                                        <?php
                                                }
                                            }
                                        ?>

                                    </div>
                                    <div class="card-footer">
                                        <p class="card-text">Cantidad Total : <?php echo number_format($cantidadProductos, 0, ".", "");?></p>
                                    </div>                                    
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-header">Existencias por Editorial</div>
                                    <div class="list-group list-group-flush">
                                    <?php 
                                            $cantidadProductos = 0;
                                            if (!is_null($rsProductosPorEditorial)) {

                                                foreach ($rsProductosPorEditorial as $row){

                                                    $nombre = $row['editorial'];
                                                    $cantidad = $row['stock'];
                                                    $cantidadProductos += $cantidad;
                                                    $textoIngreso = $nombre . ": " . number_format($cantidad, 0, ".", "");

                                            
                                        ?>
                                            <a href="#" class="list-group-item list-group-item-action"><?php echo $textoIngreso; ?></a>

                                        <?php
                                                }
                                            }
                                        ?>


                                    </div>
                                    <div class="card-footer">
                                        <p class="card-text">Cantidad Total : <?php echo number_format($cantidadProductos, 0, ".", "");?></p>
                                    </div>                                    
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="row">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Existencias por Serie
                                </div>
                                <div class="card-body">
                                    <form id="formProductos" method="post" onSubmit="return false;">
                                    <input type="hidden" name="action" value="saveProductosAddMore">
                                    <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tb">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Id</th>
                                                <th class="text-center">Sku</th>
                                                <th class="text-center">Titulo</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-center">Editar</th>
                                                <th class="text-center">Validar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                if (!is_null($result) ) {
                                                    $serieAnt = "";
                                                    $idSerieAnt = 0;
                                                    $totalSerie = 0;
                                                    
                                                    foreach($result as $row){

                                                        // recupero datos 
                                                        $colSku = $row['sku'];
                                                        $colTitulo = $row['titulo'];

                                                        $colPrecio = number_format($row['precio'], 2, ".", "");

                                                        $colStock = number_format($row['stock'], 0, ".", "");
                                                        $colCanal = $row['id_canal'];
                                                        $colSerie = $row['serie'];
                                                        $colIdSerie= $row['serieId'];

                                                        if (!empty($serieAnt) and $serieAnt!=$colSerie){
                                                            // corte control
                                                            ?>
                                                            <tr class="bg-info">
                                                                <td colspan="3" align="right"><b>Total :  <?php echo $serieAnt; ?></b></td>
                                                                <td align="right"><b><?php echo number_format($totalSerie, 0, ".", ""); ?></b></td>
                                                                <td colspan="4"></td>
                                                            </tr>
    
                                                            <?php     
                                                                $totalSerie = 0;
                                                            } 
                                                            // totalizo
                                                            $serieAnt = $colSerie;
                                                            $idSerieAnt = $colIdSerie;
                                                            $totalSerie += $colStock;

                                                    
                                                ?>
                                                    <tr>
                                                        <td><?php echo $row['id'];?></td>
                                                        <td><?php echo $colSku; ?></td>
                                                        <td><?php echo $colTitulo; ?></td>
                                                        <td align="right"><?php echo $colStock; ?></td>
                                                        <td align="right"><?php echo $colPrecio; ?></td>

                                                        <td align="center">
                                                            <a href="update-productos-stock.php?forwardOk=existencias.php&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        </td>                            
                                                        <td align="center">
                                                            <a href="validate-productos.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                        </td>                            
                                                    </tr>

                                            <?php
                                                    }

                                                    // final 
                                                    ?>
                                                    <tr class="bg-info">
                                                        <td colspan="3" align="right"><b>Total :  <?php echo $serieAnt; ?></b></td>
                                                        <td align="right"><b><?php echo number_format($totalSerie, 0, ".", ""); ?></b></td>
                                                        <td colspan="4"></td>
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
                                    </form>
                                </div>
                            </div>
                        </div>   

                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>