<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$filtrar_disponible = FALSE;
if(isset($_REQUEST['stock']) and $_REQUEST['stock']!=""){
    $filtrar_disponible = TRUE;
}

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

        <script>
            $(document).ready(function(e) {
                $('#tbProductos').dataTable( {
                    "order": [[ 1, 'asc' ]],
                    "lengthMenu": [[25, 50, 75, 100, 150, -1], [25, 50, 75, 100, 150, "All"]]
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
                        <h1 class="mt-4">Productos Agotados</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Productos Agotados
                            </div>
                            <div class="card-body">
                                <form id="formProductos" method="post" onSubmit="return false;">
                                    <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-responsive" id="tbProductos">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Sku</th>
                                                <th class="text-center">Titulo</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                $productoPDO = new Producto();
                                                $productos = $productoPDO->getAllOutOfStock();
                                            
                                                foreach ($productos as $row) {

                                                    $colSku = $row['sku'];
                                                    $colTitulo = $row['titulo'];
                                                    $colEditorial = $row['editorial'];
                                                    $colEsNuevo = $row['nuevo'];
                                            
                                            ?>

                                            <tr>
                                                <td><?php echo $colSku; ?></td>
                                                <td><?php echo $colTitulo. ' (' . $colEditorial.')' . ($colEsNuevo==TRUE ? '' : ' - Usado'); ?></td>
                                                <td align="center">
                                                    <a href="update-productos.php?id=<?php echo $row['id'];?>&forwardOk=agotados.php" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
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
                </main>
                <?php include '../footer.php';?>
            </div>
            
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>


    </body>
</html>
