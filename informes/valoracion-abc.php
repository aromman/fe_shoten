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


$classEstado = array("A"=>"table-danger","B"=>"table-warning","C"=>"table-success");
$leyendaEstado = array("A"=>"COMPRA YA!!","B"=>"IDEAL COMPRAR","C"=>"NO COMPRAR");

$total_inventario = 0;

$sql = "select 
sum(p.precio * p.stock) total,
sum(p.precio) precio
from productos p, editoriales e
where p.stock > 0
and p.id_editorial = e.id
and e.consignacion = false;";

$result = mysqli_query($link,$sql);
if (mysqli_num_rows($result) > 0) {

    $row = mysqli_fetch_assoc($result);
    $total_inventario = $row['total'];
    $total_precio = $row['precio'];
}        

$sql = "select 
p.sku,
p.titulo,
p.stock,
p.precio,
(p.precio * p.stock) inversion
from productos p, editoriales e
where p.stock > 0
and p.id_editorial = e.id
and e.consignacion = false
order by 5 desc, 2 asc"; 

$result = mysqli_query($link,$sql);

$sql = "select 
p.sku,
p.titulo,
p.precio
from productos p, editoriales e
where p.stock > 0
and p.id_editorial = e.id
and e.consignacion = false
order by 3 desc, 2 asc"; 

$rsPrecio = mysqli_query($link,$sql);

$sql = "select
p.sku,
p.titulo,
p.precio_costo,
p.stock,
((sum(v.unidades) / month(CURRENT_DATE()) ) * 12 ) demanda
from ventas v, productos p, editoriales e
where v.id_producto = p.id
and p.id_editorial = e.id
and e.consignacion = false
and YEAR(v.fecha) = YEAR(CURRENT_DATE())
group by 1,2,3,4
order by 5 desc"; 

