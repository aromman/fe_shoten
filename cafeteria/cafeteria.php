<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/cuentasMovimientos.php";
require_once __DIR__ . '/../app/config/url.php';

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Cafeteria</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">

        <script>
            $(document).ready(function(e) {

                $('input').keyup(function() {

                    var cobrar = $("#cobrar01").val();
                    var pagado = $("#pagado02").val();

                    if (Number.parseFloat(pagado) > Number.parseFloat(cobrar)) {
                        $("#vuelto").val(Number.parseFloat(pagado - cobrar).toFixed(0));
                    } else {
                        $("#vuelto").val(0);
                    }
                });

                var endpoint = <?php echo json_encode(app_url('/bff/cafeteria/actual.php')); ?>;
                var money = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 2 });
                var escapeHtml = function(value) {
                    return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
                    });
                };

                fetch(endpoint, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function(response) { return response.json(); })
                    .then(function(body) {
                        var data = body.data;
                        document.getElementById('totalCafeteria').textContent = money.format(data.total || 0);

                        document.getElementById('tbMovimientosBody').innerHTML = data.movimientos.length
                            ? data.movimientos.map(function(m) {
                                return '<tr>'
                                    + '<td>' + escapeHtml(m.fecha) + '</td>'
                                    + '<td>' + escapeHtml(m.descripcion) + '</td>'
                                    + '<td>' + money.format(m.monto) + '</td>'
                                    + '</tr>';
                            }).join('')
                            : '<tr><td colspan="3" class="text-center text-muted py-4">No hay movimientos registrados.</td></tr>';
                    })
                    .catch(function() {
                        var alertEl = document.getElementById('turnoAlert');
                        alertEl.textContent = 'No se pudo cargar la información de caja.';
                        alertEl.classList.remove('d-none');
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
                        <h1 class="mt-4">Cafeteria</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Operaciones</li>
                            <li class="breadcrumb-item">Cafetería</li>
                            <li class="breadcrumb-item active">Cafetería</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Movimientos
                            </div>
                            <div class="card-body">
                                <form id="formMovimientos" method="post" onSubmit="return false;">

                                    <div id="turnoAlert" class="alert alert-warning d-none"></div>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="card bg-light border-primary mb-0 d-inline-block">
                                            <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                                                <span class="fw-semibold small">Total Cafetería</span>
                                                <strong id="totalCafeteria" class="text-primary">$ 0,00</strong>
                                            </div>
                                        </div>
                                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoModal"><i class="fa-solid fa-circle-plus"></i> Nuevo movimiento</a>
                                    </div>
                                    <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-responsive" id="tbMovimientos">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Detalle</th>
                                                <th class="text-center">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbMovimientosBody">
                                        </tbody>
                                    </table>
                                    </div>
                                </form>
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

        <?php include 'nuevoModal.php'; ?>

    </body>
</html>
