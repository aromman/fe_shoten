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

// Processing form data when form is submitted
if(isset($_POST["canal"]) && !empty($_POST["canal"])){

    error_log(PHP_EOL."Insertando", 3, "my-errors.log");  

    $id_canal = $_POST["canal"];
    
    $sqlItemUpdate = array(); 
    $sql = array(); 

    foreach ($_SESSION["evento_item"] as $item){
        // 
        $id_producto =  $item["id"];
        $cantidadItem = $item["cantidad"];
        $precioItem = $item["precio"];
        $subTotalItem = $cantidadItem * $precioItem;

        $item_descuento = 0;
        if ($porcentaje > 0){
            $item_descuento = ($precioItem * ($porcentaje / 100)) * $cantidadItem;     
        }
        $totalItem = $subTotalItem - $item_descuento;

        $sql[] = '('.$id_canal.','.$id_producto.', 1, 1, '.$cantidadItem.','.$precioItem.')';

        $sqlItemUpdate[] = $id_producto;

    }
    $sqlInsert = 'INSERT INTO stock (id_canal, id_producto, id_inversor, id_estado_pedido, cantidad, precio) values '.implode(',', $sql);
    error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
    $db->query($sqlInsert);

    unset($_SESSION["evento_item"]);
    unset($_SESSION["evento_header"]);

    header('location: preparar-evento.php');
    exit();
    
} else{

    if(!empty($_SESSION["evento_header"])) {
        if(isset($_SESSION["evento_header"]['canal']) and $_SESSION["evento_header"]['canal']!=""){
            $canal = $_SESSION["evento_header"]['canal'];
        } else {
            $canal= "";
        }
    
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
        <title>Evento</title>
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

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Confirmar Evento</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="preparar-evento.php">Eventos</a></li>
                            <li class="breadcrumb-item active">Confirmar Evento</li>
                        </ol>
                        <div class="card-body">
                            <form id="formSale" method="post">
                                <div  class="form-group">
                                    <label>Evento</label>
                                    <?php
                                        $result	=	$db->query("SELECT * FROM canal where id = " . $canal);
                                        $nombreCanal = "";
                                        while($val  =   $result->fetch_assoc()){
                                            $nombreCanal = mb_strtoupper($val['nombre'],'UTF-8');
                                        }
                                    ?>
                                    <input type="text" name="nombreCanal" value="<?php echo $nombreCanal;?>" class="form-control" required="required" readOnly>
                                    <input type="hidden" name="canal" value="<?php echo $canal;?>">
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
                                        if(isset($_SESSION["evento_item"])){
                                            $total_quantity = 0;
                                            $total_price = 0;
                                            $total_descuento = 0;
                                            foreach ($_SESSION["evento_item"] as $item){
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
                                <a href="preparar-evento.php" class="btn btn-warning"><i class="fa fa-fw fa-pencil"></i>&nbsp;&nbsp;Modificar</a>
                                <a href="action-cart.php?action=empty&forwardOk=preparar-evento.php" class="btn btn-danger" onClick="return confirm('Esta seguro de querer cancelar el evento?');"><i class="fa fa-fw fa-trash"></i>&nbsp;&nbsp;Cancelar</a>
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
