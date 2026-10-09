<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($rol !== 0) {
    http_response_code(403);
    echo 'No autorizado';
    exit;
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
        <title>Faltantes Fudo</title>
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
            .faltantes-resumen {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .faltantes-resumen-card {
                border: 1px solid #dee2e6;
                border-radius: .25rem;
                background: #fff;
                padding: .75rem 1rem;
            }

            .faltantes-resumen-card span {
                display: block;
                color: #6c757d;
                font-size: .8rem;
                margin-bottom: .25rem;
            }

            .faltantes-resumen-card strong {
                display: block;
                color: #212529;
                font-size: 1.35rem;
                line-height: 1.2;
            }

            .faltantes-filtros {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                align-items: center;
                margin-bottom: 1rem;
            }

            .faltantes-badge {
                display: inline-block;
                border-radius: .25rem;
                padding: .2rem .45rem;
                font-size: .78rem;
                font-weight: 600;
            }

            .faltantes-badge-definicion {
                color: #155724;
                background: #d4edda;
                border: 1px solid #c3e6cb;
            }

            .faltantes-badge-minimo {
                color: #856404;
                background: #fff3cd;
                border: 1px solid #ffeeba;
            }

            .faltantes-badge-stock {
                color: #721c24;
                background: #f8d7da;
                border: 1px solid #f5c6cb;
            }

            .faltantes-badge-control {
                color: #383d41;
                background: #e2e3e5;
                border: 1px solid #d6d8db;
            }

            .faltantes-entidad {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                white-space: nowrap;
            }
        </style>

        <script>
            $(document).ready(function() {
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };

                var table = null;

                var actualizarResumen = function(resumen) {
                    resumen = resumen || {};
                    $('#resumenTotal').text(resumen.total || 0);
                    $('#resumenProductos').text(resumen.productos || 0);
                    $('#resumenIngredientes').text(resumen.ingredientes || 0);
                    $('#resumenSinDefinicion').text(resumen.sinDefinicion || 0);
                    $('#resumenDebajoMinimo').text(resumen.debajoMinimo || 0);
                    $('#resumenSinStock').text(resumen.sinStock || 0);
                    $('#resumenSinControlStock').text(resumen.sinControlStock || 0);
                };

                var etiquetaEntidad = function(entidad) {
                    var icono = entidad === 'Ingrediente' ? 'fa-flask' : 'fa-box';
                    return '<span class="faltantes-entidad"><i class="fa fa-fw ' + icono + '"></i>' + escapeHtml(entidad) + '</span>';
                };

                var etiquetaEstado = function(estado) {
                    if (estado === 'sin_control_stock') {
                        return '<span class="faltantes-badge faltantes-badge-control">Sin Control de Stock</span>';
                    }

                    if (estado === 'sin_stock') {
                        return '<span class="faltantes-badge faltantes-badge-stock">Sin Stock</span>';
                    }

                    if (estado === 'debajo_minimo') {
                        return '<span class="faltantes-badge faltantes-badge-minimo">Debajo del minimo</span>';
                    }

                    return '<span class="faltantes-badge faltantes-badge-definicion">Sin definicion</span>';
                };

                $.fn.dataTable.ext.search.push(function(settings, searchData, index, rowData) {
                    if (settings.nTable.id !== 'tbFaltantesFudo') {
                        return true;
                    }

                    var entidad = $('#filtroEntidad').val();
                    var mostrarSinDefinicion = $('#filtroSinDefinicion').is(':checked');
                    var mostrarDebajoMinimo = $('#filtroDebajoMinimo').is(':checked');
                    var mostrarSinStock = $('#filtroSinStock').is(':checked');
                    var mostrarSinControlStock = $('#filtroSinControlStock').is(':checked');
                    var busqueda = $.trim($('#filtroBusqueda').val()).toLowerCase();

                    if (entidad !== '' && rowData.entidad !== entidad) {
                        return false;
                    }

                    if (rowData.estado === 'sin_definicion' && !mostrarSinDefinicion) {
                        return false;
                    }

                    if (rowData.estado === 'debajo_minimo' && !mostrarDebajoMinimo) {
                        return false;
                    }

                    if (rowData.estado === 'sin_stock' && !mostrarSinStock) {
                        return false;
                    }

                    if (rowData.estado === 'sin_control_stock' && !mostrarSinControlStock) {
                        return false;
                    }

                    if (busqueda !== '') {
                        var texto = [
                            rowData.nombre,
                            rowData.categoria,
                            rowData.unidad,
                            rowData.motivo
                        ].join(' ').toLowerCase();

                        if (texto.indexOf(busqueda) === -1) {
                            return false;
                        }
                    }

                    return true;
                });

                table = $('#tbFaltantesFudo').DataTable({
                    ajax: {
                        url: '../bff/inventario/fudo-faltantes.php',
                        dataSrc: function(response) {
                            actualizarResumen(response.data.resumen);
                            return response.data.items;
                        },
                        error: function(xhr) {
                            var mensaje = 'No se pudo cargar el reporte de Fudo.';
                            if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.codigo) {
                                mensaje += ' ' + xhr.responseJSON.error.codigo;
                            }
                            $('#mensajeErrorFudo').text(mensaje).removeClass('d-none');
                        }
                    },
                    processing: true,
                    pageLength: 50,
                    lengthMenu: [[25, 50, 75, 100, 150, -1], [25, 50, 75, 100, 150, "All"]],
                    columns: [
                        { data: 'entidad', render: etiquetaEntidad },
                        { data: 'nombre', render: escapeHtml },
                        { data: 'categoria', render: escapeHtml },
                        { data: 'unidad', render: escapeHtml },
                        { data: 'stock', className: 'text-right', render: escapeHtml },
                        { data: 'stockMinimo', className: 'text-right', render: escapeHtml },
                        { data: 'controlStock', className: 'text-center', render: escapeHtml },
                        { data: 'motivo', render: escapeHtml },
                        {
                            data: 'estado',
                            className: 'text-center',
                            render: etiquetaEstado
                        }
                    ],
                    createdRow: function(row, data) {
                        if (data.estado === 'sin_control_stock') {
                            $(row).addClass('table-secondary');
                        } else if (data.estado === 'sin_stock') {
                            $(row).addClass('table-danger');
                        } else if (data.estado === 'debajo_minimo') {
                            $(row).addClass('table-warning');
                        } else {
                            $(row).addClass('table-success');
                        }
                    },
                    order: [[8, 'asc'], [1, 'asc']]
                });

                $('#filtroEntidad, #filtroSinDefinicion, #filtroDebajoMinimo, #filtroSinStock, #filtroSinControlStock').on('change', function() {
                    table.draw();
                });

                $('#filtroBusqueda').on('keyup change', function() {
                    table.draw();
                });

                $('#btnRecargarFudo').on('click', function() {
                    $('#mensajeErrorFudo').addClass('d-none').text('');
                    table.ajax.reload();
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
                        <h1 class="mt-4">Faltantes Fudo</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="control.php">Inventario</a></li>
                            <li class="breadcrumb-item active">Faltantes Fudo</li>
                        </ol>

                        <div id="mensajeErrorFudo" class="alert alert-warning d-none"></div>

                        <div class="faltantes-resumen">
                            <div class="faltantes-resumen-card">
                                <span>Total</span>
                                <strong id="resumenTotal">0</strong>
                            </div>
                            <div class="faltantes-resumen-card">
                                <span>Productos</span>
                                <strong id="resumenProductos">0</strong>
                            </div>
                            <div class="faltantes-resumen-card">
                                <span>Ingredientes</span>
                                <strong id="resumenIngredientes">0</strong>
                            </div>
                            <div class="faltantes-resumen-card">
                                <span>Sin definicion</span>
                                <strong id="resumenSinDefinicion">0</strong>
                            </div>
                            <div class="faltantes-resumen-card">
                                <span>Debajo del minimo</span>
                                <strong id="resumenDebajoMinimo">0</strong>
                            </div>
                            <div class="faltantes-resumen-card">
                                <span>Sin stock</span>
                                <strong id="resumenSinStock">0</strong>
                            </div>
                            <div class="faltantes-resumen-card">
                                <span>Sin Control de Stock</span>
                                <strong id="resumenSinControlStock">0</strong>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Productos e ingredientes
                            </div>
                            <div class="card-body">
                                <div class="faltantes-filtros">
                                    <div class="form-group mb-0">
                                        <label for="filtroEntidad">Entidad</label>
                                        <select id="filtroEntidad" class="form-control">
                                            <option value="">Todas</option>
                                            <option value="Producto">Productos</option>
                                            <option value="Ingrediente">Ingredientes</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 flex-grow-1">
                                        <label for="filtroBusqueda">Buscar</label>
                                        <input type="search" id="filtroBusqueda" class="form-control" placeholder="Nombre, categoria o motivo">
                                    </div>
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="filtroSinDefinicion" checked>
                                        <label class="form-check-label" for="filtroSinDefinicion">Sin definicion</label>
                                    </div>
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="filtroDebajoMinimo" checked>
                                        <label class="form-check-label" for="filtroDebajoMinimo">Debajo del minimo</label>
                                    </div>
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="filtroSinStock">
                                        <label class="form-check-label" for="filtroSinStock">Sin stock</label>
                                    </div>
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="filtroSinControlStock">
                                        <label class="form-check-label" for="filtroSinControlStock">Sin Control de Stock</label>
                                    </div>
                                    <button type="button" id="btnRecargarFudo" class="btn btn-outline-primary mt-4">
                                        <i class="fa fa-fw fa-rotate"></i> Recargar
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tbFaltantesFudo">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Entidad</th>
                                                <th class="text-center">Nombre</th>
                                                <th class="text-center">Categoria</th>
                                                <th class="text-center">Unidad</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-center">Minimo</th>
                                                <th class="text-center">Control Stock</th>
                                                <th class="text-center">Motivo</th>
                                                <th class="text-center">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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
    </body>
</html>
