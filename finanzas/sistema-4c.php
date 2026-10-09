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
        <meta name="description" content="Sistema 4C" />
        <title>Sistema 4C</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            .s4c-card {
                min-height: 150px;
            }

            @media (min-width: 992px) {
                .s4c-card-col {
                    flex: 0 0 20%;
                    max-width: 20%;
                }
            }

            .s4c-card-title {
                align-items: center;
                display: flex;
                font-size: .72rem;
                font-weight: 700;
                gap: .45rem;
                letter-spacing: .02em;
                margin-bottom: .75rem;
                text-transform: uppercase;
            }

            .s4c-card-icon {
                opacity: .9;
            }

            .s4c-card-row {
                align-items: baseline;
                border-top: 1px solid rgba(255, 255, 255, .25);
                display: flex;
                gap: 1rem;
                justify-content: space-between;
                padding: .45rem 0;
            }

            .s4c-card-row:first-of-type {
                border-top: 0;
            }

            .s4c-card-row span {
                color: rgba(255, 255, 255, .82);
                font-size: .82rem;
                min-width: 0;
            }

            .s4c-card-row strong {
                color: #fff;
                flex: 0 0 auto;
                font-size: 1.2rem;
                text-align: right;
                white-space: nowrap;
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
                        <h1 class="mt-4">Sistema 4C <small id="s4cPeriodo" class="text-muted"></small></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Finanzas</li>
                            <li class="breadcrumb-item">Tableros</li>
                            <li class="breadcrumb-item active">Sistema 4C</li>
                        </ol>

                        <div id="s4cAlert" class="alert alert-danger d-none" role="alert"></div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="card shadow h-100">
                                    <div class="card-body">
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-cash-register me-1"></i>
                                            Total vendido del mes
                                        </div>
                                        <div class="fs-3 fw-bold text-dark" id="s4cTotalVendido">—</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card shadow h-100">
                                    <div class="card-body">
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-gears me-1"></i>
                                            Gastos Operativos Reales
                                        </div>
                                        <div class="fs-3 fw-bold text-dark" id="s4cGastosOperativos">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4" id="s4cCards">
                            <div class="col-12 text-muted">Cargando Sistema 4C...</div>
                        </div>
                    </div>
                </main>
                <?php include __DIR__ . '/../footer.php'; ?>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                var endpoint = <?php echo json_encode(app_url('/bff/finanzas/sistema-4c.php')); ?>;
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

                var tarjeta = function(card) {
                    var tieneSegundaFila = card.segundaFilaValor !== undefined && card.segundaFilaValor !== null;
                    var etiquetaObjetivo = card.primeraFilaEtiqueta
                        ? escapeHtml(card.primeraFilaEtiqueta)
                        : (tieneSegundaFila
                            ? 'Objetivo (' + escapeHtml(card.porcentaje) + '%)'
                            : escapeHtml(card.porcentaje) + '% del total vendido');
                    var filaSegunda = '';

                    if (tieneSegundaFila) {
                        var valorSegundaFila = card.segundaFilaFormato === 'porcentaje'
                            ? escapeHtml(formatoPorcentaje.format(card.segundaFilaValor || 0)) + '%'
                            : escapeHtml(formatoMoneda.format(card.segundaFilaValor || 0));
                        filaSegunda = '' +
                            '<div class="s4c-card-row">' +
                                '<span>' + escapeHtml(card.segundaFilaEtiqueta || '') + '</span>' +
                                '<strong>' + valorSegundaFila + '</strong>' +
                            '</div>';
                    }

                    return '' +
                        '<div class="col-12 col-md-6 s4c-card-col mb-4">' +
                            '<div class="card s4c-card bg-' + escapeHtml(card.tono) + ' text-white shadow h-100">' +
                                '<div class="card-body">' +
                                    '<div class="s4c-card-title text-white">' +
                                        '<i class="fas ' + escapeHtml(card.icono) + ' s4c-card-icon text-white"></i>' +
                                        '<span>' + escapeHtml(card.titulo) + '</span>' +
                                    '</div>' +
                                    '<div class="s4c-card-row">' +
                                        '<span>' + etiquetaObjetivo + '</span>' +
                                        '<strong>' + escapeHtml(formatoMoneda.format(card.valor || 0)) + '</strong>' +
                                    '</div>' +
                                    filaSegunda +
                                '</div>' +
                            '</div>' +
                        '</div>';
                };

                var renderCards = function(cards) {
                    var html = '';
                    $.each(cards, function(index, card) {
                        html += tarjeta(card);
                    });
                    $('#s4cCards').html(html);
                };

                var cargar = function() {
                    $.getJSON(endpoint)
                        .done(function(response) {
                            var data = response.data;
                            $('#s4cAlert').addClass('d-none').text('');
                            $('#s4cPeriodo').text(data.periodoEtiqueta ? '(' + data.periodoEtiqueta + ')' : '');
                            $('#s4cTotalVendido').text(formatoMoneda.format(data.ventasTotal || 0));
                            $('#s4cGastosOperativos').text(formatoMoneda.format((data.gastosOperativos && data.gastosOperativos.total) || 0));
                            renderCards(data.cards);
                        })
                        .fail(function() {
                            $('#s4cAlert').removeClass('d-none').text('No se pudo cargar el Sistema 4C.');
                            $('#s4cCards').html('');
                            $('#s4cTotalVendido').text('—');
                            $('#s4cGastosOperativos').text('—');
                        });
                };

                cargar();
            });
        </script>
    </body>
</html>
