<?php
session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../bff/ofertas/activas-view-model.php";

$nombreBuscado = isset($_GET['nombre']) ? trim((string) $_GET['nombre']) : '';
$ofertasViewModel = obtenerOfertasActivasViewModel($nombreBuscado);
$ofertasProductos = $ofertasViewModel['productos'];
$totRegistros = $ofertasViewModel['totales']['registros'];
$totCantidad = $ofertasViewModel['totales']['cantidad'];
$totPrecioLista = $ofertasViewModel['totales']['precioLista'];
$totPrecioOferta = $ofertasViewModel['totales']['precioOferta'];
$totPorcentajeDiferencia = $ofertasViewModel['totales']['porcentajeDiferencia'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (isset($_GET['export']) && $_GET['export'] === 'html') {
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="ofertas-activas.html"');
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Ofertas Activas</title>
        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                color: #222;
                margin: 24px;
            }

            h1 {
                text-align: center;
                margin-bottom: 24px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #ccc;
                padding: 10px;
            }

            th {
                background: #f2f2f2;
                text-align: left;
            }

            .precio {
                text-align: right;
                white-space: nowrap;
            }

            .precio-lista {
                text-decoration: line-through;
                color: #777;
            }

            .consulta {
                margin-top: 28px;
                text-align: center;
                font-size: 1.2rem;
                font-weight: 700;
            }
        </style>
    </head>
    <body>
        <h1>Ofertas Activas</h1>
        <table>
            <thead>
                <tr>
                    <th>Nombre del producto</th>
                    <th>Precio de Lista Tachado</th>
                    <th>Precio de Oferta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ofertasProductos as $row) { ?>
                    <tr>
                        <td><?php echo h($row['titulo']); ?></td>
                        <td class="precio precio-lista">$ <?php echo h($row['precioLista']); ?></td>
                        <td class="precio">$ <?php echo h($row['precioOferta']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="consulta">Consulta en Caja</div>
    </body>
</html>
<?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Ofertas Activas</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
        <style>
            .ofertas-active-summary {
                gap: .75rem;
            }
            .ofertas-active-summary .card {
                min-width: 160px;
                border: 1px solid rgba(0, 0, 0, .08);
                box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            }
            .ofertas-active-summary-label {
                color: #6c757d;
                font-size: .75rem;
                line-height: 1;
            }
            .ofertas-active-summary-value {
                font-size: 1rem;
                font-weight: 700;
                line-height: 1.2;
                white-space: nowrap;
            }
            .ofertas-active-search {
                max-width: 520px;
            }
        </style>
        <script>
            $(document).ready(function() {
                $('#tbOfertasActivas').dataTable({
                    "ordering": true,
                    "paging": false,
                    "info": false,
                    "dom": "rt",
                    "order": [[0, "asc"]],
                    "language": {
                        "emptyTable": "No hay ofertas activas"
                    }
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
                        <h1 class="mt-4">Ofertas Activas</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Ofertas Activas</li>
                        </ol>

                        <div class="d-flex flex-wrap ofertas-active-summary mb-4">
                            <div class="card">
                                <div class="card-body py-2 px-3">
                                    <div class="ofertas-active-summary-label">Productos</div>
                                    <div class="ofertas-active-summary-value"><?php echo h($totRegistros); ?></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body py-2 px-3">
                                    <div class="ofertas-active-summary-label">Stock</div>
                                    <div class="ofertas-active-summary-value"><?php echo h($totCantidad); ?></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body py-2 px-3">
                                    <div class="ofertas-active-summary-label">Lista valorizada</div>
                                    <div class="ofertas-active-summary-value">$ <?php echo h($totPrecioLista); ?></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body py-2 px-3">
                                    <div class="ofertas-active-summary-label">Oferta valorizada</div>
                                    <div class="ofertas-active-summary-value">$ <?php echo h($totPrecioOferta); ?></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body py-2 px-3">
                                    <div class="ofertas-active-summary-label">Dif. lista vs oferta</div>
                                    <div class="ofertas-active-summary-value"><?php echo h($totPorcentajeDiferencia); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <a class="btn btn-success" href="activas.php?<?php echo h(http_build_query(array_filter(array('nombre' => $nombreBuscado, 'export' => 'html'), function ($value) { return $value !== ''; }))); ?>">
                                <i class="fa-solid fa-file-code"></i> Exportar a HTML
                            </a>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-tag me-1"></i>
                                Productos con precio de oferta vigente
                            </div>
                            <div class="card-body">
                                <form method="get" class="ofertas-active-search mb-3">
                                    <div class="input-group">
                                        <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre" value="<?php echo h($nombreBuscado); ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </button>
                                        <a class="btn btn-secondary" href="activas.php">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </a>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tbOfertasActivas">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-center">Precio Costo</th>
                                                <th class="text-center">Precio Lista</th>
                                                <th class="text-center">Precio Oferta</th>
                                                <th class="text-center">Fecha Precio</th>
                                                <th class="text-center">% Lista vs Oferta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ofertasProductos as $row) { ?>
                                                <tr class="<?php echo $row['precioOfertaInconsistente'] ? 'table-danger' : ''; ?>">
                                                    <td><?php echo h($row['titulo']); ?></td>
                                                    <td align="right"><?php echo h($row['cantidad']); ?></td>
                                                    <td align="right"><?php echo h($row['precioCosto']); ?></td>
                                                    <td align="right"><?php echo h($row['precioLista']); ?></td>
                                                    <td align="right">
                                                        <?php echo h($row['precioOferta']); ?>
                                                        <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#ofertaModal" data-id="<?php echo h($row['id']);?>" data-nombre="<?php echo h($row['titulo']);?>" data-lista="<?php echo h($row['precioLista']);?>" data-reposicion="<?php echo h($row['precioCosto']);?>" data-oferta="<?php echo h($row['precioOferta']);?>">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                    </td>
                                                    <td><?php echo h($row['fechaPrecio']); ?></td>
                                                    <td align="right"><?php echo h($row['porcentajeDiferencia']); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
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

        <?php include 'ofertaModal.php'; ?>

        <script>
        $('#ofertaModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var nombre = applicant.data('nombre');
            var lista = applicant.data('lista');
            var reposicion = applicant.data('reposicion');
            var oferta = applicant.data('oferta');
            var modal = $(this);
            modal.find('input[name="ofertaProductoId"]').val(id);
            modal.find('input[name="returnTo"]').val('activas.php');
            modal.find('input[name="ofertaProductoNombre"]').val(nombre);
            modal.find('input[name="ofertaPrecioLista"]').val(lista);
            modal.find('input[name="ofertaPrecioReposicion"]').val(reposicion);
            modal.find('input[name="ofertaPrecioSugerido"]').val(oferta);
            modal.find('input[name="ofertaPrecio"]').val(oferta);
            modal.find('a.btn-secondary').attr('href', 'activas.php');
        });
        </script>
    </body>
</html>
