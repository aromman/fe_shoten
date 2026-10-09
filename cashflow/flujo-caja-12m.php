<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
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
        <title>Flujo Caja</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(e) {
                var formatoNumero = function(valor) {
                    return (parseFloat(valor) || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                };

                var abreviaturasMeses = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];

                var renderFila = function($tbody, etiqueta, campo, meses, claseTr) {
                    var celdas = $.map(meses, function(mes) {
                        return '<td align="right">' + formatoNumero(mes[campo]) + '</td>';
                    }).join('');

                    $tbody.append(
                        '<tr' + (claseTr ? ' class="' + claseTr + '"' : '') + '>' +
                            '<th scope="col">' + etiqueta + '</th>' +
                            celdas +
                        '</tr>'
                    );
                };

                var renderTabla = function(meses) {
                    var $tbody = $('#flujoCajaBody');
                    $tbody.empty();

                    renderFila($tbody, 'SALDO INICIAL', 'saldoInicial', meses, 'table-info');
                    $tbody.append('<tr><td colspan="13"></td></tr>');
                    renderFila($tbody, 'Ventas', 'ventas', meses, '');
                    renderFila($tbody, 'TOTAL INGRESOS', 'totalIngresos', meses, 'table-warning');
                    $tbody.append('<tr><td colspan="13"></td></tr>');
                    renderFila($tbody, 'Compras', 'compras', meses, '');
                    renderFila($tbody, 'Gastos', 'gastos', meses, '');
                    renderFila($tbody, 'TOTAL EGRESOS', 'totalEgresos', meses, 'table-warning');
                    $tbody.append('<tr><td colspan="13"></td></tr>');
                    renderFila($tbody, 'FLUJO DE CAJA ECONOMICO', 'flujoCajaEconomico', meses, 'table-info');
                    $tbody.append('<tr><td colspan="13"></td></tr>');
                    renderFila($tbody, 'Financiamiento Recibido', 'financiamientoRecibido', meses, '');
                    renderFila($tbody, 'Pago de Financiamiento', 'pagoFinanciamiento', meses, '');
                    $tbody.append('<tr><td colspan="13"></td></tr>');
                    renderFila($tbody, 'FLUJO DE CAJA FINANCIERO', 'flujoFinanciero', meses, 'table-info');
                };

                var renderGrafico = function(meses, maximoEje) {
                    var categorias = $.map(meses, function(mes, index) { return abreviaturasMeses[index]; });
                    var dataEconomico = $.map(meses, function(mes) { return mes.flujoCajaEconomico; });
                    var dataFinanciero = $.map(meses, function(mes) { return mes.flujoFinanciero; });
                    var eje = maximoEje > 0 ? maximoEje : 1;

                    $('#container-burndown').highcharts({
                        title: {
                            text: 'Flujo de caja mensual',
                            x: -10
                        },
                        scrollbar: {
                            barBackgroundColor: 'gray',
                            barBorderRadius: 7,
                            barBorderWidth: 0,
                            buttonBackgroundColor: 'gray',
                            buttonBorderWidth: 0,
                            buttonBorderRadius: 7,
                            trackBackgroundColor: 'none',
                            trackBorderWidth: 1,
                            trackBorderRadius: 8,
                            trackBorderColor: '#CCC'
                        },
                        colors: ['blue', 'red'],
                        plotOptions: {
                            line: { lineWidth: 3 },
                            tooltip: { hideDelay: 200 }
                        },
                        subtitle: {
                            text: 'Año Actual',
                            x: -10
                        },
                        xAxis: {
                            categories: categorias,
                            title: { text: 'Meses' }
                        },
                        yAxis: {
                            title: { text: 'Cantidades' },
                            type: 'linear',
                            max: eje,
                            min: 0,
                            tickInterval: eje / 3
                        },
                        tooltip: {
                            valueSuffix: ' unidades',
                            crosshairs: true,
                            shared: true
                        },
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom',
                            borderWidth: 0
                        },
                        series: [
                            {
                                name: 'Flujo Economico',
                                color: 'rgba(0,120,200,0.75)',
                                lineWidth: 2,
                                data: dataEconomico
                            },
                            {
                                name: 'Flujo Finaciero',
                                color: 'rgba(255,0,0,0.25)',
                                lineWidth: 2,
                                data: dataFinanciero
                            }
                        ]
                    });
                };

                $.getJSON('../bff/cashflow/flujo-caja-12m.php')
                    .done(function(response) {
                        var meses = response.data && response.data.meses ? response.data.meses : [];
                        var maximoEje = response.data ? response.data.maximoEje : 0;
                        renderTabla(meses);
                        renderGrafico(meses, maximoEje);
                    })
                    .fail(function() {
                        $('#flujoCajaBody').html('<tr><td colspan="13" class="text-center text-danger">No se pudo cargar el flujo de caja.</td></tr>');
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
                        <h1 class="mt-4">Flujo de Caja</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>

                        <div class="row">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Flujo de Caja
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped table-bordered" id="tb">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Concepto</th>
                                                <th class="text-center">ENE</th>
                                                <th class="text-center">FEB</th>
                                                <th class="text-center">MAR</th>
                                                <th class="text-center">ABR</th>
                                                <th class="text-center">MAY</th>
                                                <th class="text-center">JUN</th>
                                                <th class="text-center">JUL</th>
                                                <th class="text-center">AGO</th>
                                                <th class="text-center">SEP</th>
                                                <th class="text-center">OCT</th>
                                                <th class="text-center">NOV</th>
                                                <th class="text-center">DIC</th>
                                            </tr>
                                        </thead>
                                        <tbody id="flujoCajaBody">
                                            <tr><td colspan="13" class="text-center text-muted">Cargando...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Area Chart -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Evolucion de Flujo</h6>
                                </div>
                                <div class="card-body">
                                    <div id="container-burndown" style="max-width: 800px; height: 600px;"></div>
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
        <script src="https://code.highcharts.com/highcharts.js"></script>

    </body>
</html>
