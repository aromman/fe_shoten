<?php 

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once dirname(__DIR__) . "/app/models/producto.php";
require_once dirname(__DIR__) . "/app/models/editoriales.php";

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/*
 * Formato del CSV (con fila de encabezado):
 * id, sku, titulo, stock, precio, editorial, serie, tomo, formato, admite_descuento, nuevo
 * - sku "NA" = sin código: se busca por título.
 * - editorial, serie y formato son ids.
 */
$columnasEsperadas = 11;
$filas = array();
$errorArchivo = "";
$tipo = "";

if (isset($_POST["submit"])) {
    $tipo = isset($_POST["tipo"]) ? $_POST["tipo"] : "";

    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] === UPLOAD_ERR_NO_FILE) {
        $errorArchivo = "Seleccioná un archivo CSV.";
    } elseif ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        $errorArchivo = "No se pudo subir el archivo (código " . (int) $_FILES["file"]["error"] . ").";
    } else {
        $contenido = file_get_contents($_FILES["file"]["tmp_name"]);
        // Quita BOM y convierte a UTF-8 si viene de Excel en Windows-1252.
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);
        if (!mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252');
        }
        $lineas = preg_split('/\r\n|\r|\n/', $contenido);
        array_shift($lineas); // encabezado

        $productoPDO = new Producto();
        $editorialPDO = new Editorial();
        $porcentajes = array();

        foreach ($lineas as $linea) {
            if (trim($linea) === '') {
                continue;
            }
            $item = array_map('trim', str_getcsv($linea));
            $fila = array('datos' => array_pad($item, $columnasEsperadas, ''), 'estado' => '');

            if (count($item) < $columnasEsperadas) {
                $fila['estado'] = 'Error: faltan columnas';
                $filas[] = $fila;
                continue;
            }

            list(, $sku, $titulo, $stock, $precio, $editorial, $serie, $tomo, $formato, $admiteDescuento, $esNuevo) = $item;

            $existentes = $sku !== 'NA'
                ? $productoPDO->getAllByCriteria('sku', $sku)
                : $productoPDO->getAllByCriteria('titulo', $titulo);

            if (!empty($existentes)) {
                $fila['estado'] = 'Ya existe';
            } elseif ($tipo !== 'ALTA') {
                $fila['estado'] = 'No encontrado';
            } elseif ($titulo === '' || !is_numeric($precio) || !ctype_digit($editorial)) {
                $fila['estado'] = 'Error: título, precio o editorial inválidos';
            } else {
                if (!array_key_exists($editorial, $porcentajes)) {
                    $rsEditorial = $editorialPDO->getById($editorial);
                    $porcentajes[$editorial] = $rsEditorial ? (float) $rsEditorial['porcentaje'] : null;
                }

                if ($porcentajes[$editorial] === null) {
                    $fila['estado'] = 'Error: la editorial ' . $editorial . ' no existe';
                } else {
                    $nuevo = new Producto();
                    $nuevo->sku = $sku;
                    $nuevo->titulo = $titulo;
                    $nuevo->idEditorial = (int) $editorial;
                    $nuevo->stock = (int) $stock;
                    $nuevo->precio = (float) $precio;
                    $nuevo->precioCosto = round((float) $precio * (100 - $porcentajes[$editorial]) / 100);
                    $nuevo->idSerie = ctype_digit($serie) ? (int) $serie : 1;
                    $nuevo->tomo = (int) $tomo;
                    $nuevo->idFormato = ctype_digit($formato) ? (int) $formato : 1;
                    $nuevo->admiteDescuento = $admiteDescuento === '0' ? 0 : 1;
                    $nuevo->nuevo = $esNuevo === '0' ? 0 : 1;
                    $idNuevo = $nuevo->create();
                    $fila['estado'] = $idNuevo ? 'Creado (id ' . $idNuevo . ')' : 'Error al crear';
                }
            }

            $filas[] = $fila;
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
                                <?php if ($errorArchivo !== '') { ?>
                                    <div class="alert alert-danger"><?php echo h($errorArchivo); ?></div>
                                <?php } ?>
                                <p class="small text-muted mb-2">
                                    Columnas del CSV (con encabezado): id, sku, titulo, stock, precio, editorial, serie, tomo, formato, admite_descuento, nuevo.
                                    Editorial, serie y formato van por id. Usá "NA" como sku si el producto no tiene código.
                                </p>
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
                                        <th class="text-center">admite desc.</th>
                                        <th class="text-center">nuevo</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($filas as $fila) { ?>
                                    <tr>
                                        <?php foreach (array_slice($fila['datos'], 0, $columnasEsperadas) as $valor) { ?>
                                            <td><?php echo h($valor); ?></td>
                                        <?php } ?>
                                        <td><?php echo h($fila['estado']); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="12">Total Registros : <?php echo count($filas); ?></td>
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
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
