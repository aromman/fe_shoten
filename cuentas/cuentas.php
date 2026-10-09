<?php
// Initialize the session
session_start();
 
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
        <title>Cuentas Comerciales</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(e) {
                var tipoSaldo = {"E":"Efectivo","B":"Bancario","D":"Deuda","P":"Pozo"};
                var iconoTipoSaldo = {"E":"fa-money-bill-wave","B":"fa-building-columns","D":"fa-file-invoice-dollar","P":"fa-piggy-bank"};
                var COLORES_CATEGORICOS = ['#2a78d6','#eb6834','#1baf7a','#eda100','#e87ba4','#008300','#4a3aa7','#e34948'];
                var COLOR_OTRAS = '#898781';
                var LIMITE_SLICES = 7;
                var graficoSaldos = null;

                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };

                var formatoMoneda = function(valor) {
                    return '$ ' + (parseFloat(valor) || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                };

                var estiloTextoSobre = function(hex) {
                    var valor = hex.replace('#', '');
                    var r = parseInt(valor.substr(0, 2), 16) / 255;
                    var g = parseInt(valor.substr(2, 2), 16) / 255;
                    var b = parseInt(valor.substr(4, 2), 16) / 255;
                    var linear = function(c) { return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); };
                    var luminancia = 0.2126 * linear(r) + 0.7152 * linear(g) + 0.0722 * linear(b);
                    var contrasteBlanco = 1.05 / (luminancia + 0.05);
                    var contrasteNegro = (luminancia + 0.05) / 0.05;
                    var esBlanco = contrasteBlanco >= contrasteNegro;
                    return {
                        ink: esBlanco ? '#ffffff' : '#0b0b0b',
                        borde: esBlanco ? 'rgba(255,255,255,0.35)' : 'rgba(11,11,11,0.15)',
                        muted: esBlanco ? 'rgba(255,255,255,0.75)' : 'rgba(11,11,11,0.65)'
                    };
                };

                var construirMapaColores = function(cuentas) {
                    var positivas = $.grep(cuentas || [], function(row) { return parseFloat(row.saldo || 0) > 0; });
                    positivas.sort(function(a, b) { return parseFloat(b.saldo || 0) - parseFloat(a.saldo || 0); });

                    var mapa = {};
                    $.each(positivas, function(index, row) {
                        mapa[row.id] = index < LIMITE_SLICES ? COLORES_CATEGORICOS[index] : COLOR_OTRAS;
                    });

                    return mapa;
                };

                var renderGraficoSaldos = function(cuentas, mapaColores) {
                    var positivas = $.grep(cuentas || [], function(row) { return parseFloat(row.saldo || 0) > 0; });
                    positivas.sort(function(a, b) { return parseFloat(b.saldo || 0) - parseFloat(a.saldo || 0); });

                    var principales = positivas.slice(0, LIMITE_SLICES);
                    var resto = positivas.slice(LIMITE_SLICES);

                    var etiquetas = $.map(principales, function(row) { return row.nombre; });
                    var valores = $.map(principales, function(row) { return parseFloat(row.saldo || 0); });
                    var colores = $.map(principales, function(row) { return mapaColores[row.id] || COLOR_OTRAS; });

                    if (resto.length) {
                        var totalResto = 0;
                        $.each(resto, function(_, row) { totalResto += parseFloat(row.saldo || 0); });
                        etiquetas.push('Otras cuentas (' + resto.length + ')');
                        valores.push(totalResto);
                        colores.push(COLOR_OTRAS);
                    }

                    var totalPositivas = 0;
                    $.each(positivas, function(_, row) { totalPositivas += parseFloat(row.saldo || 0); });

                    var totalGeneral = 0;
                    $.each(cuentas || [], function(_, row) { totalGeneral += parseFloat(row.saldo || 0); });
                    $('#saldoTotalGrafico').text(formatoMoneda(totalGeneral));

                    if (graficoSaldos) {
                        graficoSaldos.destroy();
                        graficoSaldos = null;
                    }

                    if (valores.length) {
                        var ctx = document.getElementById('graficoSaldosCanvas').getContext('2d');
                        graficoSaldos = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: etiquetas,
                                datasets: [{
                                    data: valores,
                                    backgroundColor: colores,
                                    borderColor: '#fcfcfb',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 1,
                                legend: { position: 'bottom', labels: { boxWidth: 12 } },
                                tooltips: {
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            var etiqueta = data.labels[tooltipItem.index] || '';
                                            var valor = data.datasets[0].data[tooltipItem.index] || 0;
                                            var porcentaje = totalPositivas > 0 ? (valor / totalPositivas * 100) : 0;
                                            return etiqueta + ': ' + formatoMoneda(valor) + ' (' + porcentaje.toFixed(1) + '%)';
                                        }
                                    }
                                }
                            }
                        });
                    }

                    var $tbody = $('#tablaSaldosBody');
                    $tbody.empty();

                    if (!positivas.length) {
                        $tbody.append('<tr><td colspan="3" class="text-center text-muted">Sin saldos para mostrar</td></tr>');
                        return;
                    }

                    $.each(positivas, function(index, row) {
                        var saldo = parseFloat(row.saldo || 0);
                        var porcentaje = totalPositivas > 0 ? (saldo / totalPositivas * 100) : 0;
                        var color = mapaColores[row.id] || COLOR_OTRAS;

                        $tbody.append(
                            '<tr>' +
                                '<td><span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:' + color + ';"></span>' + escapeHtml(row.nombre) + '</td>' +
                                '<td class="text-end">' + escapeHtml(formatoMoneda(saldo)) + '</td>' +
                                '<td class="text-end">' + porcentaje.toFixed(1) + '%</td>' +
                            '</tr>'
                        );
                    });
                };

                var renderCards = function(cuentas, mapaColores) {
                    var $contenedor = $('#cuentasCards');
                    $contenedor.empty();

                    if (!cuentas || cuentas.length === 0) {
                        $contenedor.append('<div class="col-12"><p class="text-center text-muted">No hay cuentas registradas</p></div>');
                        return;
                    }

                    $.each(cuentas, function(index, row) {
                        var icono = iconoTipoSaldo[row.tipoSaldo] || 'fa-wallet';
                        var tipoSaldoEtiqueta = row.tipoSaldoEtiqueta || tipoSaldo[row.tipoSaldo] || row.tipoSaldo;
                        var color = (mapaColores && mapaColores[row.id]) || COLOR_OTRAS;
                        var estilo = estiloTextoSobre(color);
                        var esActiva = row.activa !== false;

                        $contenedor.append(
                            '<div class="col">' +
                                '<div class="card mb-4 rounded-3 shadow-sm h-100" style="border: 1px solid ' + color + ';' + (esActiva ? '' : ' opacity: 0.6;') + '">' +
                                    '<div class="card-header py-3" style="background-color: ' + color + '; color: ' + estilo.ink + '; border-color: ' + color + ';">' +
                                        '<h4 class="my-0 fw-normal">' + escapeHtml(row.nombre) + (esActiva ? '' : ' <span class="badge bg-dark">Inactiva</span>') + '</h4>' +
                                    '</div>' +
                                    '<div class="card-body text-center">' +
                                        '<h1 class="card-title pricing-card-title">$ ' + escapeHtml(row.saldoEtiqueta) + '</h1>' +
                                        '<ul class="list-unstyled mt-3 mb-4">' +
                                            '<li><i class="fa-solid ' + icono + '" style="color: ' + color + ';"></i> ' + escapeHtml(tipoSaldoEtiqueta) + '</li>' +
                                        '</ul>' +
                                        '<div class="d-flex justify-content-center gap-3">' +
                                            '<a href="#" class="text-primary js-transferir" data-bs-toggle="modal" data-bs-target="#transferModal" data-id="' + escapeHtml(row.id) + '" data-nombre="' + escapeHtml(row.nombre) + '" title="Transferir"><i class="fa-solid fa-money-bill-transfer fa-lg"></i></a>' +
                                            '<a href="view-cuenta.php?forwardOk=cuentas.php&id=' + escapeHtml(row.id) + '" class="text-primary" title="Ver"><i class="fa fa-fw fa-eye fa-lg"></i></a>' +
                                            '<a href="update-cuentas.php?id=' + escapeHtml(row.id) + '" class="text-primary" title="Editar"><i class="fa fa-fw fa-pencil fa-lg"></i></a>' +
                                            (esActiva ? '' : '<a href="#" class="text-success js-activar-cuenta" data-id="' + escapeHtml(row.id) + '" title="Activar"><i class="fa-solid fa-rotate-left fa-lg"></i></a>') +
                                            '<a href="#" class="text-danger js-borrar-cuenta" data-id="' + escapeHtml(row.id) + '" title="Borrar"><i class="fa fa-fw fa-trash fa-lg"></i></a>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>'
                        );
                    });
                };

                var cargarCuentas = function() {
                    var incluirInactivas = $('#verInactivas').is(':checked');
                    var url = '../bff/cuentas/control.php?ambitoUso=COMERCIAL' + (incluirInactivas ? '&incluirInactivas=1' : '');

                    $.getJSON(url)
                        .done(function(response) {
                            var cuentas = response.data && response.data.cuentas ? response.data.cuentas : [];
                            var mapaColores = construirMapaColores(cuentas);
                            renderCards(cuentas, mapaColores);
                            renderGraficoSaldos(cuentas, mapaColores);
                        })
                        .fail(function() {
                            $('#cuentasCards').html('<div class="col-12"><p class="text-center text-danger">No se pudo cargar el listado de cuentas.</p></div>');
                            $('#tablaSaldosBody').html('<tr><td colspan="3" class="text-center text-danger">No se pudo cargar el gráfico.</td></tr>');
                        });
                };

                cargarCuentas();
                $('#verInactivas').on('change', cargarCuentas);

                var todasLasCuentas = [];

                $.getJSON('../bff/cuentas/control.php')
                    .done(function(response) {
                        todasLasCuentas = response.data && response.data.cuentas ? response.data.cuentas : [];
                    });

                $('#transferModal').on('show.bs.modal', function(evento) {
                    var $trigger = $(evento.relatedTarget);
                    var idOrigen = $trigger.data('id');
                    var nombreOrigen = $trigger.data('nombre');

                    $('#transferCuentaOrigen').val(idOrigen);
                    $('#transferNombreOrigen').val(nombreOrigen);
                    $('#transferFecha').val(new Date().toISOString().slice(0, 10));
                    $('#transferImporte').val('');

                    var $destino = $('#transferCuentaDestino');
                    $destino.empty().append('<option value="">Seleccione</option>');
                    $.each(todasLasCuentas, function(_, cuenta) {
                        if (String(cuenta.id) !== String(idOrigen)) {
                            $destino.append($('<option></option>').val(cuenta.id).text(cuenta.nombre));
                        }
                    });
                });

                $('#cuentasCards').on('click', '.js-borrar-cuenta', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    $.getJSON('../bff/cuentas/verificar-baja.php?id=' + encodeURIComponent(id))
                        .done(function(response) {
                            var puedeBorrar = !!(response.data && response.data.puedeBorrar);
                            var mensaje = puedeBorrar
                                ? 'Esta seguro de querer borrar esta cuenta?'
                                : 'La cuenta no se borrara, solo se dejara inactiva. Confirma?';

                            if (!confirm(mensaje)) {
                                return;
                            }

                            var $form = $('<form></form>').attr({ method: 'post', action: '../bff/cuentas/actions.php' });
                            $form.append($('<input>').attr({ type: 'hidden', name: 'action', value: 'bajaCuenta' }));
                            $form.append($('<input>').attr({ type: 'hidden', name: 'id', value: id }));
                            $form.append($('<input>').attr({ type: 'hidden', name: 'forwardOk', value: 'cuentas.php' }));
                            $form.appendTo('body').trigger('submit');
                        })
                        .fail(function() {
                            alert('No se pudo verificar la cuenta.');
                        });
                });

                $('#cuentasCards').on('click', '.js-activar-cuenta', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    if (!confirm('Desea activar esta cuenta?')) {
                        return;
                    }

                    var $form = $('<form></form>').attr({ method: 'post', action: '../bff/cuentas/actions.php' });
                    $form.append($('<input>').attr({ type: 'hidden', name: 'action', value: 'activarCuenta' }));
                    $form.append($('<input>').attr({ type: 'hidden', name: 'id', value: id }));
                    $form.append($('<input>').attr({ type: 'hidden', name: 'forwardOk', value: 'cuentas.php' }));
                    $form.appendTo('body').trigger('submit');
                });
            });
        </script>

    </head>
    <body class="sb-nav-fixed">

        <?php include_once '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include_once '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Cuentas Comerciales</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Finanzas</li>
                            <li class="breadcrumb-item">Cuentas</li>
                            <li class="breadcrumb-item active">Cuentas Comerciales</li>
                        </ol>
                        <form id="formCuentas" method="post" onSubmit="return false;">

                        <div class="row justify-content-start align-items-center mb-3">
                            <div class="col-auto">
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoModal"><i class="fa-solid fa-circle-plus"></i> Nueva Cuenta</a>
                            </div>
                            <div class="col-auto">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="verInactivas">
                                    <label class="form-check-label" for="verInactivas">Ver cuentas inactivas</label>
                                </div>
                            </div>
                        </div>

                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4" id="cuentasCards">
                            <div class="col-12">
                                <p class="text-center text-muted">Cargando cuentas...</p>
                            </div>
                        </div>
                        </form>

                        <div class="card mt-4 mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-1"></i>
                                Distribución de Saldo por Cuenta
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div style="max-width: 220px; margin: 0 auto;">
                                            <canvas id="graficoSaldosCanvas"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mb-2">
                                            <span class="text-muted">Saldo total:</span>
                                            <span class="fw-bold" id="saldoTotalGrafico">$ 0,00</span>
                                        </div>
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Cuenta</th>
                                                    <th class="text-end">Saldo</th>
                                                    <th class="text-end">%</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaSaldosBody">
                                                <tr><td colspan="3" class="text-center text-muted">Cargando...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

                <?php include_once '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <?php include_once 'nuevoModal.php'; ?>
        <?php include_once 'transferModal.php'; ?>

    </body>
</html>
