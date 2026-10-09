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
        <meta name="description" content="Dashboard financiero" />
        <title>Dashboard financiero</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .financial-module {
                border: 0;
                transition: transform .2s ease, box-shadow .2s ease;
            }
            .financial-module:hover {
                box-shadow: 0 .75rem 1.5rem rgba(15, 23, 42, .12) !important;
                transform: translateY(-3px);
            }
            .financial-module-icon {
                align-items: center;
                border-radius: 1rem;
                display: inline-flex;
                font-size: 1.5rem;
                height: 3.5rem;
                justify-content: center;
                width: 3.5rem;
            }
            .financial-value {
                font-size: clamp(1.45rem, 2vw, 2rem);
                font-weight: 700;
            }
            .financial-traffic-light {
                align-items: center;
                display: flex;
                gap: .5rem;
            }
            .financial-traffic-light-dot {
                border: 2px solid rgba(255, 255, 255, .85);
                border-radius: 50%;
                box-shadow: 0 0 0 1px rgba(15, 23, 42, .18);
                flex: 0 0 auto;
                height: .9rem;
                width: .9rem;
            }
            .financial-chart {
                min-height: 320px;
                position: relative;
            }
            .financial-detail-group {
                border-bottom: 1px solid #e9ecef;
                margin-bottom: .75rem;
                padding-bottom: .75rem;
            }
            .financial-detail-source {
                color: #6c757d;
                padding-left: 1rem;
                position: relative;
            }
            .financial-detail-source::before {
                color: #adb5bd;
                content: "↳";
                left: 0;
                position: absolute;
            }
            .financial-loading {
                animation: financial-pulse 1.4s ease-in-out infinite;
                background: #e9ecef;
                border-radius: .5rem;
                height: 2rem;
                width: 70%;
            }
            @keyframes financial-pulse {
                50% { opacity: .45; }
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
                        <h1 class="mt-4">Dashboard financiero</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Finanzas</li>
                            <li class="breadcrumb-item">Rentabilidad</li>
                            <li class="breadcrumb-item active">Dashboard financiero</li>
                        </ol>

                        <div id="financialAlert" class="alert alert-danger d-none" role="alert"></div>

                        <div class="row g-4 mb-4" id="financialCards" aria-live="polite">
                            <?php for ($card = 0; $card < 8; $card++) { ?>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="card financial-module h-100 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="financial-loading mb-3"></div>
                                            <div class="financial-loading mb-3" style="width: 90%;"></div>
                                            <div class="financial-loading" style="width: 55%; height: 1rem;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-8">
                                <section class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 px-4 pt-4">
                                        <h2 class="h5 mb-1">Evolución financiera</h2>
                                        <p class="text-muted small mb-0">Ventas locales y resultado neto del año actual.</p>
                                    </div>
                                    <div class="card-body p-4 financial-chart">
                                        <canvas id="financialChart"></canvas>
                                    </div>
                                </section>
                            </div>
                            <div class="col-12 col-xl-4">
                                <section class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 px-4 pt-4">
                                        <h2 class="h5 mb-1">Detalle del período</h2>
                                        <p class="text-muted small mb-0" id="financialPeriod">Cargando información...</p>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-0" id="financialDetail">
                                            <div class="row">
                                                <div class="col-7 text-muted">Cargando detalle...</div>
                                                <div class="col-5 text-end">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>

                    </div>
                </main>

                <?php include __DIR__ . '/../footer.php'; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" crossorigin="anonymous"></script>
        <script>
            (function () {
                const endpoint = <?php echo json_encode(app_url('/bff/finanzas/dashboard.php')); ?>;
                const cardsContainer = document.getElementById('financialCards');
                const detailContainer = document.getElementById('financialDetail');
                const alertContainer = document.getElementById('financialAlert');
                const periodContainer = document.getElementById('financialPeriod');
                let chart = null;

                const moneyFormatter = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    maximumFractionDigits: 0
                });
                const numberFormatter = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 1 });
                const integerFormatter = new Intl.NumberFormat('es-AR');
                const monthFormatter = new Intl.DateTimeFormat('es-AR', { month: 'short' });

                function escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function formatValue(value, format) {
                    return format === 'porcentaje'
                        ? numberFormatter.format(value) + ' %'
                        : moneyFormatter.format(value);
                }

                function variationMarkup(variation, inverse) {
                    if (variation === null) {
                        return '<span class="text-muted">Sin período comparable</span>';
                    }
                    const positive = variation >= 0;
                    const favorable = inverse ? !positive : positive;
                    const tone = favorable ? 'success' : 'danger';
                    const icon = positive ? 'fa-arrow-up' : 'fa-arrow-down';
                    return '<span class="text-' + tone + '"><i class="fas ' + icon + ' me-1"></i>'
                        + numberFormatter.format(Math.abs(variation)) + '% vs. mes anterior</span>';
                }

                function trafficLightMarkup(trafficLight) {
                    if (!trafficLight) {
                        return '';
                    }
                    const percentage = trafficLight.porcentaje === null
                        ? 'Sin punto de equilibrio'
                        : numberFormatter.format(trafficLight.porcentaje) + '% '
                            + escapeHtml(trafficLight.descripcion);
                    return '<div class="financial-traffic-light small mb-2">'
                        + '<span class="financial-traffic-light-dot bg-'
                        + escapeHtml(trafficLight.tono) + '" aria-hidden="true"></span>'
                        + '<span class="text-muted">' + percentage + '</span></div>';
                }

                function renderCards(cards) {
                    cardsContainer.innerHTML = cards.map(function (card) {
                        return '<div class="col-12 col-md-6 col-xl-3">'
                            + '<article class="card financial-module h-100 shadow-sm"><div class="card-body p-4">'
                            + '<span class="financial-module-icon bg-' + escapeHtml(card.tono)
                            + ' bg-opacity-10 text-' + escapeHtml(card.tono) + ' mb-3">'
                            + '<i class="fas ' + escapeHtml(card.icono) + '"></i></span>'
                            + '<p class="text-muted mb-2">' + escapeHtml(card.titulo) + '</p>'
                            + '<div class="financial-value mb-2">' + formatValue(card.valor, card.formato) + '</div>'
                            + trafficLightMarkup(card.semaforo)
                            + '<div class="small">' + variationMarkup(card.variacion, card.tendenciaInversa === true) + '</div>'
                            + '</div></article></div>';
                    }).join('');
                }

                function renderDetail(detail) {
                    const facturacionMarkup = '<div class="financial-detail-group">'
                        + '<div class="row mb-2">'
                        + '<div class="col-7 fw-semibold">Facturación total</div>'
                        + '<div class="col-5 text-end fw-bold">'
                        + escapeHtml(moneyFormatter.format(detail.facturacion)) + '</div></div>'
                        + '<div class="row small">'
                        + '<div class="col-7 financial-detail-source">Comiqueria</div>'
                        + '<div class="col-5 text-end">'
                        + escapeHtml(moneyFormatter.format(detail.facturacionLocal)) + '</div></div></div>';
                    const rows = [
                        ['Costo registrado local', moneyFormatter.format(detail.costoVentas)],
                        ['Costos fijos', moneyFormatter.format(detail.costosFijos)],
                        ['Ganancia bruta', moneyFormatter.format(detail.gananciaBruta)],
                        ['Descuentos', moneyFormatter.format(detail.descuentos)],
                        ['Operaciones', integerFormatter.format(detail.operaciones)],
                        ['Presupuesto', moneyFormatter.format(detail.presupuesto)],
                        ['Presupuesto ejecutado', numberFormatter.format(detail.ejecucionPresupuesto) + ' %'],
                        ['Saldo en cuentas', moneyFormatter.format(detail.saldoCuentas)]
                    ];
                    detailContainer.innerHTML = facturacionMarkup + rows.map(function (row) {
                        return '<div class="row mb-2">'
                            + '<div class="col-7 text-muted">' + escapeHtml(row[0]) + '</div>'
                            + '<div class="col-5 text-end fw-semibold">' + escapeHtml(row[1]) + '</div>'
                            + '</div>';
                    }).join('');
                }

                function renderChart(graph) {
                    const labels = graph.etiquetas.map(function (month) {
                        return monthFormatter.format(new Date(month + '-01T12:00:00'));
                    });
                    const colors = {
                        ventas: { border: '#198754', background: 'rgba(25, 135, 84, .12)' },
                        resultado: { border: '#0d6efd', background: 'rgba(13, 110, 253, .10)' }
                    };
                    if (chart) {
                        chart.destroy();
                    }
                    chart = new Chart(document.getElementById('financialChart'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: graph.series.map(function (serie) {
                                const color = colors[serie.clave];
                                return {
                                    label: serie.titulo,
                                    data: serie.valores,
                                    borderColor: color.border,
                                    backgroundColor: color.background,
                                    borderWidth: 2,
                                    fill: true,
                                    tension: .3
                                };
                            })
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function (value) {
                                            return moneyFormatter.format(value);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return context.dataset.label + ': ' + moneyFormatter.format(context.parsed.y);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                function showError(message) {
                    alertContainer.textContent = message;
                    alertContainer.classList.remove('d-none');
                    cardsContainer.innerHTML = '';
                }

                fetch(endpoint, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('No se pudo obtener el resumen financiero.');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        const data = payload.data;
                        renderCards(data.tarjetas);
                        renderDetail(data.detalle);
                        renderChart(data.grafico);
                        periodContainer.textContent = 'Período ' + data.periodo;
                    })
                    .catch(function (error) {
                        showError(error.message);
                    });
            }());
        </script>
    </body>
</html>
