<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Liquidacion</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script>
            $(document).ready(function(e) {
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };

                var formatoMoneda = function(valor) {
                    return '$ ' + (parseFloat(valor) || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                };

                var renderCuentas = function(cuentas, total) {
                    var $lista = $('#listaCuentas');
                    $lista.empty();
                    $.each(cuentas, function(index, cuenta) {
                        $lista.append('<a href="#" class="list-group-item list-group-item-action">' + escapeHtml(cuenta.nombre) + ' : ' + formatoMoneda(cuenta.saldo) + '</a>');
                    });
                    $('#totalCuentas').text('Total : ' + formatoMoneda(total));
                };

                var renderCostos = function(costos, total) {
                    var $lista = $('#listaCostos');
                    $lista.empty();
                    $.each(costos, function(index, costo) {
                        $lista.append('<a href="#" class="list-group-item list-group-item-action">' + escapeHtml(costo.nombre) + ' : ' + formatoMoneda(costo.total) + '</a>');
                    });
                    $('#totalCostos').text('Total : ' + formatoMoneda(total));
                };

                var renderInversiones = function(inversiones) {
                    var $lista = $('#listaInversiones');
                    var $footer = $('#footerInversiones');
                    $lista.empty();
                    $footer.empty();

                    $.each(inversiones, function(index, inversion) {
                        $lista.append('<a href="#" class="list-group-item list-group-item-action">' + escapeHtml(inversion.nombre) + ' (' + inversion.porcentaje + '%) ' + formatoMoneda(inversion.valorizado) + '</a>');
                        $footer.append('<p class="card-text">Pagar ' + escapeHtml(inversion.nombre) + ' : ' + formatoMoneda(inversion.pagar) + '</p>');
                    });
                };

                $.getJSON('../bff/liquidaciones/liquidacion.php')
                    .done(function(response) {
                        var data = response.data || {};

                        renderCuentas(data.cuentas || [], data.totalCuentas || 0);
                        renderCostos(data.costos || [], data.totalCostos || 0);

                        $('#saldoEnCuentas').text('Saldo en Cuentas : ' + formatoMoneda(data.totalCuentas));
                        $('#disponiblePresupuesto').text('Presupuesto : ' + formatoMoneda(data.disponiblePresupuesto));
                        $('#disponibleDeudas').text('Deudas : ' + formatoMoneda(data.disponibleDeudas));
                        $('#disponibleDividendos').text('Dividendos : ' + formatoMoneda(data.disponibleDividendos));

                        var deudaMes = data.deudaMes || {};
                        $('#deudaMesDisponible').text('Disponible : ' + formatoMoneda(data.disponibleDeudas));
                        $('#deudaMesAle').text('Ale (' + (deudaMes.porcentajeAle || 0) + '%): ' + formatoMoneda(deudaMes.ale));
                        $('#deudaMesFlor').text('Flor (' + (deudaMes.porcentajeFlor || 0) + '%): ' + formatoMoneda(deudaMes.flor));
                        $('#pagoDeudaAle').text('Pagar Ale: ' + formatoMoneda(deudaMes.pagoAle));
                        $('#pagoDeudaFlor').text('Pagar Flor: ' + formatoMoneda(deudaMes.pagoFlor));

                        $('#disponibleInversiones').text('Disponible: ' + formatoMoneda(data.disponibleInversiones));
                        renderInversiones(data.inversiones || []);

                        var dividendos = data.dividendos || {};
                        $('#dividendosDisponible').text('Disponible: ' + formatoMoneda(dividendos.total));
                        $('#dividendosAle').text('Ale : ' + formatoMoneda(dividendos.ale));
                        $('#dividendosFlor').text('Flor : ' + formatoMoneda(dividendos.flor));
                    })
                    .fail(function() {
                        $('#listaCuentas').html('<p class="text-center text-danger p-3">No se pudo cargar la liquidacion.</p>');
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
                        <h4 class="mt-4">Liquidacion</h4>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Datos para el cierre del mes actual</li>
                        </ol>
                        <div class="row">

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-header">Cuentas</div>
                                    <div class="list-group list-group-flush" id="listaCuentas"></div>
                                    <div class="card-footer">
                                        <p class="card-text" id="totalCuentas">Total : $ 0,00</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-header">Costos y Gastos</div>
                                    <div class="list-group list-group-flush" id="listaCostos"></div>
                                    <div class="card-footer">
                                        <p class="card-text" id="totalCostos">Total : $ 0,00</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-header">Liquidacion</div>
                                    <div class="card-body">
                                        <p class="card-text" id="saldoEnCuentas">
                                            Saldo en Cuentas : $ 0,00
                                        </p>
                                    </div>

                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1" id="disponiblePresupuesto">Presupuesto : $ 0,00</h5>
                                                <small class="text-muted">[a Pozo]</small>
                                            </div>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1" id="disponibleDeudas">Deudas : $ 0,00</h5>
                                                <small class="text-muted">[2]</small>
                                            </div>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1" id="disponibleDividendos">Dividendos : $ 0,00</h5>
                                                <small class="text-muted">[3]</small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <hr>
                        <div class="row">

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-info text-white mb-4">
                                    <div class="card-header">Deudas Mes Corriente</div>
                                    <div class="card-body">
                                        <p class="card-text" id="deudaMesDisponible">
                                            Disponible : $ 0,00
                                        </p>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action" id="deudaMesAle">Ale (0%): $ 0,00</a>
                                        <a href="#" class="list-group-item list-group-item-action" id="deudaMesFlor">Flor (0%): $ 0,00</a>
                                    </div>
                                    <div class="card-footer">
                                        <p class="card-text" id="pagoDeudaAle">Pagar Ale: $ 0,00</p>
                                        <p class="card-text" id="pagoDeudaFlor">Pagar Flor: $ 0,00</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-header">Inversiones</div>
                                    <div class="card-body">
                                        <p class="card-text" id="disponibleInversiones">
                                        Disponible: $ 0,00
                                        </p>
                                    </div>
                                    <div class="list-group list-group-flush" id="listaInversiones"></div>
                                    <div class="card-footer" id="footerInversiones"></div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                    <div class="card bg-success text-white mb-4">
                                        <div class="card-header">Dividendos</div>
                                        <div class="card-body">
                                            <p class="card-text" id="dividendosDisponible">
                                            Disponible: $ 0,00
                                            </p>
                                        </div>
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action" id="dividendosAle">Ale : $ 0,00</a>
                                            <a href="#" class="list-group-item list-group-item-action" id="dividendosFlor">Flor : $ 0,00</a>
                                        </div>
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
