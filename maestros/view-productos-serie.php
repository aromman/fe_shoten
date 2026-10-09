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

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""
    and isset($_GET['nombre']) and $_GET['nombre']!=""
    and isset($_GET['maximo']) and $_GET['maximo']!=""
    and isset($_GET['minimo']) and $_GET['minimo']!=""
    ){


        $id_serie =  trim($_GET["id"]);

        $nombreSerie = trim($_GET["nombre"]);
        $cupoMaximo = number_format(trim($_GET["maximo"]), 0, ".", ""); 
        $cupoMinimo = number_format(trim($_GET["minimo"]), 0, ".", ""); 

        $sql = "select 
        p.id,
        p.sku,
        p.titulo,
        p.stock,
        p.precio,
        p.nuevo,
        e.nombre editorial 
        from productos p, editoriales e 
        where p.id_editorial = e.id
        and p.id_serie = ".$id_serie."
        and p.stock > 0
        order by p.titulo;
        ";
        $rsProductos = $db->query($sql);

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
        <title>Ver Productos Serie</title>
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
                        <h1 class="mt-4">Productos en Serie</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="productos-serie.php">Serie</a></li>
                            <li class="breadcrumb-item active">Productos en Serie</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <div  class="form-group">
                                    <label>Serie</label>
                                    <input type="text" name="nombreSerie" value="<?php echo $nombreSerie;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Cupo Maximo</label>
                                    <input type="number" step="0" name="cupoMaximo" value="<?php echo $cupoMaximo;?>" class="form-control" required="required" readOnly>
                                </div>
                                <div  class="form-group">
                                    <label>Cupo Minimo</label>
                                    <input type="number" step="0" name="cupoMinimo" value="<?php echo $cupoMinimo;?>" class="form-control" required="required" readOnly>
                                </div>

                                <div class="container-fluid px-4">
                                    <div class="table-responsive mt-2">
                                        <table class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php

                                                if (mysqli_num_rows($rsProductos) > 0) {
                                                    while($item = mysqli_fetch_assoc($rsProductos))
                                                    {

                                                        $item_quantity = $item["stock"];
                                                        $item_price = $item["precio"];
                                                        $item_total = $item_quantity * $item_price;

                                                        // acumuladores
                                                        $grand_total += $item_total;
                                                        $total_quantity += $item_quantity;

                                                        $colTitulo = $item["titulo"];
                                                        $colEditorial = $item["editorial"];
                                                        $colEsNuevo = $item["nuevo"];
                                                        
                                            ?>
                                            <tr>
                                                <td><?= $item["sku"]; ?></td>
                                                <td><?php echo $colTitulo. ' (' . $colEditorial.')' . ($colEsNuevo==TRUE ? '' : ' - Usado'); ?></td>
                                                <td>
                                                <i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_price,2); ?>
                                                </td>
                                                <input type="hidden" class="pprice" value="<?= $item_price ?>">
                                                <td><?= $item_quantity ?></td>
                                                <td align="right"><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($item_total,2); ?></td>
                                            </tr>
                                            <?php 
                                                    } 
                                                }
                                            ?>
                                            </tbody>
                                            <tfooter>
                                            <tr>
                                                <td>
                                                    <b>Cantidad Items</b></td>
                                                    <td><?= $total_quantity ?></td>
                                                </td>
                                                <td colspan="2"><b>Monto Total</b></td>
                                                <td align="right"><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                            </tr>
                                            </tfooter>
                                        </table>
                                    </div>
                                </div>

                                <?php 
                                    if ($total_quantity < $cupoMaximo ){      
                                        
                                        $montoDisponible = $grand_total / $total_quantity;
                                        $montoDisponible = $montoDisponible * ( $cupoMaximo - $total_quantity);
                                        $montoDisponible = $montoDisponible * 0.70; // restamos el 30%
                                       

                                ?>
                                <div class="alert alert-primary" role="alert">Monto disponibles para compras <b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($montoDisponible,2); ?></b></div>
                                <div class="alert alert-success" role="alert">Faltan <?= number_format($cupoMaximo - $total_quantity,0);?> unidades para el cupo maximo</div>
                                <?php } else {
                                ?>
                                <div class="alert alert-danger" role="alert">Cupo maximo Completo. No podes comprar!!!!</div>                                
                                <?php
                                }

                                    if ($total_quantity <=$cupoMinimo ){      

                                ?>

                                <div class="alert alert-danger" role="alert">Estas por debajo del cupo minimo</div>                                
                                <?php }
                                ?>
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
