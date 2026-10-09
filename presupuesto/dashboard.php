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
        <title>Presupuesto</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <style>
            .row-striped:nth-of-type(odd){
            background-color: #efefef;
            border-left: 4px #000000 solid;
            }

            .row-striped:nth-of-type(even){
            background-color: #ffffff;
            border-left: 4px #efefef solid;
            }

            .row-striped {
                padding: 15px 0;
            }

            .badge-secondary {
                color: #fff;
                background-color: #6c757d;
            }
        </style>

        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

        <script>
            $(document).ready(function(e) {
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };

                var formatoMoneda = function(valor) {
                    return '$ ' + (parseFloat(valor) || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                };

                var setPorcentaje = function(idTexto, idBarra, valor, sufijo) {
                    var texto = (sufijo || '') + valor + '%';
                    $('#' + idTexto).text(texto);
                    $('#' + idBarra).css('width', valor + '%').attr('aria-valuenow', valor);
                };

                var renderTarjetasGasto = function(tarjetas) {
                    var $contenedor = $('#tarjetasGastoContainer');
                    $contenedor.empty();

                    $.each(tarjetas, function(index, tarjeta) {
                        $contenedor.append(
                            '<div class="col-xl-3 col-md-6 mb-4">' +
                                '<div class="card border-left-info shadow h-100 py-2">' +
                                    '<div class="card-body">' +
                                        '<div class="row no-gutters align-items-center">' +
                                            '<div class="col mr-2">' +
                                                '<div class="h5 mb-0 mr-3 font-weight-bold text-success text-uppercase mb-1">Objetivo ' + escapeHtml(tarjeta.nombre) + ' $ ' + escapeHtml(tarjeta.presupuesto) + '</div>' +
                                                '<div class="row no-gutters align-items-center">' +
                                                    '<div class="col-auto"><div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">Estimado ' + tarjeta.porcentajeIdeal + '%</div></div>' +
                                                    '<div class="col"><div class="progress progress-sm mr-2"><div class="progress-bar bg-warning" role="progressbar" style="width: ' + tarjeta.porcentajeIdeal + '%" aria-valuenow="' + tarjeta.porcentajeIdeal + '" aria-valuemin="0" aria-valuemax="100"></div></div></div>' +
                                                '</div>' +
                                                '<div class="row no-gutters align-items-center">' +
                                                    '<div class="col-auto"><div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">Real (' + tarjeta.diasPasados + ' d) - ' + tarjeta.porcentajeReal + '%</div></div>' +
                                                    '<div class="col"><div class="progress progress-sm mr-2"><div class="progress-bar bg-warning" role="progressbar" style="width: ' + tarjeta.porcentajeReal + '%" aria-valuenow="' + tarjeta.porcentajeReal + '" aria-valuemin="0" aria-valuemax="100"></div></div></div>' +
                                                '</div>' +
                                                '<div class="row no-gutters align-items-center">' +
                                                    '<div class="col-auto"><div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">Pagado ' + tarjeta.porcentajePagado + '%</div></div>' +
                                                    '<div class="col"><div class="progress progress-sm mr-2"><div class="progress-bar bg-success" role="progressbar" style="width: ' + tarjeta.porcentajePagado + '%" aria-valuenow="' + tarjeta.porcentajePagado + '" aria-valuemin="0" aria-valuemax="100"></div></div></div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>'
                        );
                    });
                };

                var renderGraficoGastos = function(estimados, reales) {
                    var barChartGastos = new CanvasJS.Chart('barChartGastosContainer', {
                        animationEnabled: true,
                        title: { text: 'Gastos, <?php echo date('F Y'); ?>' },
                        axisY: {
                            title: 'Estimado',
                            titleFontColor: '#4F81BC',
                            lineColor: '#4F81BC',
                            labelFontColor: '#4F81BC',
                            tickColor: '#4F81BC'
                        },
                        axisY2: {
                            title: 'Real',
                            titleFontColor: '#C0504E',
                            lineColor: '#C0504E',
                            labelFontColor: '#C0504E',
                            tickColor: '#C0504E'
                        },
                        toolTip: { shared: true },
                        legend: {
                            cursor: 'pointer',
                            itemclick: function(e) {
                                if (typeof(e.dataSeries.visible) === 'undefined' || e.dataSeries.visible) {
                                    e.dataSeries.visible = false;
                                } else {
                                    e.dataSeries.visible = true;
                                }
                                barChartGastos.render();
                            }
                        },
                        data: [
                            { type: 'column', name: 'Estimado', legendText: 'Estimado', showInLegend: true, dataPoints: estimados },
                            { type: 'column', name: 'Real', legendText: 'Real', showInLegend: true, dataPoints: reales }
                        ]
                    });
                    barChartGastos.render();
                };

                var renderGraficoStock = function(minimo, actual, maximo) {
                    var barChartStock = new CanvasJS.Chart('barChartStockContainer', {
                        animationEnabled: true,
                        title: { text: 'Stock, <?php echo date('F Y'); ?>' },
                        toolTip: { shared: true },
                        legend: {
                            cursor: 'pointer',
                            itemclick: function(e) {
                                if (typeof(e.dataSeries.visible) === 'undefined' || e.dataSeries.visible) {
                                    e.dataSeries.visible = false;
                                } else {
                                    e.dataSeries.visible = true;
                                }
                                barChartStock.render();
                            }
                        },
                        data: [
                            { type: 'column', name: 'Minimo', legendText: 'Minimo', showInLegend: true, dataPoints: minimo },
                            { type: 'column', name: 'Actual', legendText: 'Actual', showInLegend: true, dataPoints: actual },
                            { type: 'column', name: 'Maximo', legendText: 'Maximo', showInLegend: true, dataPoints: maximo }
                        ]
                    });
                    barChartStock.render();
                };

                $.getJSON('../bff/presupuesto/dashboard.php')
                    .done(function(response) {
                        var data = response.data || {};

                        renderTarjetasGasto(data.tarjetasGasto || []);

                        $('#saldoCuentasTexto').text('Disponible en Cuentas ' + formatoMoneda(data.saldoCuentas));
                        setPorcentaje('pctDisponibleGastos', 'barDisponibleGastos', data.porcentajeDisponibleGastos || 0, 'Para Gastos ');
                        setPorcentaje('pctDisponibleCompras', 'barDisponibleCompras', data.porcentajeDisponibleCompras || 0, 'Para Compras ');

                        setPorcentaje('pctIngresos', 'barIngresos', data.porcentajeIngresos || 0);
                        setPorcentaje('pctGastos', 'barGastos', data.porcentajeGastos || 0);
                        setPorcentaje('pctCoberturaGastos', 'barCoberturaGastos', data.porcentajeCoberturaGastos || 0);
                        setPorcentaje('pctCostos', 'barCostos', data.porcentajeCostos || 0);
                        setPorcentaje('pctCostosCompras', 'barCostosCompras', data.porcentajeCostosCompras || 0);
                        setPorcentaje('pctCoberturaCompras', 'barCoberturaCompras', data.porcentajeCoberturaCompras || 0);

                        renderGraficoGastos(data.gastosEstimados || [], data.gastosReales || []);
                        renderGraficoStock(data.stockMinimo || [], data.stockActual || [], data.stockMaximo || []);
                    })
                    .fail(function() {
                        $('#tarjetasGastoContainer').html('<div class="col-12"><p class="text-center text-danger">No se pudo cargar el presupuesto.</p></div>');
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

                    <!-- Begin Page Content -->
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Tablero</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Presupuesto</a></li>
                        <li class="breadcrumb-item active"><?php echo date('F Y'); ?></li>
                    </ol>

                    <div class="row" id="tarjetasGastoContainer">
                        <div class="col-12"><p class="text-center text-muted">Cargando...</p></div>
                    </div>

                    <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="h5 mb-0 mr-3 font-weight-bold text-success text-uppercase mb-1" id="saldoCuentasTexto">
                                                Disponible en Cuentas $ 0,00
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctDisponibleGastos">Para Gastos 0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-warning" id="barDisponibleGastos" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctDisponibleCompras">Para Compras 0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-warning" id="barDisponibleCompras" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ventas Ganancias
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctIngresos">0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-success" id="barIngresos" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-cart-shopping fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Gastos
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctGastos">0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-danger" id="barGastos" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cobertura Gastos Actuales
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctCoberturaGastos">0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" id="barCoberturaGastos" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">


                        <!-- Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ventas Costo
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctCostos">0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-success" id="barCostos" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-cart-shopping fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Compras -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Costo Compras
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctCostosCompras">0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-warning" id="barCostosCompras" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gastos Netas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cobertura Compras Actuales
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="pctCoberturaCompras">0%</div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" id="barCoberturaCompras" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="row">

                        <!-- Gastos -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Gastos</h6>
                                </div>

                                <div class="card-body">
                                    <div id="barChartGastosContainer" style="max-width: 800px; height: 600px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Stock</h6>
                                </div>

                                <div class="card-body">
                                    <div id="barChartStockContainer" style="max-width: 800px; height: 600px;"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </main>
                <?php include '../footer.php';?>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
