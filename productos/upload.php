<?php 

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "app/models/producto.php";

$csv_data = array();
$tipo = "";
if ( isset($_POST["submit"]) ) {
    $tipo = $_POST["tipo"];
    if ( isset($_FILES["file"])) {

        if ($_FILES["file"]["error"] > 0) {
             echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
         } else {
            $tmpName = $_FILES["file"]["tmp_name"]; 
            $csv_data = array_map('str_getcsv', file($tmpName));
            // elimina encabezado
            array_shift($csv_data);
         }
    } else {
        echo "No file selected <br />";
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
                                <form id="formProductos" method="post" enctype="multipart/form-data">
                                    <div class="row justify-content-end">

                                        <div class="form-group">
                                            <label>Seleccionar Archivo</label>
                                            <input type="file" name="file" id="file" />
                                        </div>

                                        <div class="form-group">
                                            <label>Tipo</label>
                                            <select name="tipo" id="tipo" class="form-control" data-live-search="true" data-size="10">
                                                <option value="">Seleccione</option>
                                                <option value="ALTA" data-subtext="(Alta)">ALTA</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Importar</label>
                                            <input type="submit" name="submit" />
                                        </div>

                                    </div>
                                </form>
                            </div>
                            <div class="card-body">    
                                <table class="table table-striped table-bordered" id="tbProductos">
                                <thead>
                                    <tr>
                                        <th class="text-center">id</th>
                                        <th class="text-center">sku</th>
                                        <th class="text-center">titulo</th>
                                        <th class="text-center">stock</th>
                                        <th class="text-center">precio</th>
                                        <th class="text-center">editorial</th>
                                        <th class="text-center">serie</th>
                                        <th class="text-center">tomo</th>
                                        <th class="text-center">formato</th>
                                        <th class="text-center">nuevo</th>
                                        <th class="text-center">SQL</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>    
                                <tbody>
                                <?php 
                                    $count_row = 0;
                                    foreach ($csv_data as $item){
                                        $count_row++;

                                        $id = $item[0];
                                        $sku = $item[1];
                                        $titulo = $item[2];
                                        $stock =  $item[3];
                                        $precio = $item[4];
                                        $editorial = $item[5];
                                        $serie = $item[6];
                                        $tomo = $item[7];
                                        $formato = $item[8];
                                        $admiteDescuento = $item[9];
                                        $esNuevo = $item[10];

                                        $mensaje =  "No encontrado";
                                        // analizar si existe o no el producto
                                        $productoPDO = new Producto();
                                        if ($sku!="NA"){
                                            $result = $productoPDO->getAllByCriteria('sku', $sku);
                                        } else {
                                            $result = $productoPDO->getAllByCriteria('titulo', $titulo);
                                        }
                                        if(!is_null($result)){
                                            $mensaje = "OK";
                                        } else {
                                            // No encontrado 
                                            if ($tipo == "ALTA") {
                                                $productoPDO->sku = $sku;
                                                $productoPDO->titulo = $titulo;
                                                $productoPDO->idEditorial = $editorial;
                                                $productoPDO->stock = $stock;
                                                $productoPDO->precio = $precio;
                                                $productoPDO->idSerie = $serie;
                                                $productoPDO->tomo = $tomo;
                                                $productoPDO->idFormato = $formato;
                                                $productoPDO->nuevo = $esNuevo;
                                                $productoPDO->create();
                                                $mensaje = "OK";
                                            } 
                                        }
                                 ?>

                                <tr>
                                    <td><?php echo $id ?></td>
                                    <td><?php echo $sku ?></td>
                                    <td><?php echo $titulo ?></td>
                                    <td><?php echo $stock ?></td>
                                    <td><?php echo $precio ?></td>
                                    <td><?php echo $editorial ?></td>
                                    <td><?php echo $serie ?></td>
                                    <td><?php echo $tomo ?></td>
                                    <td><?php echo $formato ?></td>
                                    <td><?php echo $admiteDescuento ?></td>
                                    <td><?php echo $esNuevo ?></td>
                                    <td><?php echo $sql ?></td>
                                    <td><?php echo $mensaje ?></td>
                                </tr>
                                <?php 
                                        }
                                 ?>
                                </tbody> 
                                <tfoot>
                                    <tr>
                                        <td colspan="12">Total Registros : <?php echo $count_row ?></td>
                                    </tr>
                                </tfoot>
                                </table>
                            </div>   
                        </div>
                    </div>
                </main>
                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
