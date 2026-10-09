<?php
session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/helpers/CronExpressionHelper.php";
require_once "../app/models/proveedores.php";
require_once "../app/models/compras.php";

$proveedorPDO = new Proveedor();

date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaHoy = date("Y-m-d");
$inicioCalendario = new DateTime($fechaHoy);

$proveedores = $proveedorPDO->getAllActiveParaCalendario();
$diasCalendario = array();

for ($i = 0; $i < 7; $i++) {
    $fechaEvaluada = clone $inicioCalendario;
    $fechaEvaluada->modify('+' . $i . ' day');
    $claveFecha = $fechaEvaluada->format('Y-m-d');
    $diasCalendario[$claveFecha] = array(
        'fecha' => $claveFecha,
        'pedidos' => array(),
        'entregas' => array(),
    );

    if (!is_null($proveedores)) {
        foreach ($proveedores as $row) {
            $cronPedidoProveedor = CronExpressionHelper::normalizarCron(isset($row['cron_pedido']) ? $row['cron_pedido'] : '');
            $cronEntregaProveedor = CronExpressionHelper::normalizarCron(isset($row['cron_entrega']) ? $row['cron_entrega'] : '');
            $evento = array(
                'id' => isset($row['id']) ? (int) $row['id'] : 0,
                'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
                'origen' => isset($row['origen']) ? $row['origen'] : 'local',
                'cantidadMinima' => isset($row['cantidad_minima']) ? (float) $row['cantidad_minima'] : 0,
                'montoMinimo' => isset($row['monto_minimo']) ? (float) $row['monto_minimo'] : 0,
            );

            if ($claveFecha !== $fechaHoy && $cronPedidoProveedor !== '' && CronExpressionHelper::fechaCoincide($cronPedidoProveedor, $fechaEvaluada)) {
                $diasCalendario[$claveFecha]['pedidos'][] = $evento;
            }

            if ($cronEntregaProveedor !== '' && CronExpressionHelper::fechaCoincide($cronEntregaProveedor, $fechaEvaluada)) {
                $diasCalendario[$claveFecha]['entregas'][] = $evento;
            }
        }
    }
}

