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
        <meta name="description" content="Ticket promedio" />
        <title>Ticket promedio</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .ticket-card {
                min-height: 190px;
            }

            @media (min-width: 1200px) {
                .ticket-card-col {
                    flex: 0 0 20%;
                    max-width: 20%;
                }
            }

            .ticket-card-title {
                align-items: center;
                display: flex;
                font-size: .72rem;
                font-weight: 700;
                gap: .45rem;
                letter-spacing: .02em;
                margin-bottom: .75rem;
                text-transform: uppercase;
            }

            .ticket-card-icon {
                opacity: .9;
            }

            .ticket-card-row {
                align-items: baseline;
                border-top: 1px solid rgba(255, 255, 255, .25);
                display: flex;
                gap: 1rem;
                justify-content: space-between;
                padding: .45rem 0;
            }

            .ticket-card-row:first-of-type {
                border-top: 0;
            }

            .ticket-card-row-glued {
                border-top: 0;
            }

            .ticket-card-row span {
                color: rgba(255, 255, 255, .82);
                font-size: .82rem;
                min-width: 0;
            }

            .ticket-card-row strong {
                color: #fff;
                flex: 0 0 auto;
                font-size: .98rem;
                text-align: right;
                white-space: nowrap;
            }

            .ticket-card-row-total strong {
                font-size: 1.2rem;
            }

            .ticket-card-edit {
                align-items: center;
                background: transparent;
                border: 0;
                color: inherit;
                display: inline-flex;
                height: 1.6rem;
                justify-content: center;
                opacity: .85;
                width: 1.6rem;
            }

            .ticket-card-edit:hover,
            .ticket-card-edit:focus {
                background: transparent;
                color: inherit;
                opacity: 1;
            }

            .ticket-chart-wrap {
                height: 420px;
                position: relative;
            }

            .ticket-progreso-caption {
                color: rgba(255, 255, 255, .82);
                font-size: .68rem;
                font-weight: 700;
                letter-spacing: .02em;
                margin-top: .55rem;
                text-transform: capitalize;
            }

            .ticket-progreso {
                background: rgba(255, 255, 255, .72);
                border: 1px solid rgba(0, 0, 0, .45);
                border-radius: 999px;
                box-shadow: inset 0 .1rem .2rem rgba(0, 0, 0, .2);
                height: 1.1rem;
                margin-top: .55rem;
                overflow: hidden;
                position: relative;
            }

            .ticket-progreso-fill {
                box-shadow: inset 0 -.08rem .12rem rgba(0, 0, 0, .3), 1px 0 0 rgba(0, 0, 0, .35);
                height: 100%;
                max-width: 100%;
            }

            .ticket-progreso-label {
                align-items: center;
                color: #fff;
                display: flex;
                font-size: .68rem;
                font-weight: 700;
                height: 100%;
                justify-content: center;
                left: 0;
                position: absolute;
                text-shadow: 0 1px 2px rgba(0, 0, 0, .55);
                top: 0;
                width: 100%;
            }

            .ticket-progreso-ok .ticket-progreso-fill {
                background: #2ecc71;
            }

            .ticket-progreso-alerta .ticket-progreso-fill {
                background: #ffc107;
            }

            .ticket-progreso-riesgo .ticket-progreso-fill,
            .ticket-progreso-sin_objetivo .ticket-progreso-fill {
                background: #dc3545;
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
                        <h1 class="mt-4">Ticket promedio <small id="ticketPromedioPeriodo" class="text-muted"></small></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Finanzas</li>
                            <li class="breadcrumb-item">Tableros</li>
                            <li class="breadcrumb-item active">Ticket promedio</li>
                        </ol>

                        <div id="ticketPromedioAlert" class="alert alert-danger d-none" role="alert"></div>

                        <h5 class="mt-3 mb-3">Mes actual</h5>
                        <div class="row mb-4" id="ticketPromedioCards">
                            <div class="col-12 text-muted">Cargando tablero...</div>
                        </div>

                        <h5 class="mt-2 mb-3">Hoy</h5>
                        <div class="row mb-4" id="ticketPromedioCardsHoy">
                            <div class="col-12 text-muted">Cargando tablero...</div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="card shadow">
                                    <div class="card-header">
                                        <i class="fas fa-chart-column me-1"></i>
                                        Ultimos 30 dias
                                    </div>
                                    <div class="card-body">
                                        <div class="ticket-chart-wrap">
                                            <canvas id="ticketPromedioChartDiario"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="card shadow">
                                    <div class="card-header">
                                        <i class="fas fa-chart-column me-1"></i>
                                        Evolucion ultimos 12 meses
                                    </div>
                                    <div class="card-body">
                                        <div class="ticket-chart-wrap">
                                            <canvas id="ticketPromedioChart"></canvas>
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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function() {
                var endpoint = <?php echo json_encode(app_url('/bff/finanzas/ticket-promedio.php')); ?>;
                var ticketPromedioChart = null;
                var ticketPromedioChartDiario = null;
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

                var formatearValor = function(valor, formato) {
                    if (formato === 'moneda') {
                        return formatoMoneda.format(valor || 0);
                    }

                    return formatoNumero.format(valor || 0);
                };
                var normalizarNumeroEditable = function(valor) {
                    var texto = String(valor || '').trim();
                    if (texto.indexOf(',') >= 0) {
                        texto = texto.replace(/\./g, '').replace(',', '.');
                    }
                    return texto;
                };
                var formatearPeriodo = function(periodo) {
                    var meses = {
                        '01': 'Ene',
                        '02': 'Feb',
                        '03': 'Mar',
                        '04': 'Abr',
                        '05': 'May',
                        '06': 'Jun',
                        '07': 'Jul',
                        '08': 'Ago',
                        '09': 'Sep',
                        '10': 'Oct',
                        '11': 'Nov',
                        '12': 'Dic'
                    };
                    var partes = (periodo || '').split('-');
                    return partes.length === 2 && meses[partes[1]]
                        ? meses[partes[1]] + ' ' + partes[0]
                        : periodo;
                };
                var renderChart = function(evolucion) {
                    var rows = (evolucion || []).slice().reverse();
                    var labels = $.map(rows, function(row) {
                        return formatearPeriodo(row.periodo);
                    });
                    var comiqueria = $.map(rows, function(row) {
                        return row.local ? Number(row.local.ventas || 0) : 0;
                    });
                    var ventas = $.map(rows, function(row) {
                        return row.consolidado ? Number(row.consolidado.operaciones || 0) : 0;
                    });
                    var canvas = document.getElementById('ticketPromedioChart');

                    if (!canvas) {
                        return;
                    }
                    if (ticketPromedioChart) {
                        ticketPromedioChart.destroy();
                    }

                    ticketPromedioChart = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Comiqueria',
                                    data: comiqueria,
                                    backgroundColor: 'rgba(13, 110, 253, .78)',
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    borderWidth: 1,
                                    yAxisID: 'y-axis-montos'
                                },
                                {
                                    label: 'Ventas',
                                    data: ventas,
                                    type: 'line',
                                    borderColor: 'rgba(220, 53, 69, 1)',
                                    backgroundColor: 'rgba(220, 53, 69, .08)',
                                    borderWidth: 3,
                                    lineTension: .2,
                                    pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                                    pointRadius: 4,
                                    yAxisID: 'y-axis-ventas'
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            legend: {
                                position: 'bottom'
                            },
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var dataset = data.datasets[tooltipItem.datasetIndex];
                                        var value = tooltipItem.yLabel || 0;
                                        if (dataset.yAxisID === 'y-axis-montos') {
                                            return dataset.label + ': ' + formatoMoneda.format(value);
                                        }

                                        return dataset.label + ': ' + formatoNumero.format(value);
                                    }
                                }
                            },
                            scales: {
                                xAxes: [{
                                    stacked: false,
                                    gridLines: {
                                        display: false
                                    }
                                }],
                                yAxes: [
                                    {
                                        id: 'y-axis-montos',
                                        position: 'left',
                                        ticks: {
                                            beginAtZero: true,
                                            callback: function(value) {
                                                return formatoMoneda.format(value);
                                            }
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Montos'
                                        }
                                    },
                                    {
                                        id: 'y-axis-ventas',
                                        position: 'right',
                                        ticks: {
                                            beginAtZero: true,
                                            precision: 0,
                                            callback: function(value) {
                                                return formatoNumero.format(value);
                                            }
                                        },
                                        gridLines: {
                                            drawOnChartArea: false
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Ventas'
                                        }
                                    }
                                ]
                            }
                        }
                    });
                };
                var renderChartDiario = function(evolucionDiaria) {
                    var rows = evolucionDiaria || [];
                    var labels = $.map(rows, function(row) {
                        return row.etiqueta || row.periodo;
                    });
                    var comiqueria = $.map(rows, function(row) {
                        return row.local ? Number(row.local.ventas || 0) : 0;
                    });
                    var ventas = $.map(rows, function(row) {
                        return row.consolidado ? Number(row.consolidado.operaciones || 0) : 0;
                    });
                    var canvas = document.getElementById('ticketPromedioChartDiario');

                    if (!canvas) {
                        return;
                    }
                    if (ticketPromedioChartDiario) {
                        ticketPromedioChartDiario.destroy();
                    }

                    ticketPromedioChartDiario = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Comiqueria',
                                    data: comiqueria,
                                    backgroundColor: 'rgba(13, 110, 253, .78)',
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    borderWidth: 1,
                                    yAxisID: 'y-axis-montos'
                                },
                                {
                                    label: 'Ventas',
                                    data: ventas,
                                    type: 'line',
                                    borderColor: 'rgba(220, 53, 69, 1)',
                                    backgroundColor: 'rgba(220, 53, 69, .08)',
                                    borderWidth: 3,
                                    lineTension: .2,
                                    pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                                    pointRadius: 4,
                                    yAxisID: 'y-axis-ventas'
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            legend: {
                                position: 'bottom'
                            },
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var dataset = data.datasets[tooltipItem.datasetIndex];
                                        var value = tooltipItem.yLabel || 0;
                                        if (dataset.yAxisID === 'y-axis-montos') {
                                            return dataset.label + ': ' + formatoMoneda.format(value);
                                        }

                                        return dataset.label + ': ' + formatoNumero.format(value);
                                    }
                                }
                            },
                            scales: {
                                xAxes: [{
                                    stacked: false,
                                    gridLines: {
                                        display: false
                                    }
                                }],
                                yAxes: [
                                    {
                                        id: 'y-axis-montos',
                                        position: 'left',
                                        ticks: {
                                            beginAtZero: true,
                                            callback: function(value) {
                                                return formatoMoneda.format(value);
                                            }
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Montos'
                                        }
                                    },
                                    {
                                        id: 'y-axis-ventas',
                                        position: 'right',
                                        ticks: {
                                            beginAtZero: true,
                                            precision: 0,
                                            callback: function(value) {
                                                return formatoNumero.format(value);
                                            }
                                        },
                                        gridLines: {
                                            drawOnChartArea: false
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Ventas'
                                        }
                                    }
                                ]
                            }
                        }
                    });
                };
                var diasEnMesActual = function() {
                    var ahora = new Date();
                    return new Date(ahora.getFullYear(), ahora.getMonth() + 1, 0).getDate();
                };

                var calcularKpiDiario = function(objetivoMensual) {
                    var dias = diasEnMesActual();
                    return dias > 0 ? objetivoMensual / dias : 0;
                };

                var construirMapaObjetivos = function(cards) {
                    var mapa = {};
                    $.each(cards || [], function(index, c) {
                        mapa[c.clave] = c.objetivo ? Number(c.objetivo.objetivo || 0) : 0;
                    });
                    return mapa;
                };

                // Promedio de Venta y Promedio por Persona son promedios (monto / cantidad):
                // su diario no sale de dividir su propio objetivo mensual por los dias, sino
                // del cociente entre el diario de Monto total vendido y el diario de Ventas/Personas.
                var obtenerObjetivoDiario = function(cardData, mapaObjetivos) {
                    var objetivoMensual = cardData.objetivo ? Number(cardData.objetivo.objetivo || 0) : 0;

                    if (cardData.clave === 'ticketPromedio') {
                        var montoDiario = calcularKpiDiario(mapaObjetivos.ventas || 0);
                        var operacionesDiario = calcularKpiDiario(mapaObjetivos.operaciones || 0);
                        return operacionesDiario > 0 ? montoDiario / operacionesDiario : 0;
                    }

                    if (cardData.clave === 'promedioPersona') {
                        var montoDiarioPersona = calcularKpiDiario(mapaObjetivos.ventas || 0);
                        var personasDiario = calcularKpiDiario(mapaObjetivos.personas || 0);
                        return personasDiario > 0 ? montoDiarioPersona / personasDiario : 0;
                    }

                    return calcularKpiDiario(objetivoMensual);
                };

                // Para los promedios, el acumulado es el mismo cociente diario (un promedio no
                // se multiplica por los dias transcurridos); para el resto, si.
                var obtenerObjetivoAcumulado = function(cardData, mapaObjetivos) {
                    var diario = obtenerObjetivoDiario(cardData, mapaObjetivos);
                    if (cardData.clave === 'ticketPromedio' || cardData.clave === 'promedioPersona') {
                        return diario;
                    }

                    var diaHoy = new Date().getDate();
                    return diario * diaHoy;
                };

                var renderBarraProgreso = function(porcentaje, estado) {
                    var ancho = Math.max(0, Math.min(100, porcentaje));

                    return '' +
                        '<div class="ticket-progreso ticket-progreso-' + escapeHtml(estado) + '">' +
                            '<div class="ticket-progreso-fill" style="width: ' + escapeHtml(ancho) + '%"></div>' +
                            '<span class="ticket-progreso-label">' + escapeHtml(formatoPorcentaje.format(porcentaje)) + '%</span>' +
                        '</div>';
                };

                var renderObjetivoProgreso = function(cardData) {
                    if (!cardData.objetivo) {
                        return '';
                    }

                    var objetivo = cardData.objetivo || {};
                    return renderBarraProgreso(Number(objetivo.porcentaje || 0), objetivo.estado || 'sin_objetivo');
                };

                var calcularEstadoSemaforo = function(porcentaje, tieneObjetivo) {
                    if (!tieneObjetivo) {
                        return 'sin_objetivo';
                    }
                    if (porcentaje > 80) {
                        return 'ok';
                    }
                    if (porcentaje >= 30) {
                        return 'alerta';
                    }
                    return 'riesgo';
                };

                var renderProgresoHoy = function(cardData, resumenHoy, mapaObjetivos) {
                    if (!cardData.objetivo || !resumenHoy) {
                        return '';
                    }

                    var kpiDiario = obtenerObjetivoDiario(cardData, mapaObjetivos);
                    var valorHoy = Number((resumenHoy.consolidado && resumenHoy.consolidado[cardData.clave]) || 0);
                    var porcentaje = kpiDiario > 0 ? Math.min(999, Math.round((valorHoy * 100 / kpiDiario) * 100) / 100) : 0;
                    var estado = calcularEstadoSemaforo(porcentaje, kpiDiario > 0);

                    return renderBarraProgreso(porcentaje, estado);
                };

                var renderProgresoAcumulado = function(cardData, resumen, mapaObjetivos) {
                    if (!cardData.objetivo || !resumen) {
                        return '';
                    }

                    var kpiAcumulado = obtenerObjetivoAcumulado(cardData, mapaObjetivos);
                    var valorActual = Number((resumen.consolidado && resumen.consolidado[cardData.clave]) || 0);
                    var porcentaje = kpiAcumulado > 0 ? Math.min(999, Math.round((valorActual * 100 / kpiAcumulado) * 100) / 100) : 0;
                    var estado = calcularEstadoSemaforo(porcentaje, kpiAcumulado > 0);
                    var kpiAcumuladoTexto = kpiAcumulado > 0 ? formatearValor(kpiAcumulado, cardData.formato) : 'Sin objetivo';
                    var valorTexto = formatearValor(valorActual, cardData.formato) + ' / ' + kpiAcumuladoTexto;

                    return '' +
                        '<div class="ticket-progreso-caption d-flex justify-content-between align-items-center">' +
                            '<span>Acumulado</span>' +
                            '<span>' + escapeHtml(valorTexto) + '</span>' +
                        '</div>' +
                        renderBarraProgreso(porcentaje, estado);
                };

                var card = function(cardData, resumen, tono, icono, modoObjetivo, mapaObjetivos) {
                    var clave = cardData.clave;
                    var formato = cardData.formato;
                    var objetivoHtml = '';
                    var progresoHoyHtml = '';
                    var totalTexto = formatearValor(resumen.consolidado[clave], formato);
                    var kpiMensualHtml = '';
                    var diarioHtml = '';
                    if (modoObjetivo === 'completo') {
                        progresoHoyHtml = renderProgresoHoy(cardData, resumen, mapaObjetivos);
                        if (cardData.objetivo) {
                            var kpiDiario = obtenerObjetivoDiario(cardData, mapaObjetivos);
                            var valorHoy = Number((resumen.consolidado && resumen.consolidado[clave]) || 0);
                            var kpiDiarioTexto = kpiDiario > 0 ? formatearValor(kpiDiario, formato) : 'Sin objetivo';
                            diarioHtml = '' +
                                '<div class="ticket-card-row ticket-card-row-glued">' +
                                    '<span>Diario</span>' +
                                    '<strong>' + escapeHtml(formatearValor(valorHoy, formato) + ' / ' + kpiDiarioTexto) + '</strong>' +
                                '</div>';
                        }
                    } else if (modoObjetivo === 'progreso') {
                        objetivoHtml = renderObjetivoProgreso(cardData) + renderProgresoAcumulado(cardData, resumen, mapaObjetivos);
                        if (cardData.objetivo) {
                            var objetivoValor = Number(cardData.objetivo.objetivo || 0);
                            var objetivoTexto = objetivoValor > 0 ? formatearValor(objetivoValor, formato) : 'Sin objetivo';
                            kpiMensualHtml = '' +
                                '<div class="ticket-card-row ticket-card-row-glued">' +
                                    '<span>Objetivo</span>' +
                                    '<span class="d-flex align-items-center gap-2">' +
                                        '<strong>' + escapeHtml(objetivoTexto) + '</strong>' +
                                        '<button type="button" class="ticket-card-edit" ' +
                                            'data-id="' + escapeHtml(cardData.objetivo.id || 0) + '" ' +
                                            'data-formato="' + escapeHtml(formato) + '" ' +
                                            'data-titulo="' + escapeHtml(cardData.titulo) + '" ' +
                                            'data-valor="' + escapeHtml(objetivoValor) + '" ' +
                                            'title="Editar objetivo">' +
                                            '<i class="fas fa-pen"></i>' +
                                        '</button>' +
                                    '</span>' +
                                '</div>';
                        }
                    }

                    return '' +
                        '<div class="col-12 col-md-6 ticket-card-col mb-4">' +
                            '<div class="card ticket-card bg-' + escapeHtml(tono) + ' text-white shadow h-100">' +
                                '<div class="card-body">' +
                                    '<div class="ticket-card-title text-white">' +
                                        '<i class="fas ' + escapeHtml(icono) + ' ticket-card-icon text-white"></i>' +
                                        '<span>' + escapeHtml(cardData.titulo) + '</span>' +
                                    '</div>' +
                                    '<div class="ticket-card-row ticket-card-row-total">' +
                                        '<strong>Total</strong>' +
                                        '<strong>' + escapeHtml(totalTexto) + '</strong>' +
                                    '</div>' +
                                    kpiMensualHtml +
                                    diarioHtml +
                                    progresoHoyHtml +
                                    objetivoHtml +
                                '</div>' +
                            '</div>' +
                        '</div>';
                };

                var cargarTablero = function() {
                    $.getJSON(endpoint)
                        .done(function(response) {
                        var data = response.data;
                        var resumen = data.resumen;
                        var tonos = ['primary', 'success', 'info', 'warning', 'secondary'];
                        var iconos = ['fa-cart-shopping', 'fa-receipt', 'fa-users', 'fa-user-check', 'fa-sack-dollar'];
                        $('#ticketPromedioPeriodo').text(data.periodoEtiqueta ? '(' + data.periodoEtiqueta + ')' : '');
                        $('#ticketPromedioAlert').addClass('d-none').text('');

                        var mapaObjetivos = construirMapaObjetivos(data.cards);

                        $('#ticketPromedioCards').html($.map(data.cards, function(cardData, index) {
                            return card(cardData, resumen, tonos[index] || 'primary', iconos[index] || 'fa-chart-line', 'progreso', mapaObjetivos);
                        }).join(''));
                        $('#ticketPromedioCardsHoy').html($.map(data.cardsHoy, function(cardData, index) {
                            return card(cardData, data.resumenHoy, tonos[index] || 'primary', iconos[index] || 'fa-chart-line', 'completo', mapaObjetivos);
                        }).join(''));
                        renderChart(data.evolucion);
                        renderChartDiario(data.evolucionDiaria);
                        })
                        .fail(function() {
                        $('#ticketPromedioAlert')
                            .removeClass('d-none')
                            .text('No se pudo cargar el tablero de ticket promedio.');
                        $('#ticketPromedioCards').html('');
                        $('#ticketPromedioCardsHoy').html('');
                        });
                };

                $(document).on('click', '.ticket-card-edit', function() {
                    var $button = $(this);
                    var id = Number($button.data('id') || 0);
                    var formato = $button.data('formato') || 'numero';
                    var titulo = $button.data('titulo') || 'Objetivo';
                    var valorActual = Number($button.data('valor') || 0);
                    var nuevoValor = window.prompt('Objetivo para ' + titulo, valorActual > 0 ? valorActual : '');

                    if (nuevoValor === null) {
                        return;
                    }
                    nuevoValor = normalizarNumeroEditable(nuevoValor);
                    if (nuevoValor === '' || isNaN(Number(nuevoValor)) || Number(nuevoValor) < 0) {
                        window.alert('Ingrese un objetivo valido.');
                        return;
                    }
                    if (!window.confirm('Este cambio ajustara los otros objetivos relacionados. ¿Continuar?')) {
                        return;
                    }

                    $button.prop('disabled', true);
                    $.post(endpoint, {
                        accion: 'guardarObjetivo',
                        id: id,
                        objetivo: nuevoValor,
                        formato: formato
                    })
                        .done(function() {
                            cargarTablero();
                        })
                        .fail(function() {
                            window.alert('No se pudo guardar el objetivo.');
                            $button.prop('disabled', false);
                        });
                });

                cargarTablero();
            });
        </script>
    </body>
</html>
