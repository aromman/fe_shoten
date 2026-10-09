<?php

session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if(!isset($_GET["id"]) || trim($_GET["id"]) === ""){
    header("location: error.php");
    exit;
}

$id = (int) $_GET["id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Conciliar Movimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body class="sb-nav-fixed">
<?php include '../topBar.php';?>
<div id="layoutSidenav">
    <?php include '../sidebar.php';?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Conciliar movimiento</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="cuentas.php">Cuentas</a></li>
                    <li class="breadcrumb-item"><a href="#" id="volverCuentaLink">Cuenta</a></li>
                    <li class="breadcrumb-item active">Conciliar</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-table me-1"></i>Información del movimiento</div>
                <div class="card-body">
                    <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Descripcion</th>
                                    <th>Causal</th>
                                </tr>
                            </thead>
                            <tbody id="movimientoBody">
                                <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                        <div class="mt-3" id="vincularMovimientoWrap" style="display:none;">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vincularMovimientoModal">
                                Crear Nueva Vinculacion
                            </button>
                        </div>
                        <div class="mt-4" id="movimientoAsociadoWrap" style="display:none;">
                            <h5 class="mb-3">Movimiento asociado</h5>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Cuenta</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Descripcion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="movimientoAsociadoBody">
                                    <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                                </tbody>
                            </table>
                            <div class="mt-3" id="conciliarMovimientoWrap" style="display:none;">
                                <form method="post" action="../bff/cuentas/actions.php" class="d-inline">
                                    <input type="hidden" name="action" value="conciliarMovimiento">
                                    <input type="hidden" name="idMovimiento" id="idMovimientoConciliar">
                                    <input type="hidden" name="consolidado" value="1">
                                    <button type="submit" class="btn btn-success">Conciliar el movimiento</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4" id="ventasCard">
                    <div class="card-header"><i class="fas fa-table me-1"></i>Ventas</div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Descripcion</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody id="ventasBody">
                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card mb-4" id="comprasCard">
                    <div class="card-header"><i class="fas fa-table me-1"></i>Compras</div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Descripcion</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody id="comprasBody">
                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card mb-4" id="gastosCard">
                    <div class="card-header"><i class="fas fa-table me-1"></i>Gastos</div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Descripcion</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody id="gastosBody">
                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card mb-4" id="transferenciasCard">
                    <div class="card-header"><i class="fas fa-table me-1"></i>Transferencias</div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Descripcion</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody id="transferenciasBody">
                                <tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <a href="#" id="volverCuentaBtn" class="btn btn-secondary">Volver</a>
            </div>
        </main>
        <?php include '../footer.php';?>
    </div>
</div>
<div class="modal fade" id="vincularMovimientoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="../bff/cuentas/actions.php">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nueva Vinculacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crearNuevaVinculacion">
                    <input type="hidden" name="idMovimiento" id="idMovimientoLink">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="text" class="form-control" id="fechaMovimientoLink" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto</label>
                            <input type="text" class="form-control" id="montoMovimientoLink" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de origen</label>
                        <select class="form-control" name="tipoOrigen" id="tipoOrigenLink" required></select>
                    </div>
                    <div class="mb-3" id="campoProveedorWrap" style="display:none;">
                        <label class="form-label">Proveedor</label>
                        <select class="form-control" name="proveedorId" id="proveedorIdLink"></select>
                    </div>
                    <div class="mb-3" id="campoClaseWrap" style="display:none;">
                        <label class="form-label">Clase</label>
                        <select class="form-control" name="claseId" id="claseIdLink"></select>
                    </div>
                    <div class="mb-3" id="campoCuentaWrap" style="display:none;">
                        <label class="form-label">Cuenta destino</label>
                        <select class="form-control" name="cuentaDestinoId" id="cuentaDestinoIdLink"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Vincular</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(function() {
    var formatoFecha = function(fecha) {
        if (!fecha) {
            return '';
        }

        var texto = String(fecha).trim();
        var match = texto.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return match[3] + '/' + match[2] + '/' + match[1];
        }

        var parsed = new Date(texto);
        if (isNaN(parsed.getTime())) {
            return texto;
        }

        var day = String(parsed.getDate()).padStart(2, '0');
        var month = String(parsed.getMonth() + 1).padStart(2, '0');
        var year = parsed.getFullYear();
        return day + '/' + month + '/' + year;
    };

    var parseFechaComparable = function(fecha) {
        if (!fecha) {
            return '';
        }

        var texto = String(fecha).trim();
        var match = texto.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return match[1] + '-' + match[2] + '-' + match[3];
        }

        var parsed = new Date(texto);
        if (isNaN(parsed.getTime())) {
            return texto;
        }

        var year = parsed.getFullYear();
        var month = String(parsed.getMonth() + 1).padStart(2, '0');
        var day = String(parsed.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    };

    var esMismoRegistro = function(item, movimiento) {
        if (!item || !movimiento) {
            return false;
        }

        var fechaItem = parseFechaComparable(item.fecha || '');
        var fechaMovimiento = parseFechaComparable(movimiento.fecha || '');
        var montoItem = Number(item.monto || 0);
        var montoMovimiento = Number(movimiento.monto || 0);

        return fechaItem && fechaMovimiento && fechaItem === fechaMovimiento && Math.abs(montoItem - montoMovimiento) < 0.0001;
    };

    var renderRows = function(items, tipoOrigen, movimiento) {
            if (!items || !items.length) {
                return '<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>';
            }

        var html = '';
        $.each(items, function(_, item) {
            var destacado = esMismoRegistro(item, movimiento);
            html += '<tr' + (destacado ? ' class="table-warning fw-bold"' : '') + '>' +
                '<td>' + formatoFecha(item.fecha || '') + '</td>' +
                '<td>' + Number(item.monto || 0).toFixed(2) + '</td>' +
                '<td>' + (item.descripcion || '') + '</td>' +
                '<td class="text-center">' +
                    '<form method="post" action="../bff/cuentas/actions.php" class="d-inline">' +
                        '<input type="hidden" name="action" value="vincularOrigenExistente">' +
                        '<input type="hidden" name="idMovimiento" value="<?php echo $id; ?>">' +
                        '<input type="hidden" name="tipoOrigen" value="' + (tipoOrigen || '') + '">' +
                        '<input type="hidden" name="origenId" value="' + (item.id || '') + '">' +
                        '<button type="submit" class="btn btn-sm btn-primary">Vincular al movimiento</button>' +
                    '</form>' +
                '</td>' +
            '</tr>';
        });

        return html;
    };

    var renderMovimientoAsociado = function(items, movimientoId) {
        if (!items || !items.length) {
            return '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
        }

        var html = '';
        $.each(items, function(_, item) {
            html += '<tr>' +
                '<td>' + (item.extra || '') + '</td>' +
                '<td>' + formatoFecha(item.fecha || '') + '</td>' +
                '<td>' + (item.tipo || '') + '</td>' +
                '<td>' + Number(item.monto || 0).toFixed(2) + '</td>' +
                '<td>' + (item.descripcion || '') + '</td>' +
                '<td>' +
                    '<form method="post" action="../bff/cuentas/actions.php" class="d-inline">' +
                        '<input type="hidden" name="action" value="unlinkMovimiento">' +
                        '<input type="hidden" name="idMovimiento" value="' + (movimientoId || '') + '">' +
                        '<button type="submit" class="btn btn-sm btn-outline-danger">Desvincular</button>' +
                    '</form>' +
                '</td>' +
            '</tr>';
        });

        return html;
    };

    var renderOptions = function(items, placeholder) {
        var html = '<option value="">' + (placeholder || 'Seleccione') + '</option>';
        $.each(items || [], function(_, item) {
            var extra = item.extra ? (' - ' + item.extra) : '';
            html += '<option value="' + item.id + '">' + formatoFecha(item.fecha || '') + ' - ' + Number(item.monto || 0).toFixed(2) + ' - ' + (item.descripcion || '') + extra + '</option>';
        });
        return html;
    };

    var renderSimpleOptions = function(items, placeholder) {
        var html = '<option value="">' + (placeholder || 'Seleccione') + '</option>';
        $.each(items || [], function(_, item) {
            html += '<option value="' + item.id + '">' + (item.nombre || '') + '</option>';
        });
        return html;
    };

    var ocultarSiVacia = function(cardId, items) {
        if (!items || !items.length) {
            $(cardId).hide();
            return false;
        }

        $(cardId).show();
        return true;
    };

    $.ajax({
        url: '../bff/cuentas/movimiento.php?id=<?php echo $id; ?>&ts=' + new Date().getTime(),
        dataType: 'json',
        cache: false
    })
        .done(function(response) {
            var movimiento = response.data.movimiento || {};
            var cuentaNombre = response.data.cuentaNombre || '';
            var tipoMovimientoEtiqueta = response.data.tipoMovimientoEtiqueta || (movimiento.tipo_movimiento || '');
            var causalNombre = response.data.causalNombre || '';
            var descripcion = response.data.descripcion || movimiento.descripcion || '';
            var causalId = response.data.idCausal || movimiento.id_causal || '';
            var movimientoAsociado = response.data.movimientoAsociado || [];
            var movimientoAsociadoTipo = response.data.movimientoAsociadoTipo || '';
            var grillas = response.data.grillas || {};
            var tipoMovimientoBase = String(tipoMovimientoEtiqueta || '').toUpperCase();
            var cuentaId = movimiento.id_cuenta || '';
            $('#volverCuentaLink').attr('href', 'view-cuenta.php?id=' + cuentaId);
            $('#volverCuentaBtn').attr('href', 'view-cuenta.php?id=' + cuentaId);
            $('#idMovimientoLink').val(movimiento.id || '');
            $('#fechaMovimientoLink').val(formatoFecha(movimiento.fecha || ''));
            $('#montoMovimientoLink').val(Number(movimiento.monto || 0).toFixed(2));
            $('#idMovimientoConciliar').val(movimiento.id || '');
            var filas = '<tr>' +
                '<td>' + (cuentaNombre || '') + '</td>' +
                '<td>' + formatoFecha(movimiento.fecha || '') + '</td>' +
                '<td>' + (tipoMovimientoEtiqueta || '') + '</td>' +
                '<td>' + Number(movimiento.monto || 0).toFixed(2) + '</td>' +
                '<td>' + (descripcion || '') + '</td>' +
                '<td>' + (causalNombre || causalId || '') + '</td>' +
            '</tr>';
            $('#movimientoBody').html(filas);

            var tieneVinculo = (movimientoAsociado.length || movimientoAsociadoTipo);
            if (tieneVinculo) {
                $('#movimientoAsociadoWrap').show();
                $('#movimientoAsociadoBody').html(renderMovimientoAsociado(movimientoAsociado, movimiento.id || ''));
                $('#conciliarMovimientoWrap').show();
                $('#vincularMovimientoWrap').hide();
            } else {
                $('#movimientoAsociadoWrap').hide();
                $('#conciliarMovimientoWrap').hide();
                $('#vincularMovimientoWrap').show();
            }

            var tiposPermitidos = [];
            if (tipoMovimientoBase === 'CREDITO') {
                tiposPermitidos = [
                    { value: 'ventas', label: 'Ventas' },
                    { value: 'transferencias', label: 'Transferencias' }
                ];
            } else if (tipoMovimientoBase === 'DEBITO') {
                tiposPermitidos = [
                    { value: 'compras', label: 'Compras' },
                    { value: 'gastos', label: 'Gastos' },
                    { value: 'transferencias', label: 'Transferencias' }
                ];
            }

            var $tipoOrigen = $('#tipoOrigenLink');
            if (!tiposPermitidos.length) {
                $('#vincularMovimientoWrap').hide();
                $tipoOrigen.empty();
            }
            $tipoOrigen.empty();
            $.each(tiposPermitidos, function(_, item) {
                $tipoOrigen.append('<option value="' + item.value + '">' + item.label + '</option>');
            });

            var actualizarOrigenes = function() {
                var tipo = $tipoOrigen.val();
                $('#campoProveedorWrap, #campoClaseWrap, #campoCuentaWrap').hide();
                $('#campoProveedorWrap select, #campoClaseWrap select, #campoCuentaWrap select').prop('required', false);

                if (tipo === 'compras') {
                    $('#campoProveedorWrap').show();
                    $('#proveedorIdLink').prop('required', true);
                } else if (tipo === 'gastos') {
                    $('#campoClaseWrap').show();
                    $('#claseIdLink').prop('required', true);
                } else if (tipo === 'transferencias') {
                    $('#campoCuentaWrap').show();
                    $('#cuentaDestinoIdLink').prop('required', true);
                }
            };

            $tipoOrigen.off('change').on('change', actualizarOrigenes);
            actualizarOrigenes();

            $.ajax({
                url: '../bff/cuentas/vinculacion-control.php?ts=' + new Date().getTime(),
                dataType: 'json',
                cache: false
            })
                .done(function(vinculoResponse) {
                    var data = vinculoResponse.data || {};
                    $('#proveedorIdLink').html(renderSimpleOptions(data.proveedores || [], 'Seleccione'));
                    $('#claseIdLink').html(renderSimpleOptions(data.clases || [], 'Seleccione'));
                    var cuentasDestino = (data.cuentas || []).filter(function(item) {
                        return String(item.id) !== String(cuentaId);
                    });
                    $('#cuentaDestinoIdLink').html(renderSimpleOptions(cuentasDestino, 'Seleccione'));
                });

            if (tieneVinculo) {
                $('#ventasCard, #comprasCard, #gastosCard, #transferenciasCard').hide();
            } else if (String(tipoMovimientoEtiqueta).toUpperCase() === 'CREDITO' && (grillas.ventas || []).length) {
                $('#ventasCard').show();
                $('#ventasBody').html(renderRows(grillas.ventas || [], 'ventas', movimiento));
            } else {
                $('#ventasCard').hide();
            }

            if (String(tipoMovimientoEtiqueta).toUpperCase() === 'DEBITO' && (grillas.compras || []).length) {
                $('#comprasCard').show();
                $('#comprasBody').html(renderRows(grillas.compras || [], 'compras', movimiento));
            } else {
                $('#comprasCard').hide();
            }

            if (String(tipoMovimientoEtiqueta).toUpperCase() === 'DEBITO' && (grillas.gastos || []).length) {
                $('#gastosCard').show();
                $('#gastosBody').html(renderRows(grillas.gastos || [], 'gastos', movimiento));
            } else {
                $('#gastosCard').hide();
            }

            if ((grillas.transferencias || []).length) {
                $('#transferenciasCard').show();
                $('#transferenciasBody').html(renderRows(grillas.transferencias || [], 'transferencias', movimiento));
            } else {
                $('#transferenciasCard').hide();
            }
        })
        .fail(function() {
            $('#movimientoBody').html('<tr><td colspan="6" class="text-center text-danger">No se pudo cargar el movimiento.</td></tr>');
        });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