$comprasPDO = new Compra();
$compras = $comprasPDO->getAllPendiente();
$diasSemana = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado");
$meses = array(1=>"Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Calendario Proveedores</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <style>
            .calendario-grid {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 12px;
            }

            .calendario-dia {
                border: 1px solid #dee2e6;
                min-height: 220px;
                background: #fff;
                padding: 12px;
            }

            .calendario-dia-hoy {
                border-color: #dc3545;
                box-shadow: inset 0 0 0 2px rgba(220, 53, 69, .15);
            }

            .calendario-dia-header {
                border-bottom: 1px solid #e9ecef;
                margin-bottom: 10px;
                padding-bottom: 8px;
            }

            .calendario-dia-numero {
                font-size: 1.8rem;
                line-height: 1;
                font-weight: 700;
            }

            .calendario-evento {
                border-left: 4px solid #6c757d;
                margin-bottom: 8px;
                padding: 8px;
                background: #f8f9fa;
            }

            .calendario-evento-local {
                border-left-color: #4a90d9;
                background: #f7fbff;
            }

            .calendario-evento-fudo {
                border-left-color: #d89b1d;
                background: #fffaf0;
            }

            .badge-origen-local {
                color: #0f4c81;
                background-color: #dceeff;
                border: 1px solid #9cc7ed;
            }

            .badge-origen-fudo {
                color: #7a4b00;
                background-color: #ffe7b3;
                border: 1px solid #f0bd58;
            }

            .badge-pedido {
                color: #fff;
                background-color: #0d6efd;
            }

            .badge-entrega {
                color: #fff;
                background-color: #198754;
            }

            .row-striped {
                padding: 15px 0;
                border-left: 4px #efefef solid;
            }

            .row-striped:nth-of-type(odd) {
                background-color: #efefef;
                border-left-color: #000000;
            }

            .row-striped:nth-of-type(even) {
                background-color: #ffffff;
            }

            .badge-secondary {
                color: #fff;
                background-color: #6c757d;
            }

            @media (max-width: 991.98px) {
                .calendario-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 575.98px) {
                .calendario-grid {
                    grid-template-columns: 1fr;
                }
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
                        <h1 class="mt-4">Calendario Proveedores</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="../index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Proximos 7 dias</li>
                        </ol>

                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <i class="fas fa-calendar-days me-1"></i>Calendario de pedidos y entregas
                            </div>
                            <div class="card-body">
                                <div class="calendario-grid">
                                    <?php foreach ($diasCalendario as $dia) {
                                        $fechaDia = $dia['fecha'];
                                        $esHoy = $fechaDia === $fechaHoy;
                                        $diaSemana = $diasSemana[date('w', strtotime($fechaDia))];
                                        $nroDia = date('d', strtotime($fechaDia));
                                        $mesDia = $meses[(int) date('m', strtotime($fechaDia))];
                                    ?>
                                        <div class="calendario-dia <?php echo $esHoy ? 'calendario-dia-hoy' : ''; ?>">
                                            <div class="calendario-dia-header d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="calendario-dia-numero"><?php echo $nroDia; ?></div>
                                                    <div><?php echo $mesDia; ?></div>
                                                </div>
                                                <div class="text-right">
                                                    <strong><?php echo $diaSemana; ?></strong>
                                                    <?php if ($esHoy) { ?>
                                                        <div><span class="badge badge-danger">Hoy</span></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <?php foreach ($dia['pedidos'] as $evento) { ?>
                                                <div class="calendario-evento calendario-evento-<?php echo htmlspecialchars($evento['origen']); ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="badge badge-pedido">Pedido</span>
                                                        <span class="badge badge-origen-<?php echo htmlspecialchars($evento['origen']); ?>"><?php echo htmlspecialchars($evento['origen']); ?></span>
                                                    </div>
                                                    <strong>
                                                        <a href="view-proveedor.php?forwardOk=calendario.php&id=<?php echo (int) $evento['id']; ?>">
                                                            <?php echo htmlspecialchars($evento['nombre']); ?>
                                                        </a>
                                                    </strong>
                                                    <div class="small text-muted">
                                                        Min: <?php echo number_format($evento['cantidadMinima'], 0, ',', '.'); ?> u. -
                                                        $ <?php echo number_format($evento['montoMinimo'], 2, ',', '.'); ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php foreach ($dia['entregas'] as $evento) { ?>
                                                <div class="calendario-evento calendario-evento-<?php echo htmlspecialchars($evento['origen']); ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="badge badge-entrega">Entrega</span>
                                                        <span class="badge badge-origen-<?php echo htmlspecialchars($evento['origen']); ?>"><?php echo htmlspecialchars($evento['origen']); ?></span>
                                                    </div>
                                                    <strong>
                                                        <a href="view-proveedor.php?forwardOk=calendario.php&id=<?php echo (int) $evento['id']; ?>">
                                                            <?php echo htmlspecialchars($evento['nombre']); ?>
                                                        </a>
                                                    </strong>
                                                    <div class="small text-muted">
                                                        Min: <?php echo number_format($evento['cantidadMinima'], 0, ',', '.'); ?> u. -
                                                        $ <?php echo number_format($evento['montoMinimo'], 2, ',', '.'); ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if (empty($dia['pedidos']) && empty($dia['entregas'])) { ?>
                                                <div class="text-muted small">Sin eventos</div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>Compras Pendientes
                            </div>
                            <div class="card-body">
                                <?php
                                    if (!is_null($compras)){
                                        foreach ($compras as $compra) {
                                            $fechaEvento = $compra['fecha'];
                                            $nroDiaEvento = date('d', strtotime($fechaEvento));
                                            $mesDiaEvento = $meses[(int)(date('m', strtotime($fechaEvento)))];
                                            $tituloEvento = $compra['proveedor'];
                                            $fechaEntrega = $compra['fecha_entrega_calculada'];
                                            if (empty($fechaEntrega)) {
                                                continue;
                                            }

                                            $vence = date('d/m/Y', strtotime($fechaEntrega));
                                            $fecha1 = new DateTime($fechaHoy);
                                            $fecha2 = new DateTime($fechaEntrega);
                                            $diff = $fecha1->diff($fecha2);
                                            $leyenda = ($diff->invert === 1 ? "Reclamar vencida" : "");
                                ?>
                                            <div class="row row-striped">
                                                <div class="col-2 text-right">
                                                    <h1 class="display-4"><span class="badge badge-secondary"><?php echo $nroDiaEvento; ?></span></h1>
                                                    <h2><?php echo $mesDiaEvento;?></h2>
                                                </div>
                                                <div class="col-10">
                                                    <h3 class="text-uppercase"><strong><?php echo htmlspecialchars($tituloEvento); ?></strong></h3>
                                                    <ul class="list-inline">
                                                        <li class="list-inline-item"><i class="fa fa-calendar" aria-hidden="true"></i> Fecha posible de entrega: <?php echo $vence;?></li>
                                                    </ul>
                                                    <p><?php echo $leyenda; ?></p>
                                                </div>
                                            </div>
                                <?php
                                        }
                                    }
                                ?>
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
