<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}


if(isset($_POST["canal"]) && !empty($_POST["canal"])){
    
    $itemArray = array('canal'=>$_POST["canal"]);
    $_SESSION["evento_header"] = $itemArray;

    header('location: evento.php');
    exit();
    
} 


// Include config file
//require_once "../server.php";

$cliente = 1; // generico cliente
$medioPago = "";
$fecha = date("Y-m-d");

// chequeo si la session esta inicializada.
if(!empty($_SESSION["evento_header"])) {

    if(isset($_SESSION["evento_header"]['canal']) and $_SESSION["evento_header"]['canal']!=""){
        $canal = $_SESSION["evento_header"]['canal'];
    } 
}

// model
require_once "../app/models/canal.php";
require_once "../app/models/producto.php";
require_once "../app/models/editoriales.php";
require_once "../app/models/productoSerie.php";
require_once "../app/models/productoFormato.php";

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Preparar Evento</title>
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
                    "order": [[ 0, 'asc' ]]
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
                        <h1 class="mt-4">Preparar Evento</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
                        </ol>
                        <!-- Content Row -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                    <form id="formFactura" method="post">
                                        <!-- Evento --> 
                                        <div id="container-cliente">
                                            <div  class="form-group">
                                                <label>Evento</label>
                                                <select name="canal" id="canal" class="form-control" data-live-search="true" data-size="10" required="required">
                                                    <option value="">Seleccione</option>
                                                    <?php
                                                        $canalPDO = new Canal();
                                                        $eventos = $canalPDO->getByType("EVENTO","nombre ASC");
                                                        foreach ($eventos as $val) {
                                                        ?>
                                                        <option 
                                                            value="<?php echo $val['id']?>" 
                                                            data-subtext="(<?php echo $val['id']?>)"
                                                            <?php if($val['id']==$canal) echo 'selected="selected"'; ?>
                                                            >
                                                            <?php echo mb_strtoupper($val['nombre'] . ' '.$val['nombre'],'UTF-8')?>
                                                        </option>
                                                        <?php 
                                                            }
                                                            unset($canalPDO);
                                                        ?>
                                                </select>
                                            </div>
                                        </div>
                                        <br>                    
                                        <!-- PRODUCTOS --> 
                                        <table class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Producto</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                    <th>
                                                        <a href="action-cart.php?action=empty&forwardOk=preparar-evento.php" onclick="return confirm('Seguro que quiere limpiar el carrito?');"><i class="fas fa-broom"></i>&nbsp;&nbsp;Limpiar Evento</a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                if(isset($_SESSION["evento_item"])){
                                                    $total_quantity = 0;
                                                    $total_price = 0;
                                                    foreach ($_SESSION["evento_item"] as $item){
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
                                                    <td colspan="4"><b>Total Evento</b></td>
                                                    <td><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<?= number_format($grand_total,2); ?></b></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <BR>        

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary <?= ($grand_total > 1) ? '' : 'disabled'; ?>"><i class="far fa-credit-card"></i>&nbsp;&nbsp;Crear</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>                        

                            <div class="col-lg-6 mb-4">

                                <div class="card shadow mb-4">
                                    <div class="card-body">

                                        <form id="formProductos" method="post" onSubmit="return false;">

                                            <table class="table table-striped table-bordered" id="tbProductos">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">ID</th>
                                                        <th class="text-center">Titulo</th>
                                                        <th class="text-center">Precio</th>
                                                        <th class="text-center">Seleccionar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php 
                                                    $productoPDO = new Producto();
                                                    $productos = $productoPDO->getAllActive();
                                                    foreach ($productos as $row) {

                                                        $colSku = $row['sku'];
                                                        $colTitulo = $row['titulo'];
                                                        $colPrecio = $row['precio'];
                                                        $colEditorial = $row['editorial'];
                                                        $colNuevo = $row['nuevo'];
                                                    
                                                ?>

                                                    <tr>
                                                        <td><?php echo $colSku; ?></td>
                                                        <td><?php echo $colTitulo. ' (' . $colEditorial.')' . ($colNuevo==TRUE ? '' : ' - Usado'); ?></td>
                                                        <td>
                                                            <?= $colPrecio; ?>
                                                            <a href="../productos/update-productos.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        </td>
                                                        <td align="center">
                                                            <a href="action-cart.php?action=add&id=<?php echo $row['id'];?>&forwardOk=preparar-evento.php" class="text-primary"><i class="fa fa-fw fa-cart-plus"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    }
                                                    unset($productoPDO);
                                                ?>
                                                </tbody>
                                            </table>
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

    </body>
</html>
