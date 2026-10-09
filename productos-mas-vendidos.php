<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "app/models/producto.php";

$currentMonth = date('n');

$mesesN=array(1=>"Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Mas Vendidos x Semana</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
                $('#tbMasVendidos').dataTable( {
                    "order": [[ 14, 'desc' ]],
                    "lengthMenu": [[20, 50, 75, 100, -1], [20, 50, 75, 100, "All"]]
                });
            });
        </script>

    </head>
    <body class="sb-nav-fixed">

        <?php include_once 'topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include_once 'sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Mas Vendidos</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Mas Vendidos
                            </div>
                            <div class="card-body">
                                <form id="formProductos" method="post" onSubmit="return false;">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered table-responsive" id="tbMasVendidos">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Marca</th>
                                    <?php 
                                        for ($x = $currentMonth + 1; $x <= 12; $x++) {
                                    ?>
                                            <th class="text-center"><?php echo $mesesN[$x]?></th>
                                    <?php                                                       
                                        }                                                
                                    ?>
                                    <?php 
                                        for ($x = 1; $x <= $currentMonth; $x++) {
                                    ?>
                                            <th class="text-center"><?php echo $mesesN[$x]?></th>
                                    <?php                                                       
                                        }                                                
                                    ?>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Sugerido</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 

                                            $productoPDO = new Producto();
                                            $productos =  $productoPDO->getAllSaleByDays(12); // ultimo año

                                            if (!is_null($productos)) {

                                                $arrayDias = array();

                                                $prodAnt = null;
                                                $colTituloAnt = '';
                                                $colMarcaAnt = '';
                                                $total = 0;

                                                foreach ($productos as $row) {
                                                    
                                                    $colDia = $row['mes'];
                                                    $colCantidad = $row['cantidad'];
                                                    $colDiaNumero = $row['numero'];
                                                    $colMarca = $row['editorial'];
                                                    
                                                    $prod = $row['id'];
                                                    $colTitulo = $row['titulo'];

                                                    if (is_null($prodAnt)){
                                                        $colTituloAnt = $colTitulo;
                                                        $prodAnt = $prod;
                                                        $colMarcaAnt = $colMarca;
                                                    }

                                                    if ($prodAnt != $prod){

                                                        // cambio el producto imprimrir 
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $colTituloAnt; ?></td>
                                                        <td><?php echo $colMarcaAnt; ?></td>

                                                        <?php 
                                                            for ($x = $currentMonth + 1; $x <= 12; $x++) {
                                                        ?>
                                                                <td><?php echo $arrayDias[$x]; ?></td>
                                                        <?php                                                       
                                                            }                                                
                                                        ?>
                                                        <?php 
                                                            for ($x = 1; $x <= $currentMonth; $x++) {
                                                        ?>
                                                                <td><?php echo $arrayDias[$x]; ?></td>
                                                        <?php                                                       
                                                            }                                                
                                                        ?>

                                                        <td><?php echo $total; ?></td>
                                                        <td><?php echo $promedio; ?></td>
                                                        <td align="center">
                                                            <a href="productos\view-productos.php?forwardOk=..\productos-mas-vendidos.php&id=<?php echo $prodAnt;?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    
                                                        // inicializo
                                                        $colTituloAnt = $colTitulo;
                                                        $prodAnt = $prod;
                                                        $colMarcaAnt = $colMarca;
                                                        $arrayDias = array();
                                                        $total = 0;
                                                        $cantidadDias = 0;
                                                
                                                    } 

                                                    $arrayDias[$colDiaNumero] = $colCantidad;
                                                    $total+=$colCantidad;
                                                    $cantidadDias+=1;
                                                    $promedio = round($total / $cantidadDias);
                                           
                                        ?>

                                        <?php
                                                }

                                                ?>
                                                <tr>
                                                    <td><?php echo $colTituloAnt; ?></td>
                                                    <td><?php echo $colMarcaAnt; ?></td>
                                                    <?php 
                                                        for ($x = $currentMonth + 1; $x <= 12; $x++) {
                                                    ?>
                                                            <td><?php echo $arrayDias[$x]; ?></td>
                                                    <?php                                                       
                                                        }                                                
                                                    ?>
                                                    <?php 
                                                        for ($x = 1; $x <= $currentMonth; $x++) {
                                                    ?>
                                                            <td><?php echo $arrayDias[$x]; ?></td>
                                                    <?php                                                       
                                                        }                                                
                                                    ?>
                                                    <td><?php echo $total; ?></td>
                                                    <td><?php echo $promedio; ?></td>
                                                    <td align="center">
                                                    <a href="productos\view-productos.php?forwardOk=..\productos-mas-vendidos.php&id=<?php echo $prodAnt;?>" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>
                                                    </td>
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
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        
    </body>
</html>