<?php
session_start();

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../login.php');
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
        <meta name="description" content="Rentabilidad" />
        <title>Rentabilidad</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">

        <?php include __DIR__ . '/../topBar.php'; ?>

        <div id="layoutSidenav">

            <?php include __DIR__ . '/../sidebar.php'; ?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Rentabilidad <small id="rentabilidadAnio" class="text-muted"></small></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>

                        <div id="rentabilidadAlert" class="alert alert-danger d-none" role="alert"></div>

                        <div class="row">
                            <div class="container py-4">
                                <div class="row row-cols-1 row-cols-md-12 g-4" id="rentabilidadKpis">
                                    <div class="col-12 text-muted">Cargando tablero...</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Estados Financieros
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
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
                                                    <th class="text-center">TOTALES</th>
                                                </tr>
                                            </thead>
                                            <tbody id="rentabilidadTablaBody">
                                                <tr><td colspan="14" class="text-muted">Cargando tablero...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Row -->
                        <div class="row">
                            <!-- Productividad -->
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                        <div id="container-productividad" style="max-width: 800px; height: 600px;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gastos sobre ventas -->
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                        <div id="container-gastos" style="max-width: 800px; height: 600px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Proyecciones -->
                        <div class="row">

                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>
                                        Proyeccion segun Ventas Actuales

                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center"></th>
                                                    <th class="text-center">Meses</th>
                                                    <th class="text-center">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">Objetivo</td>
                                                    <td class="text-center" id="proyVentasMeses"></td>
                                                    <td class="text-center" id="proyVentasMontoObjetivo"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="card-body">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Concepto</th>
                                                    <th class="text-center">Monto</th>
                                                    <th class="text-center">Porcentaje</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">Ventas</td>
                                                    <td class="text-center" id="proyVentasVentas"></td>
                                                    <td class="text-center">100%</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">%Costos</td>
                                                    <td class="text-center" id="proyVentasCosto"></td>
                                                    <td class="text-center">45%</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">Ganancia Bruta</td>
                                                    <td class="text-center" id="proyVentasGananciaBruta"></td>
                                                    <td class="text-center">55%</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">Gastos Fijos</td>
                                                    <td class="text-center" id="proyVentasGastosFijos"></td>
                                                    <td class="text-center"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">Ganancia Neta</td>
                                                    <td class="text-center" id="proyVentasGananciaNeta"></td>
                                                    <td class="text-center"></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="text-center">Objetivo Ganancias</td>
                                                    <td class="text-center" id="proyVentasObjetivoGanancias"></td>
                                                    <td class="text-center" id="proyVentasCumplido"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>
                                        Proyeccion segun meta de Ganancias

                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center"></th>
                                                    <th class="text-center">Meses</th>
                                                    <th class="text-center">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">Objetivo</td>
                                                    <td class="text-center" id="proyGananciaMeses"></td>
                                                    <td class="text-center" id="proyGananciaMontoObjetivo"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="card-body">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Concepto</th>
                                                    <th class="text-center">Monto</th>
                                                    <th class="text-center">Porcentaje</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">Ventas</td>
                                                    <td class="text-center" id="proyGananciaVentas"></td>
                                                    <td class="text-center">100%</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">%Costos</td>
                                                    <td class="text-center" id="proyGananciaCosto"></td>
                                                    <td class="text-center">45%</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">Ganancia Bruta</td>
                                                    <td class="text-center" id="proyGananciaGananciaBruta"></td>
                                                    <td class="text-center">55%</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">Gastos Fijos</td>
                                                    <td class="text-center" id="proyGananciaGastosFijos"></td>
                                                    <td class="text-center"></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">Ganancia Neta</td>
                                                    <td class="text-center" id="proyGananciaGananciaNeta"></td>
                                                    <td class="text-center"></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="text-center">Objetivo Ventas</td>
                                                    <td class="text-center" id="proyGananciaObjetivoVentas"></td>
                                                    <td class="text-center" id="proyGananciaCumplido"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </main>

                <?php include __DIR__ . '/../footer.php'; ?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>

        <script>
            $(document).ready(function() {
                var endpoint = <?php echo json_encode(app_url('/bff/rentabilidad/rentabilidad.php')); ?>;
                var meseArray = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                var chartProductividad = null;
                var chartGastos = null;
                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };
                var formatoMoneda = new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                var formatoPorcentaje = new Intl.NumberFormat('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                var fm = function(valor) {
                    return '$ ' + formatoMoneda.format(valor || 0);
                };
                var fp = function(valor) {
                    return formatoPorcentaje.format(valor || 0) + ' %';
                };

                var renderKpis = function(totales) {
                    var tarjetas = [
                        {
                            titulo: 'Ventas Brutas',
                            valor: fm(totales.ventasMes),
                            icono: 'fa-chart-line',
                            color: 'text-success'
                        },
                        {
                            titulo: 'Costo de mercaderia vendida',
                            valor: fm(totales.costoVentasMes) + ' <small style="font-size: small;">' + fp(totales.porcentajeCostoVentas) + '</small>',
                            icono: 'fa-sack-dollar',
                            color: 'text-primary'
                        },
                        {
                            titulo: 'Ganancia Bruta',
                            valor: fm(totales.gananciaBrutaMes) + ' <small style="font-size: small;">' + fp(totales.porcentajeGananciaBruta) + '</small>',
                            icono: 'fa-arrow-up',
                            color: 'text-warning'
                        },
                        {
                            titulo: 'Ganancia Neta',
                            valor: fm(totales.gananciaNetaMes) + ' <small style="font-size: small;">' + fp(totales.porcentajeGananciaNeta) + '</small>',
                            icono: 'fa-cash-register',
                            color: 'text-secondary'
                        }
                    ];

                    var html = $.map(tarjetas, function(tarjeta) {
                        return '' +
                            '<div class="col-lg-3 mb-4">' +
                                '<div class="card h-100 text-center shadow">' +
                                    '<div class="card-body">' +
                                        '<p class="card-text text-muted">' + escapeHtml(tarjeta.titulo) + '</p>' +
                                        '<div class="display-4 ' + tarjeta.color + ' mb-2">' +
                                            '<i class="fa-solid ' + tarjeta.icono + '"></i>' +
                                        '</div>' +
                                        '<h2 class="card-title mb-3">' + tarjeta.valor + '</h2>' +
                                    '</div>' +
                                '</div>' +
                            '</div>';
                    }).join('');

                    $('#rentabilidadKpis').html(html);
                };

                var filaConcepto = function(concepto, meses, total, opciones) {
                    opciones = opciones || {};
                    var claseFila = opciones.claseFila ? ' class="' + opciones.claseFila + '"' : '';
                    var tagConcepto = opciones.destacado ? 'th' : 'td';
                    var celdas = $.map(meses, function(valor) {
                        var claseNegativo = (opciones.marcarNegativo && valor < 0) ? ' class="table-danger"' : '';
                        return '<td' + claseNegativo + ' align="right">' + escapeHtml(opciones.formatoPorcentaje ? fp(valor) : fm(valor)) + '</td>';
                    }).join('');

                    return '' +
                        '<tr' + claseFila + '>' +
                            '<' + tagConcepto + (opciones.destacado ? ' scope="col"' : '') + '>' + escapeHtml(concepto) + '</' + tagConcepto + '>' +
                            celdas +
                            '<td align="right">' + escapeHtml(opciones.formatoPorcentaje ? fp(total) : fm(total)) + '</td>' +
                        '</tr>';
                };

                var mesesDeSerie = function(meses, campo) {
                    return $.map(meses, function(mes) {
                        return mes[campo];
                    });
                };

                var renderTabla = function(data) {
                    var meses = data.meses;
                    var totalesAnio = data.totalesAnio;
                    var filas = '';

                    $.each(data.ventasPorCanal, function(index, fila) {
                        filas += filaConcepto(fila.canal, fila.meses, fila.total);
                    });
                    filas += filaConcepto('TOTAL VENTAS', mesesDeSerie(meses, 'ventas'), totalesAnio.ventas, { claseFila: 'table-warning', destacado: true });

                    filas += filaConcepto('Costo Estimado Ventas (50%)', mesesDeSerie(meses, 'costoEstimadoVentas'), totalesAnio.costoEstimadoVentas);
                    filas += filaConcepto('Ajustes Stock', mesesDeSerie(meses, 'ajustesStock'), totalesAnio.ajustesStock);
                    filas += filaConcepto('Descuentos', mesesDeSerie(meses, 'descuentos'), totalesAnio.descuentos);
                    filas += filaConcepto('TOTAL COSTO DE VENTAS', mesesDeSerie(meses, 'costoVentas'), totalesAnio.costoVentas, { claseFila: 'table-warning', destacado: true });

                    filas += '<tr><td colspan="14"></td></tr>';
                    filas += filaConcepto('MARGEN BRUTO', mesesDeSerie(meses, 'margenBruto'), totalesAnio.margenBruto, { claseFila: 'table-info', destacado: true });
                    filas += '<tr><td colspan="14"></td></tr>';

                    $.each(data.gastosFijosPorClase, function(index, fila) {
                        filas += filaConcepto(fila.clase, fila.meses, fila.total);
                    });
                    filas += filaConcepto('TOTAL GASTOS GENERALES', mesesDeSerie(meses, 'gastosFijos'), totalesAnio.gastosFijos, { claseFila: 'table-warning', destacado: true });

                    filas += '<tr><td colspan="14"></td></tr>';
                    filas += filaConcepto('UTILIDAD', mesesDeSerie(meses, 'utilidad'), totalesAnio.utilidad, { claseFila: 'table-success', destacado: true, marcarNegativo: true });
                    filas += '<tr><td colspan="14"></td></tr>';
                    filas += filaConcepto('PRODUCTIVIDAD', mesesDeSerie(meses, 'productividad'), totalesAnio.productividad, { claseFila: 'table-success', destacado: true, marcarNegativo: true, formatoPorcentaje: true });
                    filas += '<tr><td colspan="14"></td></tr>';
                    filas += filaConcepto('GASTOS ENTRE VENTAS', mesesDeSerie(meses, 'gastosSobreVentas'), totalesAnio.gastosSobreVentas, { claseFila: 'table-success', destacado: true, marcarNegativo: true, formatoPorcentaje: true });

                    $('#rentabilidadTablaBody').html(filas);
                };

                var renderChartProductividad = function(data) {
                    var mesActual = data.mesActual;
                    var meses = data.meses.slice(0, mesActual);
                    var categorias = meseArray.slice(0, mesActual);
                    var real = $.map(meses, function(mes) { return mes.productividad; });
                    var ideal = $.map(meses, function() { return 30; });
                    var valores = real.concat(ideal);
                    var maximo = Math.max.apply(null, valores.concat([0]));
                    var minimo = Math.min.apply(null, valores.concat([0]));

                    if (chartProductividad) {
                        chartProductividad.destroy();
                    }

                    chartProductividad = Highcharts.chart('container-productividad', {
                        title: {
                            text: 'Productividad',
                            x: -10
                        },
                        colors: ['blue', 'red'],
                        plotOptions: {
                            line: {
                                lineWidth: 3
                            },
                            tooltip: {
                                hideDelay: 200
                            }
                        },
                        subtitle: {
                            text: 'Ganancias',
                            x: -10
                        },
                        xAxis: {
                            categories: categorias,
                            title: {
                                text: 'Meses'
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Porcentajes'
                            },
                            type: 'linear',
                            max: maximo,
                            min: minimo,
                            tickInterval: 25
                        },
                        tooltip: {
                            valueSuffix: ' %',
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
                                name: 'Ideal',
                                color: 'rgba(255,0,0,0.25)',
                                lineWidth: 2,
                                data: ideal
                            },
                            {
                                name: 'Real',
                                color: 'rgba(0,120,200,0.75)',
                                lineWidth: 2,
                                data: real
                            }
                        ]
                    });
                };

                var renderChartGastos = function(data) {
                    var mesActual = data.mesActual;
                    var meses = data.meses.slice(0, mesActual);
                    var categorias = meseArray.slice(0, mesActual);
                    var real = $.map(meses, function(mes) { return mes.gastosSobreVentas; });
                    var ideal = $.map(meses, function() { return 0; });
                    var valores = real.concat(ideal);
                    var maximo = Math.max.apply(null, valores.concat([0]));
                    var minimo = Math.min.apply(null, valores.concat([0]));

                    if (chartGastos) {
                        chartGastos.destroy();
                    }

                    chartGastos = Highcharts.chart('container-gastos', {
                        title: {
                            text: 'Gastos sobre Ventas',
                            x: -10
                        },
                        colors: ['blue', 'red'],
                        plotOptions: {
                            line: {
                                lineWidth: 3
                            },
                            tooltip: {
                                hideDelay: 200
                            }
                        },
                        subtitle: {
                            text: 'Punto de Quiebre',
                            x: -10
                        },
                        xAxis: {
                            categories: categorias,
                            title: {
                                text: 'Meses'
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Porcentajes'
                            },
                            type: 'linear',
                            max: maximo,
                            min: minimo,
                            tickInterval: 25
                        },
                        tooltip: {
                            valueSuffix: ' %',
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
                                name: 'Ideal',
                                color: 'rgba(255,0,0,0.25)',
                                lineWidth: 2,
                                data: ideal
                            },
                            {
                                name: 'Real',
                                color: 'rgba(0,120,200,0.75)',
                                lineWidth: 2,
                                data: real
                            }
                        ]
                    });
                };

                var renderProyecciones = function(data) {
                    var pv = data.proyeccionSegunVentas;
                    $('#proyVentasMeses').text(pv.mesesObjetivo);
                    $('#proyVentasMontoObjetivo').text(fm(pv.montoObjetivo));
                    $('#proyVentasVentas').text(fm(pv.ventas));
                    $('#proyVentasCosto').text(fm(pv.costo));
                    $('#proyVentasGananciaBruta').text(fm(pv.gananciaBruta));
                    $('#proyVentasGastosFijos').text(fm(pv.gastosFijos));
                    $('#proyVentasGananciaNeta').text(fm(pv.gananciaNeta));
                    $('#proyVentasObjetivoGanancias').text(fm(pv.objetivoGanancias));
                    $('#proyVentasCumplido').text(pv.cumplido ? 'CUMPLIDO' : 'NO CUMPLIDO');

                    var pg = data.proyeccionSegunGanancia;
                    $('#proyGananciaMeses').text(pg.mesesObjetivo);
                    $('#proyGananciaMontoObjetivo').text(fm(pg.montoObjetivo));
                    $('#proyGananciaVentas').text(fm(pg.ventas));
                    $('#proyGananciaCosto').text(fm(pg.costo));
                    $('#proyGananciaGananciaBruta').text(fm(pg.gananciaBruta));
                    $('#proyGananciaGastosFijos').text(fm(pg.gastosFijos));
                    $('#proyGananciaGananciaNeta').text(fm(pg.gananciaNeta));
                    $('#proyGananciaObjetivoVentas').text(fm(pg.objetivoVentas));
                    $('#proyGananciaCumplido').text(pg.cumplido ? 'CUMPLIDO' : 'NO CUMPLIDO');
                };

                var cargarTablero = function() {
                    $.getJSON(endpoint)
                        .done(function(response) {
                            var data = response.data;
                            $('#rentabilidadAnio').text('(' + data.anio + ')');
                            $('#rentabilidadAlert').addClass('d-none').text('');

                            renderKpis(data.totales);
                            renderTabla(data);
                            renderChartProductividad(data);
                            renderChartGastos(data);
                            renderProyecciones(data);
                        })
                        .fail(function() {
                            $('#rentabilidadAlert')
                                .removeClass('d-none')
                                .text('No se pudo cargar el tablero de rentabilidad.');
                            $('#rentabilidadKpis').html('');
                            $('#rentabilidadTablaBody').html('');
                        });
                };

                cargarTablero();
            });
        </script>

    </body>
</html>
