<?php
session_start();

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../login.php');
    exit;
}

if (!isset($_SESSION['user.rol']) || (int) $_SESSION['user.rol'] !== 0) {
    header('location: ../bienvenido.php');
    exit;
}

require_once __DIR__ . '/../app/config/url.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Costo de mantenimiento de inventario" />
        <title>Costo de mantenimiento de inventario</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .cmi-chart-wrap {
                height: 380px;
                position: relative;
            }

            .cmi-tasa-form {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: .6rem;
            }

            .cmi-tasa-form input {
                max-width: 120px;
            }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include __DIR__ . '/../topBar.php'; ?>

        <div id="layoutSidenav">
            <?php include __DIR__ . '/../sidebar.php'; ?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Costo de mantenimiento de inventario</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Productos</li>
                            <li class="breadcrumb-item">Inventario</li>
                            <li class="breadcrumb-item active">Costo de mantenimiento</li>
                        </ol>

                        <div id="cmiAlert" class="alert alert-danger d-none" role="alert"></div>

                        <div class="card shadow mb-4">
                            <div class="card-body d-flex flex-column gap-3">
                                <form id="cmiTasaMantenimientoForm" class="cmi-tasa-form" onsubmit="return false;" data-accion="guardarTasaMantenimiento">
                                    <label for="cmiTasaMantenimientoInput" class="mb-0 fw-bold">Tasa anual de mantenimiento</label>
                                    <div class="input-group" style="max-width: 160px;">
                                        <input type="text" class="form-control cmi-tasa-input" id="cmiTasaMantenimientoInput" inputmode="decimal">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm cmi-tasa-guardar">Guardar</button>
                                    <span class="text-muted small">Almacenamiento, seguro y obsolescencia.</span>
                                </form>
                                <form id="cmiTasaInteresForm" class="cmi-tasa-form" onsubmit="return false;" data-accion="guardarTasaInteres">
                                    <label for="cmiTasaInteresInput" class="mb-0 fw-bold">Tasa de interés anual</label>
                                    <div class="input-group" style="max-width: 160px;">
                                        <input type="text" class="form-control cmi-tasa-input" id="cmiTasaInteresInput" inputmode="decimal">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm cmi-tasa-guardar">Guardar</button>
                                    <span class="text-muted small">Costo de oportunidad / financiero del capital inmovilizado en inventario.</span>
                                </form>
                            </div>
                        </div>

                        <div class="row g-3 mb-4" id="cmiResumenCards">
                            <div class="col-12 text-muted">Cargando tablero...</div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-xl-7 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>
                                        Costo de mantenimiento por editorial
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Editorial</th>
                                                        <th class="text-end">Stock</th>
                                                        <th class="text-end">Valor a costo</th>
                                                        <th class="text-end">Mantenimiento mensual</th>
                                                        <th class="text-end">Interés mensual</th>
                                                        <th class="text-end">Total mensual</th>
                                                        <th class="text-end">Participación</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="cmiTablaBody">
                                                    <tr><td colspan="7" class="text-muted">Cargando...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-5 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header">
                                        <i class="fas fa-chart-column me-1"></i>
                                        Top 10 editoriales por costo total mensual
                                    </div>
                                    <div class="card-body">
                                        <div class="cmi-chart-wrap">
                                            <canvas id="cmiChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <?php include __DIR__ . '/../footer.php'; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function() {
                var endpoint = <?php echo json_encode(app_url('/bff/stock/costo-mantenimiento.php')); ?>;
                var cmiChart = null;
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };
                var formatoMoneda = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                var formatoNumero = new Intl.NumberFormat('es-AR', {
                    maximumFractionDigits: 0
                });
                var formatoPorcentaje = new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
                var normalizarNumeroEditable = function(valor) {
                    var texto = String(valor || '').trim();
                    if (texto.indexOf(',') >= 0) {
                        texto = texto.replace(/\./g, '').replace(',', '.');
                    }
                    return texto;
                };

                var tarjeta = function(titulo, valor, formato, tono, icono) {
                    var valorTexto = formato === 'moneda' ? formatoMoneda.format(valor || 0) : formatoNumero.format(valor || 0);

                    return '' +
                        '<div class="col-12 col-md-6 col-xl-3">' +
                            '<div class="card shadow h-100">' +
                                '<div class="card-body">' +
                                    '<div class="text-muted small mb-1"><i class="fas ' + escapeHtml(icono) + ' me-1"></i>' + escapeHtml(titulo) + '</div>' +
                                    '<div class="fs-4 fw-bold text-' + escapeHtml(tono) + '">' + escapeHtml(valorTexto) + '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
                };

                var renderCards = function(data) {
                    var resumen = data.resumen;
                    var html = '' +
                        tarjeta('Valor de inventario (costo)', resumen.valorCosto, 'moneda', 'primary', 'fa-warehouse') +
                        tarjeta('Mantenimiento mensual', resumen.costoMantenimientoMensual, 'moneda', 'danger', 'fa-money-bill-transfer') +
                        tarjeta('Interés mensual', resumen.costoInteresMensual, 'moneda', 'warning', 'fa-percent') +
                        tarjeta('Costo total mensual', resumen.costoTotalMensual, 'moneda', 'dark', 'fa-scale-balanced') +
                        tarjeta('Costo total anual', resumen.costoTotalAnual, 'moneda', 'dark', 'fa-calendar-days') +
                        tarjeta('Productos con stock', resumen.cantidadProductos, 'numero', 'secondary', 'fa-boxes-stacked');
                    $('#cmiResumenCards').html(html);
                };

                var filaEditorial = function(fila) {
                    return '' +
                        '<tr>' +
                            '<td>' + escapeHtml(fila.editorial) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoNumero.format(fila.stock || 0)) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoMoneda.format(fila.valorCosto || 0)) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoMoneda.format(fila.costoMantenimientoMensual || 0)) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoMoneda.format(fila.costoInteresMensual || 0)) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoMoneda.format(fila.costoTotalMensual || 0)) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoPorcentaje.format(fila.participacion || 0)) + '%</td>' +
                        '</tr>';
                };

                var renderTabla = function(porEditorial) {
                    if (!porEditorial || porEditorial.length === 0) {
                        $('#cmiTablaBody').html('<tr><td colspan="7" class="text-muted">Sin datos.</td></tr>');
                        return;
                    }

                    $('#cmiTablaBody').html($.map(porEditorial, filaEditorial).join(''));
                };

                var renderChart = function(porEditorial) {
                    var top = (porEditorial || []).slice(0, 10);
                    var labels = $.map(top, function(fila) { return fila.editorial; });
                    var costos = $.map(top, function(fila) { return fila.costoTotalMensual; });
                    var canvas = document.getElementById('cmiChart');

                    if (!canvas) {
                        return;
                    }
                    if (cmiChart) {
                        cmiChart.destroy();
                    }

                    cmiChart = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Costo total mensual',
                                    data: costos,
                                    backgroundColor: 'rgba(220, 53, 69, .75)'
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return formatoMoneda.format(context.parsed.x);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        callback: function(value) {
                                            return formatoMoneda.format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                };

                var cargar = function() {
                    $.getJSON(endpoint)
                        .done(function(response) {
                            var data = response.data;
                            $('#cmiAlert').addClass('d-none').text('');
                            $('#cmiTasaMantenimientoInput').val(data.tasaAnual);
                            $('#cmiTasaInteresInput').val(data.tasaInteresAnual);
                            renderCards(data);
                            renderTabla(data.porEditorial);
                            renderChart(data.porEditorial);
                        })
                        .fail(function() {
                            $('#cmiAlert').removeClass('d-none').text('No se pudo cargar el costo de mantenimiento de inventario.');
                            $('#cmiResumenCards').html('');
                            $('#cmiTablaBody').html('');
                        });
                };

                $('.cmi-tasa-form').on('submit', function() {
                    var $form = $(this);
                    var nuevaTasa = normalizarNumeroEditable($form.find('.cmi-tasa-input').val());
                    if (nuevaTasa === '' || isNaN(Number(nuevaTasa)) || Number(nuevaTasa) < 0) {
                        window.alert('Ingrese una tasa válida.');
                        return;
                    }

                    var $boton = $form.find('.cmi-tasa-guardar');
                    $boton.prop('disabled', true);
                    $.post(endpoint, {
                        accion: $form.data('accion'),
                        tasaAnual: nuevaTasa
                    })
                        .done(function() {
                            cargar();
                        })
                        .fail(function() {
                            window.alert('No se pudo guardar la tasa.');
                        })
                        .always(function() {
                            $boton.prop('disabled', false);
                        });
                });

                cargar();
            });
        </script>
    </body>
</html>
