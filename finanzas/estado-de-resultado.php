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
        <meta name="description" content="Estado de resultado" />
        <title>Estado de resultado</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .er-table th,
            .er-table td {
                vertical-align: middle;
            }

            .er-row-total th,
            .er-row-total td {
                border-top: 2px solid #212529;
                font-weight: 700;
            }

            .er-row-sub td {
                color: #6c757d;
                padding-left: 2rem;
            }

            .er-row-grupo td {
                font-weight: 600;
            }

            .er-row-grupo.er-row-sub td {
                padding-left: 2rem;
            }

            .er-row-sub.er-row-detalle td {
                padding-left: 3.25rem;
            }

            .er-row-pendiente td {
                font-style: italic;
            }

            .er-row-gastos-total td {
                border-top: 1px dashed #adb5bd;
                font-weight: 600;
            }

            .er-chart-wrap {
                height: 380px;
                position: relative;
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
                        <h1 class="mt-4">Estado de resultado <small id="erPeriodo" class="text-muted"></small></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Finanzas</li>
                            <li class="breadcrumb-item">Tableros</li>
                            <li class="breadcrumb-item active">Estado de resultado</li>
                        </ol>

                        <div id="erAlert" class="alert alert-danger d-none" role="alert"></div>

                        <div class="row g-3 mb-4" id="erResumenCards">
                            <div class="col-12 text-muted">Cargando estado de resultado...</div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-xl-7 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>
                                        Estado de resultado
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table er-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Concepto</th>
                                                        <th class="text-end" id="erColActual">Mes actual</th>
                                                        <th class="text-end" id="erColAnterior">Mes anterior</th>
                                                        <th class="text-end">Var. %</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="erTablaBody">
                                                    <tr><td colspan="4" class="text-muted">Cargando...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-5 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Evolución 12 meses
                                    </div>
                                    <div class="card-body">
                                        <div class="er-chart-wrap">
                                            <canvas id="erChart"></canvas>
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
                var endpoint = <?php echo json_encode(app_url('/bff/finanzas/estado-resultado.php')); ?>;
                var erChart = null;
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };
                var formatoMoneda = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                var formatoPorcentaje = new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
                var monthFormatter = new Intl.DateTimeFormat('es-AR', { month: 'short' });

                var formatearVariacion = function(variacion) {
                    if (variacion === null || variacion === undefined) {
                        return '<span class="text-muted">—</span>';
                    }
                    var positivo = variacion >= 0;
                    var tono = positivo ? 'success' : 'danger';
                    var icono = positivo ? 'fa-arrow-up' : 'fa-arrow-down';
                    return '<span class="text-' + tono + '"><i class="fas ' + icono + ' me-1"></i>' +
                        escapeHtml(formatoPorcentaje.format(Math.abs(variacion))) + '%</span>';
                };

                var filaSimple = function(concepto, actual, anterior, variacion, claseFila) {
                    return '' +
                        '<tr class="' + (claseFila || '') + '">' +
                            '<td>' + escapeHtml(concepto) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoMoneda.format(actual || 0)) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoMoneda.format(anterior || 0)) + '</td>' +
                            '<td class="text-end">' + formatearVariacion(variacion) + '</td>' +
                        '</tr>';
                };

                var filaPorcentaje = function(concepto, actual, anterior) {
                    return '' +
                        '<tr>' +
                            '<td>' + escapeHtml(concepto) + '</td>' +
                            '<td class="text-end">' + escapeHtml(formatoPorcentaje.format(actual || 0)) + '%</td>' +
                            '<td class="text-end">' + escapeHtml(formatoPorcentaje.format(anterior || 0)) + '%</td>' +
                            '<td class="text-end">—</td>' +
                        '</tr>';
                };

                var filaPendiente = function(concepto) {
                    return '' +
                        '<tr class="er-row-pendiente">' +
                            '<td>' + escapeHtml(concepto) + '</td>' +
                            '<td class="text-end text-muted" colspan="3">Sin implementar</td>' +
                        '</tr>';
                };

                var mapaGastosPorClase = function(filas) {
                    var mapa = {};
                    $.each(filas || [], function(index, fila) {
                        mapa[fila.clase] = fila.monto;
                    });
                    return mapa;
                };

                var renderTabla = function(data) {
                    var actual = data.actual;
                    var anterior = data.anterior;
                    var variaciones = data.variaciones;
                    var html = '';

                    html += filaSimple('Ventas', actual.ventas, anterior.ventas, variaciones.ventas);
                    html += filaSimple('Comiquería', actual.ventasLocal, anterior.ventasLocal, null, 'er-row-sub');
                    html += filaSimple('Costo de ventas', actual.costoVentas, anterior.costoVentas, variaciones.costoVentas);
                    html += filaSimple('Comiquería', actual.costoVentasLocal, anterior.costoVentasLocal, null, 'er-row-sub');
                    html += filaSimple('[1] Ganancia bruta', actual.gananciaBruta, anterior.gananciaBruta, variaciones.gananciaBruta, 'er-row-total');
                    html += filaPorcentaje('Margen bruto', actual.margenBruto, anterior.margenBruto);

                    var gastosPorGrupo = function(filasActual, filasAnterior) {
                        var clases = {};
                        $.each(filasActual, function(index, fila) { clases[fila.clase] = true; });
                        $.each(filasAnterior, function(index, fila) { clases[fila.clase] = true; });
                        var mapaActual = mapaGastosPorClase(filasActual);
                        var mapaAnterior = mapaGastosPorClase(filasAnterior);
                        var nombresClases = Object.keys(clases).sort(function(a, b) {
                            return (mapaActual[b] || 0) - (mapaActual[a] || 0);
                        });

                        var filasHtml = '';
                        $.each(nombresClases, function(index, clase) {
                            filasHtml += filaSimple(clase, mapaActual[clase] || 0, mapaAnterior[clase] || 0, null, 'er-row-sub er-row-detalle');
                        });

                        return filasHtml;
                    };

                    html += filaSimple('Comiquería', actual.gastosLocal, anterior.gastosLocal, null, 'er-row-sub er-row-grupo');
                    html += gastosPorGrupo(actual.gastosPorClase, anterior.gastosPorClase);
                    html += filaSimple('[2] Total gastos Operativos', actual.gastos, anterior.gastos, variaciones.gastos, 'er-row-gastos-total');
                    html += filaSimple('[3] Resultado Operativo [1] - [2]', actual.resultado, anterior.resultado, variaciones.resultado, 'er-row-total');

                    // Gastos financieros: todavia no hay fuente de datos, se fija en 0 hasta implementarlo.
                    var gastosFinancierosActual = 0;
                    var gastosFinancierosAnterior = 0;
                    html += filaSimple('[4] Gastos financieros', gastosFinancierosActual, gastosFinancierosAnterior, null, '');
                    html += filaPendiente('Detalle de deudas');
                    var resultadoAntesImpuestosActual = actual.resultado - gastosFinancierosActual;
                    var resultadoAntesImpuestosAnterior = anterior.resultado - gastosFinancierosAnterior;
                    html += filaSimple(
                        '[5] Resultado antes de Impuestos [3] - [4]',
                        resultadoAntesImpuestosActual,
                        resultadoAntesImpuestosAnterior,
                        variaciones.resultado,
                        'er-row-total'
                    );

                    html += filaSimple(
                        '[6] Resultado Neto [5]',
                        resultadoAntesImpuestosActual,
                        resultadoAntesImpuestosAnterior,
                        null,
                        'er-row-total'
                    );

                    html += filaPorcentaje('Rentabilidad', actual.rentabilidad, anterior.rentabilidad);

                    $('#erTablaBody').html(html);
                    $('#erColActual').text(data.periodoEtiqueta);
                    $('#erColAnterior').text(data.periodoAnteriorEtiqueta);
                };

                var tarjeta = function(titulo, valor, formato, tono, icono) {
                    var valorTexto = formato === 'porcentaje'
                        ? formatoPorcentaje.format(valor || 0) + '%'
                        : formatoMoneda.format(valor || 0);

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
                    var actual = data.actual;
                    var html = '' +
                        tarjeta('Ventas', actual.ventas, 'moneda', 'success', 'fa-chart-line') +
                        tarjeta('Ganancia bruta', actual.gananciaBruta, 'moneda', 'primary', 'fa-sack-dollar') +
                        tarjeta('Total gastos', actual.gastos, 'moneda', 'danger', 'fa-money-bill-transfer') +
                        tarjeta('Resultado neto', actual.resultado, 'moneda', actual.resultado >= 0 ? 'success' : 'danger', 'fa-scale-balanced');
                    $('#erResumenCards').html(html);
                };

                var renderChart = function(evolucion) {
                    var labels = $.map(evolucion, function(mes) {
                        return monthFormatter.format(new Date(mes.mes + '-01T12:00:00'));
                    });
                    var ventas = $.map(evolucion, function(mes) { return mes.ventas; });
                    var costosYGastos = $.map(evolucion, function(mes) { return mes.costoVentas + mes.gastos; });
                    var resultado = $.map(evolucion, function(mes) { return mes.resultado; });
                    var canvas = document.getElementById('erChart');

                    if (!canvas) {
                        return;
                    }
                    if (erChart) {
                        erChart.destroy();
                    }

                    erChart = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ventas',
                                    data: ventas,
                                    backgroundColor: 'rgba(25, 135, 84, .75)'
                                },
                                {
                                    label: 'Costos + Gastos',
                                    data: costosYGastos,
                                    backgroundColor: 'rgba(220, 53, 69, .75)'
                                },
                                {
                                    label: 'Resultado',
                                    data: resultado,
                                    type: 'line',
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    backgroundColor: 'rgba(13, 110, 253, .1)',
                                    borderWidth: 3,
                                    tension: .25,
                                    pointRadius: 3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(value) {
                                            return formatoMoneda.format(value);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + formatoMoneda.format(context.parsed.y);
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
                            $('#erAlert').addClass('d-none').text('');
                            $('#erPeriodo').text(data.periodoEtiqueta ? '(' + data.periodoEtiqueta + ')' : '');
                            renderCards(data);
                            renderTabla(data);
                            renderChart(data.evolucion);
                        })
                        .fail(function() {
                            $('#erAlert').removeClass('d-none').text('No se pudo cargar el estado de resultado.');
                            $('#erResumenCards').html('');
                            $('#erTablaBody').html('');
                        });
                };

                cargar();
            });
        </script>
    </body>
</html>
