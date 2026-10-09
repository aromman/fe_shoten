<?php
session_start();

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
        <title>Proveedores</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function() {
                $('.selectpicker').selectpicker();

                $('body').on('mousemove', function() {
                    $('[data-toggle="tooltip"]').tooltip();
                });

                function escapeHtml(value) {
                    return $('<div>').text(value == null ? '' : value).html();
                }

                function renderFechaProxima(fecha, diasHasta) {
                    var clasesPorDias = {
                        0: 'badge bg-danger',
                        2: 'badge bg-warning text-dark',
                        3: 'badge bg-success'
                    };

                    if (!fecha) {
                        return '';
                    }

                    if (clasesPorDias[diasHasta]) {
                        return '<span class="' + clasesPorDias[diasHasta] + '">' + escapeHtml(fecha) + '</span>';
                    }

                    return escapeHtml(fecha);
                }

                var proveedoresPorId = {};
                var proveedoresListado = [];

                function generarCronDesdeRepeticion($builder) {
                    var tipo = $builder.find('.recurrencia-tipo').val();

                    if (tipo === 'dias') {
                        var intervalo = parseInt($builder.find('.recurrencia-intervalo-dias').val(), 10);
                        if (!intervalo || intervalo < 1) {
                            intervalo = 1;
                        }
                        return '0 0 */' + intervalo + ' * *';
                    }

                    if (tipo === 'mensual') {
                        return '0 0 * * ' + $builder.find('.recurrencia-dia-mensual').val() + '#' + $builder.find('.recurrencia-ordinal').val();
                    }

                    if (tipo === 'mensual-dia') {
                        var diaMes = parseInt($builder.find('.recurrencia-dia-mes').val(), 10);
                        if (!diaMes || diaMes < 1) {
                            diaMes = 1;
                        }
                        if (diaMes > 31) {
                            diaMes = 31;
                        }
                        return '0 0 ' + diaMes + ' * *';
                    }

                    var dias = [];
                    $builder.find('.recurrencia-dia-semanal:checked').each(function() {
                        dias.push($(this).val());
                    });

                    return dias.length ? '0 0 * * ' + dias.join(',') : '';
                }

                function actualizarCronFormulario($form) {
                    $form.find('.recurrencia-builder').each(function() {
                        var $builder = $(this);
                        var cron = generarCronDesdeRepeticion($builder);
                        var target = $builder.data('target');
                        $form.find('input[name="' + target + '"]').val(cron);
                        $builder.find('.recurrencia-cron-preview').text(cron || 'Sin repeticion');
                    });
                }

                function actualizarVistaRepeticion($builder) {
                    var tipo = $builder.find('.recurrencia-tipo').val();
                    $builder.find('.recurrencia-semanal').toggle(tipo === 'semanal');
                    $builder.find('.recurrencia-dias').toggle(tipo === 'dias');
                    $builder.find('.recurrencia-mensual').toggle(tipo === 'mensual');
                    $builder.find('.recurrencia-mensual-dia').toggle(tipo === 'mensual-dia');
                    actualizarCronFormulario($builder.closest('form'));
                }

                function inicializarRepeticionFormulario($form) {
                    $form.find('.recurrencia-builder').each(function() {
                        var $builder = $(this);
                        $builder.find('.recurrencia-tipo').val('semanal');
                        $builder.find('.recurrencia-dia-semanal').prop('checked', false);
                        $builder.find('.recurrencia-dia-semanal[value="1"]').prop('checked', true);
                        $builder.find('.recurrencia-intervalo-dias').val('15');
                        $builder.find('.recurrencia-ordinal').val('1');
                        $builder.find('.recurrencia-dia-mensual').val('1');
                        $builder.find('.recurrencia-dia-mes').val('15');
                        actualizarVistaRepeticion($builder);
                    });
                }

                function cargarCronEnRepeticion($form, target, cron) {
                    var $builder = $form.find('.recurrencia-builder[data-target="' + target + '"]');
                    var partes = (cron || '').trim().split(/\s+/);

                    if (!$builder.length) {
                        return;
                    }

                    $form.find('input[name="' + target + '"]').val(cron || '');
                    $builder.find('.recurrencia-cron-preview').text(cron || 'Sin repeticion');

                    if (partes.length !== 5) {
                        return;
                    }

                    if (partes[2].indexOf('*/') === 0 && partes[4] === '*') {
                        $builder.find('.recurrencia-tipo').val('dias');
                        $builder.find('.recurrencia-intervalo-dias').val(partes[2].replace('*/', ''));
                    } else if (partes[2] === '*' && partes[4].indexOf('#') !== -1) {
                        var mensual = partes[4].split('#');
                        $builder.find('.recurrencia-tipo').val('mensual');
                        $builder.find('.recurrencia-dia-mensual').val(mensual[0]);
                        $builder.find('.recurrencia-ordinal').val(mensual[1]);
                    } else if (partes[2] !== '*' && partes[4] === '*') {
                        $builder.find('.recurrencia-tipo').val('mensual-dia');
                        $builder.find('.recurrencia-dia-mes').val(partes[2]);
                    } else if (partes[2] === '*') {
                        $builder.find('.recurrencia-tipo').val('semanal');
                        $builder.find('.recurrencia-dia-semanal').prop('checked', false);
                        $.each(partes[4].split(','), function(index, dia) {
                            $builder.find('.recurrencia-dia-semanal[value="' + dia + '"]').prop('checked', true);
                        });
                    }

                    actualizarVistaRepeticion($builder);
                }

                function pintarProveedores() {
                    var $tbody = $('#tbBody');
                    var busqueda = $('#buscarProveedor').val().toLowerCase();
                    $tbody.empty();
                    proveedoresPorId = {};

                    $.each(proveedoresListado, function(index, row) {
                        var nombre = row.nombre || '';
                        if (busqueda && nombre.toLowerCase().indexOf(busqueda) === -1) {
                            return;
                        }

                        proveedoresPorId[row.id] = row;
                        var origen = row.origen || 'local';
                        var claseOrigen = origen === 'fudo' ? 'proveedor-fila-fudo' : 'proveedor-fila-local';
                        var mensajeBorrado = origen === 'fudo'
                            ? 'Esta seguro de querer borrar este proveedor solo de la base local? No se borrara en Fudo.'
                            : 'Esta seguro de querer borrar este proveedor?';
                        var acciones =
                            '<a href="view-proveedor.php?forwardOk=proveedores.php&id=' + row.id + '" class="text-primary"><i class="fa fa-fw fa-eye"></i></a>' +
                            '<a href="javascript:;" class="text-primary editar-proveedor" data-id="' + row.id + '"><i class="fa fa-fw fa-pencil"></i></a>' +
                            '<a href="../delete-entity.php?entityName=proveedores&forwardOk=proveedores/proveedores.php&delId=' + row.id + '" class="text-danger" onClick="return confirm(\'' + mensajeBorrado + '\');"><i class="fa fa-fw fa-trash"></i></a>';
                        $tbody.append(
                            '<tr class="' + claseOrigen + '">' +
                                '<td>' + escapeHtml(row.nombre) + '</td>' +
                                '<td align="center"><span class="badge proveedor-origen-' + escapeHtml(origen) + '">' + escapeHtml(origen) + '</span></td>' +
                                '<td>' + escapeHtml(row.diaPedidoEtiqueta) + '</td>' +
                                '<td>' + renderFechaProxima(row.fechaProximoPedido, row.diasHastaProximoPedido) + '</td>' +
                                '<td>' + escapeHtml(row.diaEntregaEtiqueta) + '</td>' +
                                '<td>' + renderFechaProxima(row.fechaProximaEntrega, row.diasHastaProximaEntrega) + '</td>' +
                                '<td>' + escapeHtml(row.cantidadMinima) + '</td>' +
                                '<td>' + escapeHtml(row.montoMinimo) + '</td>' +
                                '<td align="center"><input class="form-check-input" type="checkbox" disabled ' + (row.activo ? 'checked' : '') + '></td>' +
                                '<td align="center">' +
                                    acciones +
                                '</td>' +
                            '</tr>'
                        );
                    });
                }

                function cargarProveedores() {
                    $.getJSON('../bff/proveedores/control.php', {
                        verInactivos: $('#verInactivos').is(':checked') ? '1' : '0'
                    }).done(function(response) {
                            proveedoresListado = response.data.proveedores || [];
                            pintarProveedores();
                        });
                }

                $("#addmoreproveedores").on("click", function() {
                    inicializarRepeticionFormulario($('#formNuevoProveedor'));
                    $('#modalNuevoProveedor').modal('show');
                });

                $(".cerrar-modal-proveedor").on("click", function() {
                    $(this).closest('.modal').modal('hide');
                });

                $("#tbBody").on("click", ".editar-proveedor", function() {
                    var proveedor = proveedoresPorId[$(this).data('id')];
                    var $form = $('#formEditarProveedor');

                    if (!proveedor) {
                        return;
                    }

                    $form[0].reset();
                    $form.find('input[name="id"]').val(proveedor.id);
                    $form.find('input[name="origen"]').val(proveedor.origen || 'local');
                    $form.find('input[name="nombre"]').val(proveedor.nombre);
                    $form.find('input[name="nombre"]').prop('readonly', (proveedor.origen || 'local') !== 'local');
                    $form.find('input[name="cantidadMinima"]').val(proveedor.cantidadMinima);
                    $form.find('input[name="montoMinimo"]').val(proveedor.montoMinimo);
                    $form.find('select[name="activo"]').val(proveedor.activo ? '1' : '0');
                    inicializarRepeticionFormulario($form);
                    cargarCronEnRepeticion($form, 'cronPedido', proveedor.cronPedido);
                    cargarCronEnRepeticion($form, 'cronEntrega', proveedor.cronEntrega);

                    $('#modalEditarProveedor').modal('show');
                });

                $("#formNuevoProveedor").on("submit", function() {
                    actualizarCronFormulario($(this));
                    $.ajax({
                        type: 'POST',
                        url: 'action-form-proveedores.ajax.php',
                        data: $(this).serialize(),
                        success: function(data) {
                            var a = data.split('|***|');
                            $('#mag').html(a[0]);
                            if (a[1] == "add") {
                                $('#modalNuevoProveedor').modal('hide');
                                $('#formNuevoProveedor')[0].reset();
                                inicializarRepeticionFormulario($('#formNuevoProveedor'));
                                cargarProveedores();
                            }
                        }
                    });
                });

                $("#formEditarProveedor").on("submit", function() {
                    actualizarCronFormulario($(this));
                    $.ajax({
                        type: 'POST',
                        url: 'action-form-proveedores.ajax.php',
                        data: $(this).serialize(),
                        success: function(data) {
                            var a = data.split('|***|');
                            $('#mag').html(a[0]);
                            if (a[1] == "update") {
                                $('#modalEditarProveedor').modal('hide');
                                cargarProveedores();
                            }
                        }
                    });
                });

                $("#buscarProveedor").on("keyup", function() {
                    pintarProveedores();
                });

                $("#verInactivos").on("change", function() {
                    cargarProveedores();
                });

                $('#formNuevoProveedor, #formEditarProveedor').on('change keyup', '.recurrencia-builder select, .recurrencia-builder input', function() {
                    actualizarVistaRepeticion($(this).closest('.recurrencia-builder'));
                });

                inicializarRepeticionFormulario($('#formNuevoProveedor'));
                cargarProveedores();
            });
        </script>
        <style>
            #tb tbody tr.proveedor-fila-local td {
                background-color: #f7fbff;
            }

            #tb tbody tr.proveedor-fila-fudo td {
                background-color: #fffaf0;
            }

            #tb tbody tr.proveedor-fila-local:hover td,
            #tb tbody tr.proveedor-fila-fudo:hover td {
                filter: brightness(0.98);
            }

            #tb tbody tr td .badge {
                filter: none;
            }

            .proveedor-origen-local {
                color: #0f4c81;
                background-color: #dceeff;
                border: 1px solid #9cc7ed;
            }

            .proveedor-origen-fudo {
                color: #7a4b00;
                background-color: #ffe7b3;
                border: 1px solid #f0bd58;
            }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include_once '../topBar.php';?>
        <div id="layoutSidenav">
            <?php include_once '../sidebar.php';?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Proveedores</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Proveedores
                            </div>
                            <div class="card-body">
                                <div id="mag"></div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="buscarProveedor">Buscar proveedor</label>
                                        <input type="text" id="buscarProveedor" class="form-control" placeholder="Nombre del proveedor">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="verInactivos">
                                            <label class="form-check-label" for="verInactivos">Ver Inactivos</label>
                                        </div>
                                    </div>
                                </div>
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Origen</th>
                                            <th class="text-center">Pedido</th>
                                            <th class="text-center">Fecha Proximo Pedido</th>
                                            <th class="text-center">Entrega</th>
                                            <th class="text-center">Fecha Proxima Entrega</th>
                                            <th class="text-center">Cantidad Minima</th>
                                            <th class="text-center">Monto Minimo</th>
                                            <th class="text-center">Activo</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="10">
                                                <a href="javascript:;" class="btn btn-danger" id="addmoreproveedores"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Proveedor</a>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <div class="modal fade" id="modalNuevoProveedor" tabindex="-1" role="dialog" aria-labelledby="modalNuevoProveedorLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <form id="formNuevoProveedor" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveProveedorPopup">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalNuevoProveedorLabel">Nuevo Proveedor</h5>
                                    <button type="button" class="close cerrar-modal-proveedor" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text" name="nombre" class="form-control" required="required">
                                    </div>
                                    <div class="form-group">
                                        <label>Pedido</label>
                                        <input type="hidden" name="cronPedido">
                                        <div class="border rounded p-3 recurrencia-builder" data-target="cronPedido">
                                            <div class="form-group">
                                                <label>Repetir</label>
                                                <select class="form-control recurrencia-tipo">
                                                    <option value="semanal">Semanalmente</option>
                                                    <option value="dias">Cada cierta cantidad de dias</option>
                                                    <option value="mensual">Mensualmente</option>
                                                    <option value="mensual-dia">Mensualmente el dia</option>
                                                </select>
                                            </div>
                                            <div class="recurrencia-semanal">
                                                <label>Dias</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="0" id="nuevoPedidoDomingo">
                                                        <label class="form-check-label" for="nuevoPedidoDomingo">Domingo</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="1" id="nuevoPedidoLunes">
                                                        <label class="form-check-label" for="nuevoPedidoLunes">Lunes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="2" id="nuevoPedidoMartes">
                                                        <label class="form-check-label" for="nuevoPedidoMartes">Martes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="3" id="nuevoPedidoMiercoles">
                                                        <label class="form-check-label" for="nuevoPedidoMiercoles">Miercoles</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="4" id="nuevoPedidoJueves">
                                                        <label class="form-check-label" for="nuevoPedidoJueves">Jueves</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="5" id="nuevoPedidoViernes">
                                                        <label class="form-check-label" for="nuevoPedidoViernes">Viernes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="6" id="nuevoPedidoSabado">
                                                        <label class="form-check-label" for="nuevoPedidoSabado">Sabado</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-dias">
                                                <label>Cada</label>
                                                <div class="input-group">
                                                    <input type="number" min="1" max="31" class="form-control recurrencia-intervalo-dias" value="15">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">dias</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual">
                                                <label>El</label>
                                                <div class="form-row">
                                                    <div class="col">
                                                        <select class="form-control recurrencia-ordinal">
                                                            <option value="1">primer</option>
                                                            <option value="2">segundo</option>
                                                            <option value="3">tercer</option>
                                                            <option value="4">cuarto</option>
                                                            <option value="5">quinto</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-control recurrencia-dia-mensual">
                                                            <option value="0">domingo</option>
                                                            <option value="1">lunes</option>
                                                            <option value="2">martes</option>
                                                            <option value="3">miercoles</option>
                                                            <option value="4">jueves</option>
                                                            <option value="5">viernes</option>
                                                            <option value="6">sabado</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual-dia">
                                                <label>Dia del mes</label>
                                                <input type="number" min="1" max="31" class="form-control recurrencia-dia-mes" value="15">
                                            </div>
                                            <small class="form-text text-muted">Cron generado: <span class="recurrencia-cron-preview"></span></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Entrega</label>
                                        <input type="hidden" name="cronEntrega">
                                        <div class="border rounded p-3 recurrencia-builder" data-target="cronEntrega">
                                            <div class="form-group">
                                                <label>Repetir</label>
                                                <select class="form-control recurrencia-tipo">
                                                    <option value="semanal">Semanalmente</option>
                                                    <option value="dias">Cada cierta cantidad de dias</option>
                                                    <option value="mensual">Mensualmente</option>
                                                    <option value="mensual-dia">Mensualmente el dia</option>
                                                </select>
                                            </div>
                                            <div class="recurrencia-semanal">
                                                <label>Dias</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="0" id="nuevoEntregaDomingo">
                                                        <label class="form-check-label" for="nuevoEntregaDomingo">Domingo</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="1" id="nuevoEntregaLunes">
                                                        <label class="form-check-label" for="nuevoEntregaLunes">Lunes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="2" id="nuevoEntregaMartes">
                                                        <label class="form-check-label" for="nuevoEntregaMartes">Martes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="3" id="nuevoEntregaMiercoles">
                                                        <label class="form-check-label" for="nuevoEntregaMiercoles">Miercoles</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="4" id="nuevoEntregaJueves">
                                                        <label class="form-check-label" for="nuevoEntregaJueves">Jueves</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="5" id="nuevoEntregaViernes">
                                                        <label class="form-check-label" for="nuevoEntregaViernes">Viernes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="6" id="nuevoEntregaSabado">
                                                        <label class="form-check-label" for="nuevoEntregaSabado">Sabado</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-dias">
                                                <label>Cada</label>
                                                <div class="input-group">
                                                    <input type="number" min="1" max="31" class="form-control recurrencia-intervalo-dias" value="15">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">dias</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual">
                                                <label>El</label>
                                                <div class="form-row">
                                                    <div class="col">
                                                        <select class="form-control recurrencia-ordinal">
                                                            <option value="1">primer</option>
                                                            <option value="2">segundo</option>
                                                            <option value="3">tercer</option>
                                                            <option value="4">cuarto</option>
                                                            <option value="5">quinto</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-control recurrencia-dia-mensual">
                                                            <option value="0">domingo</option>
                                                            <option value="1">lunes</option>
                                                            <option value="2">martes</option>
                                                            <option value="3">miercoles</option>
                                                            <option value="4">jueves</option>
                                                            <option value="5">viernes</option>
                                                            <option value="6">sabado</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual-dia">
                                                <label>Dia del mes</label>
                                                <input type="number" min="1" max="31" class="form-control recurrencia-dia-mes" value="15">
                                            </div>
                                            <small class="form-text text-muted">Cron generado: <span class="recurrencia-cron-preview"></span></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad Minima</label>
                                        <input type="number" step="0" name="cantidadMinima" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Monto Minimo</label>
                                        <input type="number" step=".01" name="montoMinimo" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="activo" class="form-control" required="required">
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Finalizado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary cerrar-modal-proveedor" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i> Grabar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalEditarProveedor" tabindex="-1" role="dialog" aria-labelledby="modalEditarProveedorLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <form id="formEditarProveedor" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="updateProveedorPopup">
                                <input type="hidden" name="id">
                                <input type="hidden" name="origen">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEditarProveedorLabel">Editar Proveedor</h5>
                                    <button type="button" class="close cerrar-modal-proveedor" data-dismiss="modal" aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text" name="nombre" class="form-control" required="required">
                                    </div>
                                    <div class="form-group">
                                        <label>Pedido</label>
                                        <input type="hidden" name="cronPedido">
                                        <div class="border rounded p-3 recurrencia-builder" data-target="cronPedido">
                                            <div class="form-group">
                                                <label>Repetir</label>
                                                <select class="form-control recurrencia-tipo">
                                                    <option value="semanal">Semanalmente</option>
                                                    <option value="dias">Cada cierta cantidad de dias</option>
                                                    <option value="mensual">Mensualmente</option>
                                                    <option value="mensual-dia">Mensualmente el dia</option>
                                                </select>
                                            </div>
                                            <div class="recurrencia-semanal">
                                                <label>Dias</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="0" id="editarPedidoDomingo">
                                                        <label class="form-check-label" for="editarPedidoDomingo">Domingo</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="1" id="editarPedidoLunes">
                                                        <label class="form-check-label" for="editarPedidoLunes">Lunes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="2" id="editarPedidoMartes">
                                                        <label class="form-check-label" for="editarPedidoMartes">Martes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="3" id="editarPedidoMiercoles">
                                                        <label class="form-check-label" for="editarPedidoMiercoles">Miercoles</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="4" id="editarPedidoJueves">
                                                        <label class="form-check-label" for="editarPedidoJueves">Jueves</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="5" id="editarPedidoViernes">
                                                        <label class="form-check-label" for="editarPedidoViernes">Viernes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="6" id="editarPedidoSabado">
                                                        <label class="form-check-label" for="editarPedidoSabado">Sabado</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-dias">
                                                <label>Cada</label>
                                                <div class="input-group">
                                                    <input type="number" min="1" max="31" class="form-control recurrencia-intervalo-dias" value="15">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">dias</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual">
                                                <label>El</label>
                                                <div class="form-row">
                                                    <div class="col">
                                                        <select class="form-control recurrencia-ordinal">
                                                            <option value="1">primer</option>
                                                            <option value="2">segundo</option>
                                                            <option value="3">tercer</option>
                                                            <option value="4">cuarto</option>
                                                            <option value="5">quinto</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-control recurrencia-dia-mensual">
                                                            <option value="0">domingo</option>
                                                            <option value="1">lunes</option>
                                                            <option value="2">martes</option>
                                                            <option value="3">miercoles</option>
                                                            <option value="4">jueves</option>
                                                            <option value="5">viernes</option>
                                                            <option value="6">sabado</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual-dia">
                                                <label>Dia del mes</label>
                                                <input type="number" min="1" max="31" class="form-control recurrencia-dia-mes" value="15">
                                            </div>
                                            <small class="form-text text-muted">Cron generado: <span class="recurrencia-cron-preview"></span></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Entrega</label>
                                        <input type="hidden" name="cronEntrega">
                                        <div class="border rounded p-3 recurrencia-builder" data-target="cronEntrega">
                                            <div class="form-group">
                                                <label>Repetir</label>
                                                <select class="form-control recurrencia-tipo">
                                                    <option value="semanal">Semanalmente</option>
                                                    <option value="dias">Cada cierta cantidad de dias</option>
                                                    <option value="mensual">Mensualmente</option>
                                                    <option value="mensual-dia">Mensualmente el dia</option>
                                                </select>
                                            </div>
                                            <div class="recurrencia-semanal">
                                                <label>Dias</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="0" id="editarEntregaDomingo">
                                                        <label class="form-check-label" for="editarEntregaDomingo">Domingo</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="1" id="editarEntregaLunes">
                                                        <label class="form-check-label" for="editarEntregaLunes">Lunes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="2" id="editarEntregaMartes">
                                                        <label class="form-check-label" for="editarEntregaMartes">Martes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="3" id="editarEntregaMiercoles">
                                                        <label class="form-check-label" for="editarEntregaMiercoles">Miercoles</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="4" id="editarEntregaJueves">
                                                        <label class="form-check-label" for="editarEntregaJueves">Jueves</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="5" id="editarEntregaViernes">
                                                        <label class="form-check-label" for="editarEntregaViernes">Viernes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input recurrencia-dia-semanal" type="checkbox" value="6" id="editarEntregaSabado">
                                                        <label class="form-check-label" for="editarEntregaSabado">Sabado</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-dias">
                                                <label>Cada</label>
                                                <div class="input-group">
                                                    <input type="number" min="1" max="31" class="form-control recurrencia-intervalo-dias" value="15">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">dias</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual">
                                                <label>El</label>
                                                <div class="form-row">
                                                    <div class="col">
                                                        <select class="form-control recurrencia-ordinal">
                                                            <option value="1">primer</option>
                                                            <option value="2">segundo</option>
                                                            <option value="3">tercer</option>
                                                            <option value="4">cuarto</option>
                                                            <option value="5">quinto</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-control recurrencia-dia-mensual">
                                                            <option value="0">domingo</option>
                                                            <option value="1">lunes</option>
                                                            <option value="2">martes</option>
                                                            <option value="3">miercoles</option>
                                                            <option value="4">jueves</option>
                                                            <option value="5">viernes</option>
                                                            <option value="6">sabado</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="recurrencia-mensual-dia">
                                                <label>Dia del mes</label>
                                                <input type="number" min="1" max="31" class="form-control recurrencia-dia-mes" value="15">
                                            </div>
                                            <small class="form-text text-muted">Cron generado: <span class="recurrencia-cron-preview"></span></small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad Minima</label>
                                        <input type="number" step="0" name="cantidadMinima" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Monto Minimo</label>
                                        <input type="number" step=".01" name="montoMinimo" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="activo" class="form-control" required="required">
                                            <option value="1">Activo</option>
                                            <option value="0">Finalizado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary cerrar-modal-proveedor" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i> Grabar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php include_once '../footer.php';?>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    </body>
</html>
