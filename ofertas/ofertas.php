<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../bff/ofertas/view-model.php";

$ofertasViewModel = obtenerOfertasViewModel();
$ofertasProductos = $ofertasViewModel['productos'];
$editorialesConsignacion = $ofertasViewModel['editorialesConsignacion'];
$mediosPagoProveedor = $ofertasViewModel['mediosPagoProveedor'];
$porcentajeMaximoOferta = $ofertasViewModel['porcentajeMaximoOferta'];
$totRegistros = $ofertasViewModel['totales']['registros'];
$totCantidad = $ofertasViewModel['totales']['cantidad'];
$totMontoCompra = $ofertasViewModel['totales']['montoCompra'];
$totMontoVenta = $ofertasViewModel['totales']['montoVenta'];
$totOferta10 = $ofertasViewModel['totales']['oferta10'];
$totOferta20 = $ofertasViewModel['totales']['oferta20'];
$totOferta30 = $ofertasViewModel['totales']['oferta30'];
$totOferta40 = $ofertasViewModel['totales']['oferta40'];
$totOferta50 = $ofertasViewModel['totales']['oferta50'];
$totValorizado = $ofertasViewModel['totales']['valorizado'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
        <title>Posibles Ofertas</title>
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
                $('#tbOfertas').dataTable( {
                    "ordering": false,
                    "paging": false,
                    "info": false,
                    "dom": "<'row align-items-center mb-3'<'col-md-8 ofertas-summary'><'col-md-4'f>>rt",
                    "initComplete": function() {
                        $('.ofertas-summary').append($('#ofertasResumen'));
                    }
                });
            });


        </script>

        <style>
            .ofertas-summary-cards {
                display: flex;
                flex-direction: column;
                gap: .75rem;
            }
            .ofertas-summary-row {
                gap: .75rem;
            }
            .ofertas-summary-card {
                min-width: 150px;
                border: 1px solid rgba(0, 0, 0, .08);
                box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            }
            .ofertas-summary-card .card-body {
                min-height: 74px;
            }
            .ofertas-summary-icon {
                width: 36px;
                height: 36px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                flex: 0 0 36px;
                font-size: .72rem;
                font-weight: 700;
            }
            .ofertas-summary-label {
                font-size: .75rem;
                color: #6c757d;
                line-height: 1;
            }
            .ofertas-summary-value {
                font-size: 1rem;
                font-weight: 700;
                line-height: 1.2;
                white-space: nowrap;
            }
            .ofertas-criticality-10 {
                background-color: #e7f1ff;
                color: #0d6efd;
            }
            .ofertas-criticality-20 {
                background-color: #d1e7dd;
                color: #198754;
            }
            .ofertas-criticality-30 {
                background-color: #fff3cd;
                color: #997404;
            }
            .ofertas-criticality-40 {
                background-color: #ffe5d0;
                color: #cc5a00;
            }
            .ofertas-criticality-50 {
                background-color: #f8d7da;
                color: #dc3545;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-10 td {
                background-color: #f7fbff;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-20 td {
                background-color: #f5fbf8;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-30 td {
                background-color: #fffaf0;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-40 td {
                background-color: #fff4ea;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-50 td {
                background-color: #fff0f1;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-10 td:first-child {
                border-left: 4px solid #0d6efd;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-20 td:first-child {
                border-left: 4px solid #198754;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-30 td:first-child {
                border-left: 4px solid #ffc107;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-40 td:first-child {
                border-left: 4px solid #fd7e14;
            }
            #tbOfertas tbody tr.ofertas-row-criticality-50 td:first-child {
                border-left: 4px solid #dc3545;
            }
        </style>


    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Posibles Ofertas</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Analisis de posibles Ofertas - Porcentaje maximo de oferta <?php echo h($porcentajeMaximoOferta); ?>%
                            </div>
                            <div class="card-body">
                                <form id="formConsignaciones" method="post" onSubmit="return false;">
                                <div class="table-responsive">
                                <div id="ofertasResumen" class="ofertas-summary-cards">
                                    <div class="ofertas-summary-row d-flex flex-wrap">
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon bg-primary bg-opacity-10 text-primary me-2">
                                                    <i class="fa-solid fa-list"></i>
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Registros</div>
                                                    <div class="ofertas-summary-value"><?php echo h($totRegistros); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon bg-success bg-opacity-10 text-success me-2">
                                                    <i class="fa-solid fa-boxes-stacked"></i>
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Stock</div>
                                                    <div class="ofertas-summary-value"><?php echo h($totCantidad); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon bg-warning bg-opacity-10 text-warning me-2">
                                                    <i class="fa-solid fa-cart-shopping"></i>
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Total Compra</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totMontoCompra); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon bg-info bg-opacity-10 text-info me-2">
                                                    <i class="fa-solid fa-dollar-sign"></i>
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Total Venta</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totMontoVenta); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ofertas-summary-row d-flex flex-wrap">
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon ofertas-criticality-10 me-2">
                                                    10%
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Oferta 10%</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totOferta10); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon ofertas-criticality-20 me-2">
                                                    20%
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Oferta 20%</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totOferta20); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon ofertas-criticality-30 me-2">
                                                    30%
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Oferta 30%</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totOferta30); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon ofertas-criticality-40 me-2">
                                                    40%
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Oferta 40%</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totOferta40); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card ofertas-summary-card">
                                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                                <div class="ofertas-summary-icon ofertas-criticality-50 me-2">
                                                    50%
                                                </div>
                                                <div>
                                                    <div class="ofertas-summary-label">Oferta 50%</div>
                                                    <div class="ofertas-summary-value">$ <?php echo h($totOferta50); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table class="table table-striped table-bordered table-responsive" id="tbOfertas">
                                    <thead>
                                        <tr>
                                            <th class="text-center" rowspan="2">Titulo</th>
                                            <th class="text-center" colspan="3">Ultima Accion</th>
                                            <th class="text-center" rowspan="2">Cantidad</th>
                                            <th class="text-center" colspan="3">Precios</th>
                                            <th class="text-center" colspan="2">Oferta Sugerida</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Meses</th>
                                            <th class="text-center">Compra</th>
                                            <th class="text-center">Venta</th>
                                            <th class="text-center">Oferta</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (count($ofertasProductos) > 0) {
                                                foreach ($ofertasProductos as $row)
                                                {
                                                    $colId = $row['id'];
                                                    $colTitulo = $row['titulo'];
                                                    $colFechaUltimaAccion = $row['fechaUltimaAccion'];
                                                    $colTipoUltimaAccion = $row['tipoUltimaAccion'];
                                                    $colMesesSinAccion = $row['mesesSinAccion'];
                                                    $colCantidad = $row['cantidad'];
                                                    $colPrecioCompra = $row['precioCompra'];
                                                    $colPrecioCosto = $row['precioCosto'];
                                                    $colPrecioVenta = $row['precioVenta'];
                                                    $colPrecioOferta = $row['precioOferta'];
                                                    $porcentajeOferta = $row['porcentajeOferta'];
                                                    $criticidadOferta = $row['criticidadOferta'];
                                                    $colPrecioOfertaSugerido = $row['precioOfertaSugerido'];
                                        ?>

                                                <tr class="ofertas-row-criticality-<?php echo h($criticidadOferta); ?>">
                                                    <td><?php echo h($colTitulo); ?></td>
                                                    <td><?php echo h($colFechaUltimaAccion); ?></td>
                                                    <td><?php echo h($colTipoUltimaAccion); ?></td>
                                                    <td><?php echo h($colMesesSinAccion); ?></td>
                                                    <td align="right">
                                                        <?php echo h($colCantidad); ?>
                                                        <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#stockModal" data-id="<?php echo h($colId);?>" data-nombre="<?php echo h($colTitulo);?>" data-stock="<?php echo h($colCantidad);?>" ><i class="fa-solid fa-arrow-down"></i></a>
                                                    </td>
                                                    <td align="right"><?php echo h($colPrecioCompra); ?></td>
                                                    <td align="right"><?php echo h($colPrecioVenta); ?></td>
                                                    <td align="right">
                                                        <?php echo h($colPrecioOferta); ?>
                                                        <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#preciosModal" data-id="<?php echo h($colId);?>" data-nombre="<?php echo h($colTitulo);?>" data-compra="<?php echo h($colPrecioCosto);?>" data-lista="<?php echo h($colPrecioVenta);?>" data-oferta="<?php echo h($colPrecioOferta);?>" data-sugerido="<?php echo h($colPrecioOfertaSugerido);?>" ><i class="fa-solid fa-pen-to-square"></i></a>
                                                    </td>
                                                    <td align="right"><?php echo h($colPrecioOfertaSugerido); ?></td>
                                                    <td align="right"><?php echo h($porcentajeOferta); ?></td>
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
                                    <tfoot>
                                        <tr></tr>
                                        <tr>
                                            <td colspan="3">Totales</td>
                                            <td align="right">Cantidad</td>
                                            <td align="right"><?php echo h($totCantidad); ?></td>
                                            <td align="right">Valorizado</td>
                                            <td align="right"><?php echo h($totValorizado); ?></td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>

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


        <?php include 'stockModal.php'; ?>
        
        <script>
        $('#stockModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var nombre = applicant.data('nombre');
            var stock = applicant.data('stock');
            var modal = $(this);
            modal.find('input[name="stockProductoId"]').val(id);
            modal.find('input[name="stockProductoNombre"]').val(nombre);
            modal.find('input[name="stockCantidadTeorico"]').val(stock);
        });
        </script> 

        <?php include 'preciosModal.php'; ?>

        <script>
        $('#preciosModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var nombre = applicant.data('nombre');
            var compra = applicant.data('compra');
            var lista = applicant.data('lista');
            var oferta = applicant.data('oferta');
            var sugerido = applicant.data('sugerido');
            var modal = $(this);
            modal.find('input[name="preciosProductoId"]').val(id);
            modal.find('input[name="preciosProductoNombre"]').val(nombre);
            modal.find('input[name="preciosCompra"]').val(compra);
            modal.find('input[name="preciosLista"]').val(lista);
            modal.find('input[name="preciosOferta"]').val(oferta);
            modal.find('input[name="preciosOfertaSugerido"]').val(sugerido);
        });
        </script> 

    </body>
</html>