$rsOEQ = mysqli_query($link,$sql);


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Valoracion Inventario</title>
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

                $('#tbStock').dataTable( {
                    "order": [[ 5, 'asc' ]]
                });

                $('#tbStockPrecio').dataTable( {
                    "order": [[ 5, 'asc' ]]
                });

                $('#tbStockOEQ').dataTable( {
                    "order": [[ 3, 'desc' ]]
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
                        <h1 class="mt-4">Valoracion Inventario</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                ABC Por Inversion
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">

                                <table class="table table-striped table-bordered" id="tbStock">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sku</th>
                                            <th class="text-center">Titulo</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Inversion</th>
                                            <th class="text-center">Inversion Acumulada</th>
                                            <th class="text-center">Porcentaje Inversion Acumulada</th>
                                            <th class="text-center">Zona</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                $inversionAcumulada = 0;

                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $colSku = $row['sku'];
                                                    $colTitulo = $row['titulo'];
                                                    $colStock = number_format($row['stock'], 0, ".", ""); 
                                                    $colPrecio = number_format($row['precio'], 2, ".", ""); 
                                                    $colInversion = number_format($row['inversion'], 0, ".", ""); 

                                                    $inversionAcumulada +=$colInversion;
                                                    
                                                    $porcentajeInversion = number_format(($inversionAcumulada * 100 ) / $total_inventario, 5, ".", ""); 

                                                    $zona = $porcentajeInversion <= 80 ? "A" : ($porcentajeInversion <= 95 ? "B" : "C");

                                                    $className = $classEstado[$zona];
                                                    
                                            
                                        ?>

                                                <tr>
                                                    <td><?php echo $colSku; ?></td>
                                                    <td><?php echo $colTitulo; ?></td>
                                                    <td align="right"><?php echo $colStock; ?></td>
                                                    <td align="right"><?php echo $colPrecio; ?></td>
                                                    <td align="right"><?php echo $colInversion; ?></td>
                                                    <td align="right"><?php echo $inversionAcumulada; ?></td>
                                                    <td align="right"><?php echo $porcentajeInversion; ?></td>
                                                    <td class="<?php echo $className;?>" align="center"><?php echo $zona; ?></td>
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
                                </form>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                ABC Por Precio
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">

                                <table class="table table-striped table-bordered" id="tbStockPrecio">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sku</th>
                                            <th class="text-center">Titulo</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Acumulada</th>
                                            <th class="text-center">Porcentaje Acumulada</th>
                                            <th class="text-center">Zona</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($rsPrecio) > 0) {

                                                $inversionAcumulada = 0;

                                                while($row = mysqli_fetch_assoc($rsPrecio))
                                                {
                                                    $colSku = $row['sku'];
                                                    $colTitulo = $row['titulo'];
                                                    $colStock = number_format($row['stock'], 0, ".", ""); 
                                                    $colPrecio = number_format($row['precio'], 2, ".", ""); 
               
                                                    $inversionAcumulada +=$colPrecio;
                                                    
                                                    $porcentajeInversion = number_format(($inversionAcumulada * 100 ) / $total_precio, 5, ".", ""); 

                                                    $zona = $porcentajeInversion <= 80 ? "A" : ($porcentajeInversion <= 95 ? "B" : "C");

                                                    $className = $classEstado[$zona];
                                                    
                                            
                                        ?>

                                                <tr>
                                                    <td><?php echo $colSku; ?></td>
                                                    <td><?php echo $colTitulo; ?></td>
                                                    <td align="right"><?php echo $colPrecio; ?></td>
                                                    <td align="right"><?php echo $inversionAcumulada; ?></td>
                                                    <td align="right"><?php echo $porcentajeInversion; ?></td>
                                                    <td class="<?php echo $className;?>" align="center"><?php echo $zona; ?></td>
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
                                </form>
                            </div>
                        </div>     
                                   
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                OEQ
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">

                                <table class="table table-striped table-bordered" id="tbStockOEQ">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-center">Sku</th>
                                            <th rowspan="2"  class="text-center">Titulo</th>
                                            <th rowspan="2"  class="text-center">Stock</th>
                                            <th rowspan="2"  class="text-center">Demanda Anual</th>

                                            <th class="text-center">CPP</th>
                                            <th class="text-center">CMP</th>
                                            <th class="text-center">OEQ</th>

                                            <th rowspan="2"  class="text-center">Accion</th>
                                            <th rowspan="2"  class="text-center">Pedidos a Realizar</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Costo Promedio por Pedido</th>
                                            <th class="text-center">Costo Mantenimiento por Unidad</th>
                                            <th class="text-center">Cantidad Economica por Pedido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($rsOEQ) > 0) {

                                                $inversionAcumulada = 0;

                                                while($row = mysqli_fetch_assoc($rsOEQ))
                                                {
                                                    $colSku = $row['sku'];
                                                    $colTitulo = $row['titulo'];
                                                    $colStock = number_format($row['stock'], 0, ".", ""); 
                                                    $colDemanda = number_format($row['demanda'], 0, ".", ""); 
                                                    $colCPP = number_format($row['precio_costo'], 2, ".", ""); 
                                                    $colCMP = number_format($colCPP * 0.10, 2, ".", ""); 
                                                    
                                                    if ($colCMP>0){
                                                        $colOEQ = ($colDemanda * $colCPP * 2) / $colCMP;
                                                    } else {
                                                        $colOEQ = ($colDemanda * $colCPP * 2);
                                                    }
                                                    $colOEQ = number_format(sqrt($colOEQ), 0, ".", ""); 

                                                    $zona = $colStock < ($colOEQ /2) ? "A" : ($colStock < $colOEQ ? "B" : "C");

                                                    $className = $classEstado[$zona];

                                                    $leyenda = $leyendaEstado[$zona];

                                                    if ($zona!="C") {

                                                        $cantPedidos = number_format(($colDemanda - $colStock) / $colOEQ, 0, ".", ""); 
                                            
                                        ?>

                                                <tr>
                                                    <td><?php echo $colSku; ?></td>
                                                    <td><?php echo $colTitulo; ?></td>
                                                    <td align="right"><?php echo $colStock; ?></td>
                                                    <td align="right"><?php echo $colDemanda; ?></td>
                                                    <td align="right"><?php echo $colCPP; ?></td>
                                                    <td align="right"><?php echo $colCMP; ?></td>
                                                    <td align="right"><?php echo $colOEQ; ?></td>
                                                    <td class="<?php echo $className;?>" align="center"><?php echo $leyenda; ?></td>
                                                    <td align="right"><?php echo $cantPedidos; ?></td>
                                                </tr>
                                        <?php
                                                    }
                                                }
                                            }
                                            else
                                            {
                                            echo '0 results';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                </form>
                            </div>
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
