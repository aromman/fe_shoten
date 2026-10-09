<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "../bff/cashflow/flujo-caja-proy.php";

$flujoCajaViewModel = obtenerFlujoCajaProyectadoViewModel();
$periodos = $flujoCajaViewModel['periodos'];
$filasFlujo = $flujoCajaViewModel['filas'];
$supuestos = $flujoCajaViewModel['supuestos'];
$grafico = $flujoCajaViewModel['grafico'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function dinero($value) {
    return number_format((float) $value, 2, ".", "");
}

function importeFlujo($value) {
    $importe = dinero($value);
    if ((float) $value >= 0) {
        return $importe;
    }

    return '<span class="cashflow-monto-negativo">' . h($importe) . '</span>';
}

function columnaConceptoFlujo($concepto) {
    return 1;
}

function filaPorConcepto($filas, $concepto) {
    foreach ($filas as $fila) {
        if ($fila['concepto'] === $concepto) {
            return $fila;
        }
    }

    return null;
}

function agruparPeriodosFlujo($periodos) {
    $nombreMes = array(1=>"ENE",2=>"FEB",3=>"MAR",4=>"ABR",5=>"MAY",6=>"JUN",7=>"JUL",8=>"AGO",9=>"SEP",10=>"OCT",11=>"NOV",12=>"DIC");
    $grupos = array();

    foreach ($periodos as $periodo) {
        if (preg_match('/^(\d{4})-(\d{2})-S\d+$/', $periodo['clave'], $matches)) {
            $claveGrupo = $matches[1] . '-' . $matches[2];
            $labelGrupo = $nombreMes[(int) $matches[2]] . ' ' . $matches[1];

            if (!isset($grupos[$claveGrupo])) {
                $grupos[$claveGrupo] = array(
                    'label' => $labelGrupo,
                    'colspan' => 0,
                    'subcolumnas' => true,
                );
            }
            $grupos[$claveGrupo]['colspan']++;
            continue;
        }

        $grupos[$periodo['clave']] = array(
            'label' => $periodo['label'],
            'colspan' => 1,
            'subcolumnas' => false,
        );
    }

    return array_values($grupos);
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
        <title>Flujo Caja Proyectado </title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <style>
            .cashflow-monto-negativo {
                display: inline-block;
                background-color: #dc3545 !important;
                border: 2px solid #a71d2a !important;
                border-radius: .35rem;
                color: #fff !important;
                font-weight: 700;
                line-height: 1.2;
                min-width: 92px;
                padding: .25rem .5rem;
                text-align: right;
            }
            .cashflow-child th {
                padding-left: 2rem;
                font-weight: 500;
            }
            .cashflow-total-ventas {
                background-color: #d1e7dd !important;
                color: #0f5132;
                font-weight: 700;
            }
        </style>
    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Flujo de Caja Proyectado</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>

                        <div class="row">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Flujo de Caja Proyectado
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <div class="border rounded p-2 h-100">
                                                <div class="text-muted small">Saldo disponible inicial</div>
                                                <strong>$ <?php echo dinero($supuestos['saldoInicialDisponible']); ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="border rounded p-2 h-100">
                                                <div class="text-muted small">Venta mensual proyectada Manga</div>
                                                <strong>$ <?php echo dinero($supuestos['ventaPromedio']); ?></strong>
                                                <div class="text-muted small">
                                                    <?php echo h($supuestos['ventasMangaMesesConOperatoria']); ?> meses con operatoria
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="border rounded p-2 h-100">
                                                <div class="text-muted small">Compras sobre ventas</div>
                                                <strong><?php echo dinero($supuestos['porcentajeCompraSobreVenta']); ?>%</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <div class="border rounded p-2 h-100">
                                                <div class="text-muted small">Horizonte</div>
                                                <strong><?php echo h($supuestos['horizonteMeses']); ?> meses</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $gruposPeriodos = agruparPeriodosFlujo($periodos); ?>
                                    <table class="table table-striped table-bordered" id="tb">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle" rowspan="2">Concepto</th>
                                                <?php foreach ($gruposPeriodos as $grupoPeriodo) { ?>
                                                    <?php if ($grupoPeriodo['subcolumnas']) { ?>
                                                        <th class="text-center" colspan="<?php echo h($grupoPeriodo['colspan']); ?>"><?php echo h($grupoPeriodo['label']); ?></th>
                                                    <?php } else { ?>
                                                        <th class="text-center align-middle" rowspan="2"><?php echo h($grupoPeriodo['label']); ?></th>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tr>
                                            <tr>
                                                <?php foreach ($periodos as $periodo) { ?>
                                                    <?php if (preg_match('/-S\d+$/', $periodo['clave'])) { ?>
                                                        <th class="text-center"><?php echo h($periodo['label']); ?></th>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $ordenFlujo = array(
                                                    'SALDO INICIAL',
                                                    'Ventas Proyectadas Manga',
                                                    'TOTAL VENTAS PROYECTADAS',
                                                    'TOTAL INGRESOS',
                                                    'Compras proyectadas',
                                                    'Gastos presupuestados',
                                                    'Ordenes de compra pendientes',
                                                    'Consignaciones pendientes',
                                                    'TOTAL EGRESOS',
                                                    'SALDO FINAL PROYECTADO',
                                                );
                                            ?>
                                            <?php foreach ($ordenFlujo as $concepto) { ?>
                                                <?php $fila = filaPorConcepto($filasFlujo, $concepto); ?>
                                                <?php if ($fila === null) { continue; } ?>
                                                <tr class="<?php echo h($fila['clase']); ?>">
                                                    <th scope="col"><?php echo h($fila['concepto']); ?></th>
                                                    <?php foreach ($periodos as $periodo) { ?>
                                                        <?php $valor = isset($fila['valores'][$periodo['clave']]) ? $fila['valores'][$periodo['clave']] : 0; ?>
                                                        <td align="right"><?php echo importeFlujo($valor); ?></td>
                                                    <?php } ?>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Saldo final proyectado</h6>
                                </div>
                                <div class="card-body">
                                    <div id="container-cashflow-proyectado" style="height: 420px;"></div>
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

        <script>
            jQuery(document).ready(function() {
                $('#container-cashflow-proyectado').highcharts({
                    title: {
                        text: 'Evolucion del saldo proyectado'
                    },
                    xAxis: {
                        categories: <?php echo json_encode($grafico['categorias']); ?>,
                        title: {
                            text: 'Mes'
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Saldo'
                        },
                        plotLines: [{
                            value: 0,
                            color: '#dc3545',
                            width: 2,
                            zIndex: 4
                        }]
                    },
                    tooltip: {
                        valuePrefix: '$ ',
                        shared: true
                    },
                    legend: {
                        enabled: false
                    },
                    series: [{
                        name: 'Saldo final proyectado',
                        color: '#0d6efd',
                        negativeColor: '#dc3545',
                        data: <?php echo json_encode($grafico['saldoFinal']); ?>
                    }]
                });
            });
        </script>


    </body>
</html>
