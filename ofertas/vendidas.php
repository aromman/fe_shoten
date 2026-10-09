<?php
session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if (!isset($_SESSION['user.rol']) || (int) $_SESSION['user.rol'] !== 0) {
    header("location: ../bienvenido.php");
    exit;
}

require_once "../bff/ofertas/vendidas-view-model.php";

$nombreBuscado = isset($_GET['nombre']) ? trim((string) $_GET['nombre']) : '';
$ofertasViewModel = obtenerOfertasVendidasViewModel($nombreBuscado, 3);
$ofertasProductos = $ofertasViewModel['productos'];
$graficoMeses = $ofertasViewModel['graficos']['meses'];
$graficoSemanas = $ofertasViewModel['graficos']['semanas'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Ofertas Vendidas</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
        <style>
            .ofertas-sold-search {
                max-width: 520px;
            }
            .ofertas-chart-panel {
                border: 1px solid rgba(0, 0, 0, .08);
                padding: 1rem;
                min-height: 300px;
                background: #fff;
            }
            .ofertas-chart-title {
                color: #495057;
                font-size: .9rem;
                font-weight: 700;
                margin-bottom: .75rem;
            }
            .ofertas-chart-canvas {
                height: 240px;
                position: relative;
            }
        </style>
        <script>
            $(document).ready(function() {
                $('#tbOfertasVendidas').dataTable({
                    "ordering": false,
                    "paging": false,
                    "info": false,
                    "dom": "rt",
                    "language": {
                        "emptyTable": "No hay productos con oferta vendidos en los ultimos 3 meses"
                    }
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
                        <h1 class="mt-4">Ofertas Vendidas</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Ultimos 3 meses</li>
                        </ol>

                        <div class="row mb-4">
                            <div class="col-lg-6 mb-3 mb-lg-0">
                                <div class="ofertas-chart-panel">
                                    <div class="ofertas-chart-title">Total vendido por mes</div>
                                    <div class="ofertas-chart-canvas">
                                        <canvas id="chartOfertasMeses"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="ofertas-chart-panel">
                                    <div class="ofertas-chart-title">Ventas por semana</div>
                                    <div class="ofertas-chart-canvas">
                                        <canvas id="chartOfertasSemanas"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-tags me-1"></i>
                                Productos con precio de oferta vendidos en los ultimos 3 meses
                            </div>
                            <div class="card-body">
                                <form method="get" class="ofertas-sold-search mb-3">
                                    <div class="input-group">
                                        <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre" value="<?php echo h($nombreBuscado); ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </button>
                                        <a class="btn btn-secondary" href="vendidas.php">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </a>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tbOfertasVendidas">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Ultima Venta</th>
                                                <th>Producto</th>
                                                <th class="text-center">Unid. vendidas</th>
                                                <th class="text-center">Precio Costo</th>
                                                <th class="text-center">Precio Venta</th>
                                                <th class="text-center">Precio Oferta</th>
                                                <th class="text-center">% Lista vs Oferta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ofertasProductos as $row) { ?>
                                                <tr class="<?php echo $row['precioOfertaInconsistente'] ? 'table-danger' : ''; ?>">
                                                    <td><?php echo h($row['ultimaVenta']); ?></td>
                                                    <td><?php echo h($row['titulo']); ?></td>
                                                    <td align="right"><?php echo h($row['unidadesVendidas']); ?></td>
                                                    <td align="right"><?php echo h($row['precioCosto']); ?></td>
                                                    <td align="right"><?php echo h($row['precioLista']); ?></td>
                                                    <td align="right"><?php echo h($row['precioOferta']); ?></td>
                                                    <td align="right"><?php echo h($row['porcentajeDiferencia']); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
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
        <script>
            var ofertasMeses = <?php echo json_encode($graficoMeses, JSON_NUMERIC_CHECK); ?>;
            var ofertasSemanas = <?php echo json_encode($graficoSemanas, JSON_NUMERIC_CHECK); ?>;

            new Chart(document.getElementById('chartOfertasMeses'), {
                type: 'pie',
                data: {
                    labels: ofertasMeses.labels,
                    datasets: [{
                        data: ofertasMeses.data,
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom'
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.labels[tooltipItem.index] || '';
                                var value = data.datasets[0].data[tooltipItem.index] || 0;
                                return label + ': $ ' + Number(value).toFixed(2);
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('chartOfertasSemanas'), {
                type: 'line',
                data: {
                    labels: ofertasSemanas.labels,
                    datasets: [{
                        label: 'Total vendido',
                        data: ofertasSemanas.data,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, .12)',
                        pointBackgroundColor: '#0d6efd',
                        pointRadius: 3,
                        lineTension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '$ ' + Number(tooltipItem.yLabel).toFixed(2);
                            }
                        }
                    }
                }
            });
        </script>
    </body>
</html>
