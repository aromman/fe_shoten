<?php
// Initialize the session
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
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
        <title>Control</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
        <style>
            .control-filtros-principales {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                align-items: flex-end;
                margin-bottom: .5rem;
            }

            .control-filtro {
                flex: 1 1 240px;
                min-width: 0;
                position: relative;
            }

            .control-filtro .bootstrap-select,
            .control-filtro .bootstrap-select > .dropdown-toggle,
            .control-filtro .form-control {
                width: 100% !important;
            }

            .control-filtro .bootstrap-select .dropdown-menu {
                min-width: 100% !important;
                max-width: 100%;
            }

            .control-filtro .bootstrap-select .dropdown-menu .dropdown-item {
                white-space: normal;
            }

            .control-filtros-stock {
                display: flex;
                gap: 1rem;
                align-items: center;
                margin-bottom: 1rem;
            }

            .control-acciones-masivas {
                display: flex;
                flex-wrap: wrap;
                gap: .5rem;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1rem;
            }

            .control-acciones-masivas-botones {
                display: flex;
                flex-wrap: wrap;
                gap: .5rem;
            }

            .control-seleccion-contador {
                color: #6c757d;
                font-size: .875rem;
            }

            .control-resumen-stock {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .control-resumen-card {
                border: 1px solid #dee2e6;
                border-radius: .25rem;
                background: #fff;
                padding: .75rem 1rem;
            }

            .control-resumen-card span {
                display: block;
                color: #6c757d;
                font-size: .8rem;
                margin-bottom: .25rem;
            }

            .control-resumen-card strong {
                display: block;
                color: #212529;
                font-size: 1.25rem;
                line-height: 1.2;
            }

            .control-grilla-detalle {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 320px;
                gap: 1rem;
                align-items: start;
            }

            .control-grilla-panel {
                border: 1px solid #dee2e6;
                border-radius: .25rem;
                background: #f8f9fa;
                padding: .75rem;
            }

            .control-grilla-panel .table-responsive {
                margin-bottom: 0;
            }

            .control-detalle-producto {
                border: 1px solid #dee2e6;
                border-radius: .25rem;
                background: #fff;
                padding: 1rem;
                position: sticky;
                top: 1rem;
            }

            .control-detalle-producto h5 {
                font-size: 1rem;
                margin-bottom: 0;
            }

            .control-detalle-header {
                display: flex;
                gap: .75rem;
                align-items: flex-start;
                justify-content: space-between;
                margin-bottom: .75rem;
            }

            .control-detalle-header .btn {
                flex: 0 0 auto;
            }

            .control-detalle-campo {
                border-top: 1px solid #f1f3f5;
                padding: .5rem 0;
            }

            .control-detalle-campo:first-child {
                border-top: 0;
                padding-top: 0;
            }

            .control-detalle-campo span {
                display: block;
                color: #6c757d;
                font-size: .78rem;
            }

            .control-detalle-campo strong {
                display: block;
                color: #212529;
                font-size: .95rem;
                overflow-wrap: anywhere;
            }

            .control-detalle-acciones {
                display: flex;
                gap: .5rem;
                align-items: center;
                margin-top: 1rem;
            }

            .control-detalle-acciones .btn {
                position: relative;
                z-index: 1;
            }

            #tbProductos tbody tr {
                cursor: pointer;
                height: 48px;
            }

            #tbProductos tbody tr.control-fila-seleccionada {
                outline: 2px solid #0d6efd;
                outline-offset: -2px;
            }

            @media (max-width: 991.98px) {
                .control-grilla-detalle {
                    grid-template-columns: 1fr;
                }

                .control-detalle-producto {
                    position: static;
                }
            }
        </style>

        <script>
            $(document).ready(function(e) {
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };
                var table = null;
                var productoSeleccionadoId = null;
                var productoDetalleActual = null;
                var detalleEditando = false;
                var productosSeleccionados = {};
                var formatoMoneda = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    maximumFractionDigits: 0
                });

                var actualizarResumen = function() {
                    if (!table) {
                        return;
                    }

                    var cantidadRegistros = 0;
                    var stockTotal = 0;
                    var costoTotal = 0;

                    table.rows({ filter: 'applied' }).every(function() {
                        var producto = this.data();
                        var stock = parseInt(producto.stock, 10) || 0;
                        var precioCosto = parseFloat(producto.precioCosto) || 0;

                        cantidadRegistros++;
                        stockTotal += stock;
                        costoTotal += stock * precioCosto;
                    });

                    $('#resumenRegistrosSeleccionados').text(cantidadRegistros);
                    $('#resumenStockTotal').text(stockTotal);
                    $('#resumenCostoTotal').text(formatoMoneda.format(costoTotal));
                };

                var textoEstado = function(estado) {
                    if (estado === 'noActivo') {
                        return 'No Activo';
                    }
                    if (estado === 'agotado') {
                        return 'Agotado';
                    }
                    if (estado === 'validado') {
                        return 'Validado';
                    }
                    return 'Disponible';
                };

                var obtenerIdsSeleccionados = function() {
                    return Object.keys(productosSeleccionados);
                };

                var actualizarAccionesMasivas = function() {
                    var cantidadSeleccionada = obtenerIdsSeleccionados().length;
                    var haySeleccion = cantidadSeleccionada > 0;

                    $('#btnValidarSeleccionados, #btnSinStockSeleccionados').prop('disabled', !haySeleccion);
                    $('#seleccionProductosCantidad').text(cantidadSeleccionada);
                    $('#checkSeleccionarTodos').prop('checked', false);

                    if (!table) {
                        return;
                    }

                    var visibles = 0;
                    var visiblesSeleccionados = 0;
                    table.rows({ filter: 'applied' }).every(function() {
                        var producto = this.data();
                        if (!producto) {
                            return;
                        }

                        visibles++;
                        if (productosSeleccionados[String(producto.id)]) {
                            visiblesSeleccionados++;
                        }
                    });

                    $('#checkSeleccionarTodos')
                        .prop('checked', visibles > 0 && visibles === visiblesSeleccionados)
                        .prop('indeterminate', visiblesSeleccionados > 0 && visiblesSeleccionados < visibles);
                };

                var actualizarDetalleSeleccion = function() {
                    var idsSeleccionados = obtenerIdsSeleccionados();
                    if (idsSeleccionados.length > 1) {
                        productoSeleccionadoId = null;
                        productoDetalleActual = null;
                        detalleEditando = false;
                        $('#detalleProductoTitulo').text('Producto');
                        $('#detalleProductoEditar').addClass('d-none').attr('href', '#');
                        $('#detalleProductoContenido').empty();
                        return;
                    }

                    if (idsSeleccionados.length !== 1 || !table) {
                        productoSeleccionadoId = null;
                        productoDetalleActual = null;
                        detalleEditando = false;
                        mostrarDetalleProducto(null);
                        return;
                    }

                    var productoSeleccionado = null;
                    table.rows().every(function() {
                        var producto = this.data();
                        if (producto && String(producto.id) === idsSeleccionados[0]) {
                            productoSeleccionado = producto;
                        }
                    });

                    productoSeleccionadoId = productoSeleccionado ? productoSeleccionado.id : null;
                    productoDetalleActual = productoSeleccionado;
                    detalleEditando = false;
                    mostrarDetalleProducto(productoSeleccionado);
                };

                var campoDetalle = function(etiqueta, valor) {
                    return '' +
                        '<div class="control-detalle-campo">' +
                            '<span>' + escapeHtml(etiqueta) + '</span>' +
                            '<strong>' + escapeHtml(valor || '-') + '</strong>' +
                        '</div>';
                };

                var campoDetalleInput = function(etiqueta, nombre, valor, step) {
                    return '' +
                        '<div class="control-detalle-campo">' +
                            '<label for="' + escapeHtml(nombre) + '">' + escapeHtml(etiqueta) + '</label>' +
                            '<input type="number" class="form-control form-control-sm" id="' + escapeHtml(nombre) + '" name="' + escapeHtml(nombre) + '" value="' + escapeHtml(valor) + '" min="0" step="' + escapeHtml(step) + '" required>' +
                        '</div>';
                };

                var mostrarDetalleProducto = function(producto) {
                    if (!producto) {
                        productoDetalleActual = null;
                        detalleEditando = false;
                        $('#detalleProductoTitulo').text('Producto');
                        $('#detalleProductoEditar').addClass('d-none').attr('href', '#');
                        $('#detalleProductoContenido').html(
                            '<p class="text-muted mb-0">Seleccione un producto de la grilla.</p>'
                        );
                        return;
                    }

                    productoDetalleActual = producto;
                    $('#detalleProductoTitulo').text(producto.tituloCompleto || 'Producto');
                    $('#detalleProductoEditar').toggleClass('d-none', detalleEditando).attr('href', '#');

                    if (detalleEditando) {
                        $('#detalleProductoContenido').html(
                            '<div id="formEditarDetalleProducto">' +
                                campoDetalle('Sku', producto.sku) +
                                campoDetalleInput('Stock', 'detalleStock', producto.stock, '1') +
                                campoDetalleInput('Precio de Venta', 'detallePrecio', producto.precio, '.01') +
                                campoDetalle('Precio de Costo', producto.precioCostoEtiqueta) +
                                campoDetalle('Editorial', producto.editorial) +
                                '<div class="control-detalle-acciones">' +
                                    '<button type="button" id="detalleProductoAceptar" class="btn btn-primary btn-sm">Aceptar</button>' +
                                    '<button type="button" id="detalleProductoCancelar" class="btn btn-secondary btn-sm">Cancelar</button>' +
                                '</div>' +
                            '</div>'
                        );
                        return;
                    }

                    $('#detalleProductoContenido').html(
                        campoDetalle('Sku', producto.sku) +
                        campoDetalle('Stock', producto.stockEtiqueta) +
                        campoDetalle('Precio de Venta', producto.precioEtiqueta) +
                        campoDetalle('Precio de Costo', producto.precioCostoEtiqueta) +
                        campoDetalle('Editorial', producto.editorial) +
                        campoDetalle('Proveedor', producto.proveedor) +
                        campoDetalle('Serie', producto.serie) +
                        campoDetalle('Formato', producto.formato) +
                        campoDetalle('Estado', textoEstado(producto.estadoStock)) +
                        campoDetalle('Actualizacion Stock', producto.fechaActualizacionStock)
                    );
                };

                var marcarFilaSeleccionada = function() {
                    $('#tbProductos tbody tr').removeClass('control-fila-seleccionada');
                    if (!productoSeleccionadoId || !table) {
                        return;
                    }

                    var seleccionVisible = false;
                    table.rows({ filter: 'applied' }).every(function() {
                        var producto = this.data();
                        if (String(producto.id) === String(productoSeleccionadoId)) {
                            $(this.node()).addClass('control-fila-seleccionada');
                            seleccionVisible = true;
                        }
                    });

                    if (!seleccionVisible) {
                        productoSeleccionadoId = null;
                        productoDetalleActual = null;
                        detalleEditando = false;
                        mostrarDetalleProducto(null);
                    }
                };

                var marcarChecksSeleccionados = function() {
                    $('#tbProductos tbody .check-producto').each(function() {
                        var id = $(this).val();
                        $(this).prop('checked', !!productosSeleccionados[String(id)]);
                    });
                    actualizarAccionesMasivas();
                };

                var cargarOpciones = function(selector, opciones) {
                    var $select = $(selector);
                    var valorActual = $select.val();

                    $select.find('option:not(:first)').remove();
                    $.each(opciones || [], function(index, opcion) {
                        $select.append(
                            '<option value="' + escapeHtml(opcion.id) + '">' + escapeHtml(opcion.nombre) + '</option>'
                        );
                    });

                    $select.val(valorActual || '');
                    if ($select.selectpicker) {
                        $select.selectpicker('refresh');
                    }
                };

                $.fn.dataTable.ext.search.push(function(settings, searchData, index, rowData) {
                    if (settings.nTable.id !== 'tbProductos') {
                        return true;
                    }

                    var proveedor = $('#filtroProveedor').val();
                    var editorial = $('#filtroEditorial').val();
                    var formato = $('#filtroFormato').val();
                    var serie = $('#filtroSerie').val();
                    var busqueda = $.trim($('#filtroBusqueda').val()).toLowerCase();
                    var mostrarDisponibles = $('#filtroDisponibles').is(':checked');
                    var mostrarAgotados = $('#filtroAgotados').is(':checked');
                    var mostrarValidados = $('#filtroValidados').is(':checked');
                    var mostrarNoActivos = $('#filtroNoActivos').is(':checked');
                    var estado = rowData.estadoStock;

                    if (proveedor && String(rowData.proveedorId) !== proveedor) {
                        return false;
                    }

                    if (editorial && String(rowData.editorialId) !== editorial) {
                        return false;
                    }

                    if (formato && String(rowData.formatoId) !== formato) {
                        return false;
                    }

                    if (serie && String(rowData.serieId) !== serie) {
                        return false;
                    }

                    if (estado === 'disponible' && !mostrarDisponibles) {
                        return false;
                    }

                    if (estado === 'agotado' && !mostrarAgotados) {
                        return false;
                    }

                    if (estado === 'validado' && !mostrarValidados) {
                        return false;
                    }

                    if (estado === 'noActivo' && !mostrarNoActivos) {
                        return false;
                    }

                    if (busqueda !== '') {
                        var texto = [
                            rowData.sku,
                            rowData.tituloCompleto,
                            rowData.serie,
                            rowData.proveedor,
                            rowData.formato
                        ].join(' ').toLowerCase();

                        if (texto.indexOf(busqueda) === -1) {
                            return false;
                        }
                    }

                    return true;
                });

                table = $('#tbProductos').DataTable({
                    ajax: {
                        url: '../bff/inventario/control.php',
                        dataSrc: function(response) {
                            cargarOpciones('#filtroProveedor', response.data.filtros.proveedores);
                            cargarOpciones('#filtroEditorial', response.data.filtros.editoriales);
                            cargarOpciones('#filtroFormato', response.data.filtros.formatos);
                            cargarOpciones('#filtroSerie', response.data.filtros.series);

                            return response.data.productos;
                        },
                        error: function() {
                            $('#tbProductos tbody').html(
                                '<tr><td colspan="6" class="text-center text-danger">No se pudo cargar el control de existencias.</td></tr>'
                            );
                        }
                    },
                    processing: true,
                    dom: 'rti',
                    paging: false,
                    scrollY: '480px',
                    scrollCollapse: true,
                    columns: [
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(id) {
                                return '<input type="checkbox" class="check-producto" value="' + escapeHtml(id) + '" aria-label="Seleccionar producto">';
                            }
                        },
                        { data: 'tituloCompleto', render: escapeHtml },
                        { data: 'stockEtiqueta', render: escapeHtml },
                        { data: 'precioCostoEtiqueta', render: escapeHtml },
                        { data: 'precioEtiqueta', render: escapeHtml },
                        {
                            data: 'acciones',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(acciones, type, row) {
                                if (row.estadoStock === 'validado') {
                                    return '';
                                }

                                return '<a href="' + escapeHtml(acciones.validar) + '" class="text-primary" title="Validar"><i class="fa fa-fw fa-thumbs-up"></i></a>';
                            }
                        }
                    ],
                    createdRow: function(row, data) {
                        if (data.claseFila) {
                            $(row).addClass(data.claseFila);
                        }
                    },
                    order: [[0, 'asc']],
                    lengthMenu: [[50, 75, 100, 150, -1], [50, 75, 100, 150, "All"]]
                });

                table.on('draw', function() {
                    actualizarResumen();
                    marcarChecksSeleccionados();
                    marcarFilaSeleccionada();
                });
                actualizarResumen();
                actualizarAccionesMasivas();

                $('#tbProductos tbody').on('change', '.check-producto', function() {
                    var id = String($(this).val());
                    if ($(this).is(':checked')) {
                        productosSeleccionados[id] = true;
                    } else {
                        delete productosSeleccionados[id];
                    }

                    actualizarDetalleSeleccion();
                    table.draw(false);
                });

                $('#tbProductos tbody').on('click', 'tr', function(e) {
                    if ($(e.target).closest('a, input, button').length > 0) {
                        return;
                    }

                    var producto = table.row(this).data();
                    if (!producto) {
                        return;
                    }

                    var id = String(producto.id);
                    if (productosSeleccionados[id]) {
                        delete productosSeleccionados[id];
                    } else {
                        productosSeleccionados[id] = true;
                    }

                    actualizarDetalleSeleccion();
                    table.draw(false);
                });

                $('#filtroProveedor, #filtroEditorial, #filtroFormato, #filtroSerie, #filtroDisponibles, #filtroAgotados, #filtroValidados, #filtroNoActivos').on('change', function() {
                    table.draw();
                });

                $('#filtroBusqueda').on('keyup change', function() {
                    table.draw();
                });

                $('#checkSeleccionarTodos').on('change', function() {
                    var seleccionar = $(this).is(':checked');
                    table.rows({ filter: 'applied' }).every(function() {
                        var producto = this.data();
                        if (!producto) {
                            return;
                        }

                        if (seleccionar) {
                            productosSeleccionados[String(producto.id)] = true;
                        } else {
                            delete productosSeleccionados[String(producto.id)];
                        }
                    });

                    actualizarDetalleSeleccion();
                    table.draw(false);
                });

                $('#detalleProductoEditar').on('click', function(e) {
                    e.preventDefault();
                    if (!productoDetalleActual) {
                        return;
                    }

                    detalleEditando = true;
                    mostrarDetalleProducto(productoDetalleActual);
                });

                $('#detalleProductoContenido').on('click', '#detalleProductoCancelar', function() {
                    detalleEditando = false;
                    mostrarDetalleProducto(productoDetalleActual);
                });

                $('#detalleProductoContenido').on('click', '#detalleProductoAceptar', function(e) {
                    e.preventDefault();
                    if (!productoDetalleActual) {
                        return;
                    }

                    var stock = $.trim($('#detalleStock').val());
                    var precio = $.trim($('#detallePrecio').val());
                    if (stock === '' || precio === '' || isNaN(Number(stock)) || isNaN(Number(precio)) || Number(stock) < 0 || Number(precio) < 0) {
                        alert('Ingrese stock y precio validos.');
                        return;
                    }

                    $('#formEditarDetalleProducto :input').prop('disabled', true);
                    $.ajax({
                        url: '../bff/inventario/actions.php',
                        method: 'POST',
                        data: {
                            accion: 'editar',
                            id: productoDetalleActual.id,
                            stock: stock,
                            precio: precio
                        },
                        success: function() {
                            var idEditado = String(productoDetalleActual.id);
                            detalleEditando = false;
                            table.ajax.reload(function() {
                                productoSeleccionadoId = idEditado;
                                productosSeleccionados = {};
                                productosSeleccionados[idEditado] = true;
                                actualizarDetalleSeleccion();
                                actualizarAccionesMasivas();
                            }, false);
                        },
                        error: function() {
                            alert('No se pudo actualizar el producto.');
                            $('#formEditarDetalleProducto :input').prop('disabled', false);
                        }
                    });
                });

                var ejecutarAccionMasiva = function(accion) {
                    var ids = obtenerIdsSeleccionados();
                    if (ids.length === 0) {
                        return;
                    }

                    var etiquetaProductos = ids.length === 1 ? 'producto seleccionado' : 'productos seleccionados';
                    if (!confirm('Esta seguro que va a actualizar estos ' + ids.length + ' ' + etiquetaProductos + '?')) {
                        return;
                    }

                    $('#btnValidarSeleccionados, #btnSinStockSeleccionados').prop('disabled', true);

                    $.ajax({
                        url: '../bff/inventario/actions.php',
                        method: 'POST',
                        data: {
                            accion: accion,
                            ids: ids
                        },
                        success: function() {
                            productosSeleccionados = {};
                            productoSeleccionadoId = null;
                            mostrarDetalleProducto(null);
                            table.ajax.reload(function() {
                                actualizarAccionesMasivas();
                            }, false);
                        },
                        error: function() {
                            alert('No se pudo aplicar la accion seleccionada.');
                            actualizarAccionesMasivas();
                        }
                    });
                };

                $('#btnValidarSeleccionados').on('click', function() {
                    ejecutarAccionMasiva('validar');
                });

                $('#btnSinStockSeleccionados').on('click', function() {
                    ejecutarAccionMasiva('sin_stock');
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
                        <h1 class="mt-4">Control Existencias</h1>
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
                                <form id="formProductos" method="post" onSubmit="return false;">
                                    <div class="control-filtros-principales">
                                        <div class="form-group control-filtro">
                                            <label for="filtroProveedor">Proveedor</label>
                                            <select id="filtroProveedor" class="form-control selectpicker" data-live-search="true" data-size="10">
                                                <option value="">Todos</option>
                                            </select>
                                        </div>
                                        <div class="form-group control-filtro">
                                            <label for="filtroEditorial">Editorial</label>
                                            <select id="filtroEditorial" class="form-control selectpicker" data-live-search="true" data-size="10">
                                                <option value="">Todas</option>
                                            </select>
                                        </div>
                                        <div class="form-group control-filtro">
                                            <label for="filtroFormato">Formato</label>
                                            <select id="filtroFormato" class="form-control selectpicker" data-live-search="true" data-size="10">
                                                <option value="">Todos</option>
                                            </select>
                                        </div>
                                        <div class="form-group control-filtro">
                                            <label for="filtroSerie">Serie</label>
                                            <select id="filtroSerie" class="form-control selectpicker" data-live-search="true" data-size="10">
                                                <option value="">Todas</option>
                                            </select>
                                        </div>
                                        <div class="form-group control-filtro">
                                            <label for="filtroBusqueda">Buscar</label>
                                            <input type="search" id="filtroBusqueda" class="form-control" placeholder="Sku, titulo, proveedor o formato">
                                        </div>
                                    </div>
                                    <div class="control-filtros-stock">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filtroDisponibles" checked>
                                            <label class="form-check-label" for="filtroDisponibles">Disponibles</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filtroAgotados">
                                            <label class="form-check-label" for="filtroAgotados">Agotados</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filtroValidados">
                                            <label class="form-check-label" for="filtroValidados">Validado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filtroNoActivos">
                                            <label class="form-check-label" for="filtroNoActivos">No Activo</label>
                                        </div>
                                    </div>
                                    <div class="control-acciones-masivas">
                                        <div class="control-acciones-masivas-botones">
                                            <button type="button" id="btnValidarSeleccionados" class="btn btn-primary btn-sm" disabled>
                                                <i class="fa fa-fw fa-thumbs-up"></i> Validar
                                            </button>
                                            <button type="button" id="btnSinStockSeleccionados" class="btn btn-outline-danger btn-sm" disabled>
                                                <i class="fa fa-fw fa-box-open"></i> Sin stock
                                            </button>
                                        </div>
                                        <span class="control-seleccion-contador">
                                            <strong id="seleccionProductosCantidad">0</strong> seleccionados
                                        </span>
                                    </div>
                                    <div class="control-resumen-stock">
                                        <div class="control-resumen-card">
                                            <span>Registros Seleccionados</span>
                                            <strong id="resumenRegistrosSeleccionados">0</strong>
                                        </div>
                                        <div class="control-resumen-card">
                                            <span>Stock Total</span>
                                            <strong id="resumenStockTotal">0</strong>
                                        </div>
                                        <div class="control-resumen-card">
                                            <span>Costo Total</span>
                                            <strong id="resumenCostoTotal">$ 0</strong>
                                        </div>
                                    </div>

                                    <div class="control-grilla-detalle">
                                        <div class="control-grilla-panel">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-responsive" id="tbProductos">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">
                                                                <input type="checkbox" id="checkSeleccionarTodos" aria-label="Seleccionar todos los productos visibles">
                                                            </th>
                                                            <th class="text-center">Producto</th>
                                                            <th class="text-center">Stock</th>
                                                            <th class="text-center">Precio de Costo</th>
                                                            <th class="text-center">Precio de Venta</th>
                                                            <th class="text-center">Validar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <aside class="control-detalle-producto">
                                            <div class="control-detalle-header">
                                                <h5 id="detalleProductoTitulo">Producto</h5>
                                                <a id="detalleProductoEditar" href="#" class="btn btn-primary btn-sm d-none">
                                                    <i class="fa fa-fw fa-pencil"></i> Editar
                                                </a>
                                            </div>
                                            <div id="detalleProductoContenido">
                                                <p class="text-muted mb-0">Seleccione un producto de la grilla.</p>
                                            </div>
                                        </aside>
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
