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
include_once('../config.php');

if(isset($_POST["saleAction"]) && !empty($_POST["saleAction"]) && $_POST["saleAction"]="add" && 
    isset($_POST["id"]) && !empty($_POST["id"])){

    $idItem = $_POST["id"];
  
    // agrego producto a session
    $titulo = $_POST["producto"];
    $sku = $_POST["sku"];
    $cantidad = $_POST["cantidad"];


    error_log(PHP_EOL."Ubicacion  :  " . $_POST["ubicacion"], 3, "my-errors.log");  

    $itemArray = array('ubicacion'=>$_POST["ubicacion"]);
    $_SESSION["stock_header"] = $itemArray;

    $itemArray = array($idItem=>array('id'=>$idItem, 'titulo'=>$titulo, 'sku'=>$sku, 'cantidad'=>$cantidad));
    if(!empty($_SESSION["stock_item"])) {
        $found = FALSE;
        foreach($_SESSION["stock_item"] as $k => $v) {
            if($idItem == $v["id"]) {
                $found = TRUE;
                if(empty($_SESSION["stock_item"][$k]["cantidad"])) {
                    $_SESSION["stock_item"][$k]["cantidad"] = 0;
                }
                $_SESSION["stock_item"][$k]["cantidad"] += $cantidad;
            }
        }
        if (!$found){
            $_SESSION["stock_item"] = array_merge($_SESSION["stock_item"],$itemArray);
        }
    } else {
        $_SESSION["stock_item"] = $itemArray;
    }


}

if(isset($_POST["ubicacion"]) && !empty($_POST["ubicacion"])){
    $itemArray = array('ubicacion'=>$_POST["ubicacion"]);
    $_SESSION["stock_header"] = $itemArray;
} 

// chequeo si la session esta inicializada.
if(!empty($_SESSION["stock_header"])) {

    if(isset($_SESSION["stock_header"]['ubicacion']) and $_SESSION["stock_header"]['ubicacion']!=""){
        $ubicacion = $_SESSION["stock_header"]['ubicacion'];
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
        <title>Ubicar Stock</title>
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
                        <h1 class="mt-4">Ubicar Stock</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="/index.php">Stock</a></li>
                        </ol>
                        <!-- Content Row -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                    <form id="formFactura" method="post">
                                        <input type="hidden" name="saleAction" value="ubicar">
                                        <!-- CLIENTE --> 
                                        <div id="container-cliente">
                                            <div  class="form-group">
                                                <label>Ubicacion</label>
                                                <select name="ubicacion" id="ubicacion" class="form-control" required="required">
                                                    <option value="">Seleccione</option>
                                                    <?php
                                                        $result	=	$db->query('select 
                                                                                pu.id id,
                                                                                concat(c.nombre, " - ", put1.nombre, " ", pu.nivel_codigo_01, " - ", put2.nombre, " ", pu.nivel_codigo_02 )  nombre
                                                                                from productos_ubicacion pu, 
                                                                                canal c,
                                                                                productos_ubicacion_tipo put1,
                                                                                productos_ubicacion_tipo put2
                                                                                where pu.id_canal = c.id
                                                                                and pu.id_nivel_tipo_01 = put1.id
                                                                                and pu.id_nivel_tipo_02 = put2.id
                                                                                and pu.id not in (select distinct psk.id_ubicacion from productos_stock psk);');
                                                        while($val  =   $result->fetch_assoc()){
                                                        ?>
                                                        <option 
                                                            value="<?php echo $val['id']?>" 
                                                            data-subtext="(<?php echo $val['id']?>)">
                                                            <?php if($val['id']==$ubicacion) echo 'selected="selected"'; ?>
                                                            <?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                                                    <?php }?>
                                                    
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
                                                    <th>Cantidad</th>
                                                    <th>
                                                        <a href="action-stock.php?action=empty&forwardOk=ubicar.php" onclick="return confirm('Seguro que quiere limpiar el carrito?');"><i class="fas fa-broom"></i>&nbsp;&nbsp;Limpiar Carrito</a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                if(isset($_SESSION["stock_item"])){
                                                    $total_quantity = 0;
                                                    $total_price = 0;
                                                    foreach ($_SESSION["stock_item"] as $item){
                                                        $item_price = $item["cantidad"] * $item["precio"];
                                            ?>
                                                <tr>
                                                    <td><?= $item["sku"]; ?></td>
                                                    <input type="hidden" class="pid" value="<?= $item["id"]; ?>">
                                                    <td><?= $item["titulo"]; ?></td>
                                                    <td><?= $item["cantidad"] ?></td>
                                                    <td>
                                                    <a href="action-stock.php?action=remove&id=<?= $item['id'] ?>&forwardOk=ubicar.php" class="text-danger lead" onclick="return confirm('Are you sure want to remove this item?');"><i class="fas fa-trash-alt"></i></a>
                                                    </td>
                                                </tr>
                                            <?php 
                                                    } 
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                        </div>
                                        <BR>        
                                        <div class="form-group">
                                            <a href="action-stock.php?action=ubicar&ubicacion=5&forwardOk=existencias.php" class="btn btn-primary" onclick="return confirm('Confirma Ubicacion?');"><i class="fa fa-fw fa-plus-circle"></i>Ubicar</a>
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
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-center">Ubicar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php 
                                                    $productoPDO = new Producto();
                                                    $productos = $productoPDO->getAllWithOutUbicacion();
                                                    foreach ($productos as $row) {

                                                        $colId = $row['id'];
                                                        $colSku = $row['sku'];
                                                        $colTitulo = $row['titulo'];
                                                        $colEditorial = $row['editorial'];
                                                        $colNuevo = $row['nuevo'];

                                                        $colCantidad = $row['stock'];
                                                    
                                                ?>

                                                    <tr>
                                                        <td><?php echo $colSku; ?></td>
                                                        <td><?php echo $colTitulo. ' (' . $colEditorial.')' . ($colNuevo==TRUE ? '' : ' - Usado'); ?></td>
                                                        <td><?= $colCantidad; ?></td>
                                                        <td align="center">
                                                            <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#addModal" data-id="<?php echo $colId;?>" data-sku="<?php echo $colSku;?>" data-producto="<?php echo $colTitulo;?>" data-stock="<?php echo $colCantidad;?>" ><i class="fa fa-fw fa-cart-plus"></i></a>
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
            var stock = applicant.data('stock');

            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="sku"]').val(sku);
            modal.find('input[name="producto"]').val(producto);
            modal.find('input[name="stock"]').val(stock);

        });
    </script> 

</html>
