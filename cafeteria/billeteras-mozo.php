<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once __DIR__ . '/../app/config/url.php';

$denominaciones = array(10, 20, 50, 100, 200, 500, 1000, 2000, 10000, 20000);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Billeteras Mozo</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(e) {

                var endpoint = <?php echo json_encode(app_url('/bff/cafeteria/billeteras-mozo.php')); ?>;
                var endpointAbrir = <?php echo json_encode(app_url('/bff/cafeteria/billetera-abrir.php')); ?>;
                var endpointCerrar = <?php echo json_encode(app_url('/bff/cafeteria/billetera-cerrar.php')); ?>;
                var endpointCompensar = <?php echo json_encode(app_url('/bff/cafeteria/billetera-compensar.php')); ?>;
                var money = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 2 });
                var escapeHtml = function(value) {
                    return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
                    });
                };
                var formatearFecha = function(value) {
                    var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(String(value || ''));
                    if (!m) {
                        return escapeHtml(value);
                    }
                    return m[3] + '/' + m[2] + '/' + m[1] + ' ' + m[4] + ':' + m[5];
                };

                var renderAcciones = function(item) {
                    if (item.turnoAbierto) {
                        return '<button type="button" class="btn btn-sm btn-light js-cerrar-billetera" data-id-cuenta="' + item.cuenta.id + '" data-monto-apertura="' + item.turnoAbierto.montoApertura + '" data-nombre="' + escapeHtml(item.empleado.nombre) + '">'
                            + '<i class="fa-solid fa-lock me-1"></i>Cerrar</button>';
                    }

                    var botones = '<button type="button" class="btn btn-sm btn-primary js-abrir-billetera" data-id-cuenta="' + item.cuenta.id + '">'
                        + '<i class="fa-solid fa-lock-open me-1"></i>Abrir</button>';

                    if (item.saldo < -0.01) {
                        botones += ' <button type="button" class="btn btn-sm btn-warning js-compensar-billetera" data-id-cuenta="' + item.cuenta.id + '" data-nombre="' + escapeHtml(item.empleado.nombre) + '" data-saldo="' + item.saldo + '">'
                            + '<i class="fa-solid fa-scale-balanced me-1"></i>Compensar</button>';
                    }

                    return botones;
                };

                var renderCard = function(item) {
                    var sinCuenta = !item.cuenta;
                    var abierta = !sinCuenta && !!item.turnoAbierto;
                    var claseHeader = sinCuenta ? 'bg-secondary' : (abierta ? 'bg-success' : 'bg-secondary');

                    var cuerpo = '';
                    if (sinCuenta) {
                        cuerpo = '<span class="badge bg-secondary">Sin cuenta asignada</span>';
                    } else {
                        if (abierta) {
                            cuerpo += '<p class="mb-1">Apertura: <strong>' + money.format(item.turnoAbierto.montoApertura) + '</strong></p>';
                        }
                        if (item.saldo < -0.01) {
                            cuerpo += '<p class="mb-1 text-danger">Saldo: <strong>' + money.format(item.saldo) + '</strong></p>';
                        }
                    }

                    return ''
                        + '<div class="col">'
                        + '<div class="card h-100 shadow-sm">'
                        + '<div class="card-header ' + claseHeader + ' text-white text-center">'
                        + '<h3 class="my-0">' + escapeHtml(item.empleado.nombre) + '</h3>'
                        + '<small>' + (sinCuenta ? 'Sin billetera' : (abierta ? 'Billetera abierta' : 'Billetera cerrada')) + '</small>'
                        + (abierta ? '<div class="small">' + formatearFecha(item.turnoAbierto.fechaApertura) + '</div>' : '')
                        + '</div>'
                        + '<div class="card-body text-center">'
                        + cuerpo
                        + (sinCuenta ? '' : '<div class="d-flex justify-content-center gap-2 mt-3">' + renderAcciones(item) + '</div>')
                        + '</div>'
                        + '</div>'
                        + '</div>';
                };

                var renderMozos = function(mozos) {
                    if (!mozos.length) {
                        return '<div class="col-12 text-center text-muted py-4">No hay mozos cargados en el equipo "Mozos".</div>';
                    }

                    return mozos.map(renderCard).join('');
                };

                var cargar = function() {
                    return fetch(endpoint, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                        .then(function(response) { return response.json(); })
                        .then(function(body) {
                            var mozos = body.data && body.data.mozos ? body.data.mozos : [];

                            if (body.meta && body.meta.aviso === 'EQUIPO_MOZOS_NO_ENCONTRADO') {
                                var alertEl = document.getElementById('turnoAlert');
                                alertEl.textContent = 'No existe un equipo llamado "Mozos" en Maestros > Equipos.';
                                alertEl.classList.remove('d-none');
                            }

                            document.getElementById('mozosContenedor').innerHTML = renderMozos(mozos);
                        })
                        .catch(function() {
                            var alertEl = document.getElementById('turnoAlert');
                            alertEl.textContent = 'No se pudo cargar la información de billeteras.';
                            alertEl.classList.remove('d-none');
                        });
                };

                cargar();

                // ---- Modal de Abrir Billetera ----

                var abrirMozo = null;

                var construirFilasAbrir = function() {
                    var denominaciones = <?php echo json_encode($denominaciones); ?>;
                    var $tbody = $('#abrirBilleteraBilletesBody');
                    $tbody.empty();
                    denominaciones.forEach(function(den) {
                        $tbody.append(
                            '<tr>'
                            + '<td class="fw-semibold">' + money.format(den) + '</td>'
                            + '<td><input type="number" min="0" step="1" class="form-control abrir-cantidad-billetes" data-denominacion="' + den + '" value="0"></td>'
                            + '<td class="text-end abrir-subtotal-billetes" data-denominacion="' + den + '">' + money.format(0) + '</td>'
                            + '</tr>'
                        );
                    });
                };

                var recalcularAbrir = function() {
                    var total = 0;
                    $('.abrir-cantidad-billetes').each(function() {
                        var den = Number($(this).data('denominacion'));
                        var cantidad = Number.parseInt($(this).val() || '0', 10) || 0;
                        var subtotal = den * cantidad;
                        total += subtotal;
                        $('.abrir-subtotal-billetes[data-denominacion="' + den + '"]').text(money.format(subtotal));
                    });
                    $('#abrirBilleteraTotal').text(money.format(total));
                    return total;
                };

                $(document).on('input', '.abrir-cantidad-billetes', recalcularAbrir);

                $(document).on('click', '.js-abrir-billetera', function() {
                    abrirMozo = $(this).data('id-cuenta');
                    construirFilasAbrir();
                    recalcularAbrir();
                    $('#abrirBilleteraError').addClass('d-none').text('');
                    var modal = new bootstrap.Modal(document.getElementById('abrirBilleteraModal'));
                    modal.show();
                });

                $('#abrirBilleteraConfirmar').on('click', function() {
                    var billetes = {};
                    $('.abrir-cantidad-billetes').each(function() {
                        billetes[String($(this).data('denominacion'))] = Number.parseInt($(this).val() || '0', 10) || 0;
                    });

                    var $boton = $(this);
                    $boton.prop('disabled', true);

                    fetch(endpointAbrir, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ idCuenta: abrirMozo, billetes: billetes })
                    })
                        .then(function(response) { return response.json().then(function(body) { return { ok: response.ok, body: body }; }); })
                        .then(function(result) {
                            if (!result.ok) {
                                var codigo = result.body.error && result.body.error.codigo ? result.body.error.codigo : 'ERROR_INTERNO';
                                throw new Error(codigo);
                            }

                            bootstrap.Modal.getInstance(document.getElementById('abrirBilleteraModal')).hide();
                            return cargar();
                        })
                        .catch(function(error) {
                            $('#abrirBilleteraError').removeClass('d-none').text('No se pudo abrir la billetera (' + error.message + ').');
                        })
                        .finally(function() {
                            $boton.prop('disabled', false);
                        });
                });

                // ---- Modal de Cerrar Billetera (estilo Arqueo) ----

                var cerrarMozo = null;
                var montoEsperadoCierre = 0;

                var construirFilasCierre = function() {
                    var denominaciones = <?php echo json_encode($denominaciones); ?>;
                    var $tbody = $('#cerrarBilleteraBilletesBody');
                    $tbody.empty();
                    denominaciones.forEach(function(den) {
                        $tbody.append(
                            '<tr>'
                            + '<td class="fw-semibold">' + money.format(den) + '</td>'
                            + '<td><input type="number" min="0" step="1" class="form-control cerrar-cantidad-billetes" data-denominacion="' + den + '" value="0"></td>'
                            + '<td class="text-end cerrar-subtotal-billetes" data-denominacion="' + den + '">' + money.format(0) + '</td>'
                            + '</tr>'
                        );
                    });
                };

                var recalcularCierre = function() {
                    var total = 0;
                    $('.cerrar-cantidad-billetes').each(function() {
                        var den = Number($(this).data('denominacion'));
                        var cantidad = Number.parseInt($(this).val() || '0', 10) || 0;
                        var subtotal = den * cantidad;
                        total += subtotal;
                        $('.cerrar-subtotal-billetes[data-denominacion="' + den + '"]').text(money.format(subtotal));
                    });

                    var diferenciaBruta = total - montoEsperadoCierre;
                    var diferencia = diferenciaBruta < 0 ? diferenciaBruta : 0;
                    var cobradoCafeteria = diferenciaBruta > 0 ? diferenciaBruta : 0;
                    $('#cerrarMontoContado').text(money.format(total));
                    $('#cerrarDiferencia').text(money.format(diferencia))
                        .attr('class', 'fs-4 ' + (diferencia < 0 ? 'text-danger' : ''));
                    $('#cerrarCobradoCafeteria').text(money.format(cobradoCafeteria))
                        .attr('class', 'fs-4 ' + (cobradoCafeteria > 0 ? 'text-success' : ''));

                    return total;
                };

                $(document).on('input', '.cerrar-cantidad-billetes', recalcularCierre);

                $(document).on('click', '.js-cerrar-billetera', function() {
                    cerrarMozo = $(this).data('id-cuenta');
                    montoEsperadoCierre = Number($(this).data('monto-apertura')) || 0;

                    $('#cerrarBilleteraModalLabel').text('Cerrar Billetera ' + $(this).data('nombre'));
                    $('#cerrarMontoEsperado').text(money.format(montoEsperadoCierre));
                    construirFilasCierre();
                    recalcularCierre();
                    $('#cerrarBilleteraError').addClass('d-none').text('');

                    var modal = new bootstrap.Modal(document.getElementById('cerrarBilleteraModal'));
                    modal.show();
                });

                $('#cerrarBilleteraConfirmar').on('click', function() {
                    var billetes = {};
                    $('.cerrar-cantidad-billetes').each(function() {
                        billetes[String($(this).data('denominacion'))] = Number.parseInt($(this).val() || '0', 10) || 0;
                    });

                    var $boton = $(this);
                    $boton.prop('disabled', true);

                    fetch(endpointCerrar, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ idCuenta: cerrarMozo, billetes: billetes })
                    })
                        .then(function(response) { return response.json().then(function(body) { return { ok: response.ok, body: body }; }); })
                        .then(function(result) {
                            if (!result.ok) {
                                var codigo = result.body.error && result.body.error.codigo ? result.body.error.codigo : 'ERROR_INTERNO';
                                throw new Error(codigo);
                            }

                            bootstrap.Modal.getInstance(document.getElementById('cerrarBilleteraModal')).hide();
                            return cargar();
                        })
                        .catch(function(error) {
                            $('#cerrarBilleteraError').removeClass('d-none').text('No se pudo cerrar la billetera (' + error.message + ').');
                        })
                        .finally(function() {
                            $boton.prop('disabled', false);
                        });
                });

                // ---- Modal de compensacion ----

                var compensarMozo = null;

                var recalcularResto = function() {
                    var cobrar = Number($('#compensarCobrar').data('valor')) || 0;
                    var pagar = Number.parseFloat($('#compensarPagar').val()) || 0;
                    var resto = cobrar - pagar;
                    $('#compensarResto').val(money.format(resto));
                    return resto;
                };

                $(document).on('click', '.js-compensar-billetera', function() {
                    var idCuenta = $(this).data('id-cuenta');
                    var nombre = $(this).data('nombre');
                    var saldo = Number($(this).data('saldo')) || 0;
                    var cobrar = Math.abs(saldo);

                    compensarMozo = idCuenta;
                    $('#compensarModalError').addClass('d-none').text('');
                    $('#compensarNombre').text(nombre);
                    $('#compensarCobrar').data('valor', cobrar).val(money.format(cobrar));
                    $('#compensarPagar').attr('max', cobrar).val(cobrar.toFixed(2));
                    recalcularResto();

                    var modal = new bootstrap.Modal(document.getElementById('compensarModal'));
                    modal.show();
                });

                $(document).on('input', '#compensarPagar', recalcularResto);

                $('#compensarModalConfirmar').on('click', function() {
                    var importePagar = Number.parseFloat($('#compensarPagar').val()) || 0;
                    var $boton = $(this);
                    $boton.prop('disabled', true);

                    fetch(endpointCompensar, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ idCuenta: compensarMozo, importePagar: importePagar })
                    })
                        .then(function(response) { return response.json().then(function(body) { return { ok: response.ok, body: body }; }); })
                        .then(function(result) {
                            if (!result.ok) {
                                var codigo = result.body.error && result.body.error.codigo ? result.body.error.codigo : 'ERROR_INTERNO';
                                throw new Error(codigo);
                            }

                            var modalEl = document.getElementById('compensarModal');
                            bootstrap.Modal.getInstance(modalEl).hide();

                            return cargar();
                        })
                        .catch(function(error) {
                            $('#compensarModalError').removeClass('d-none').text('No se pudo compensar (' + error.message + ').');
                        })
                        .finally(function() {
                            $boton.prop('disabled', false);
                        });
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
                        <h1 class="mt-4">Billeteras Mozo</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Operaciones</li>
                            <li class="breadcrumb-item">Cafetería</li>
                            <li class="breadcrumb-item active">Billeteras Mozo</li>
                        </ol>
                        <div id="turnoAlert" class="alert alert-warning d-none"></div>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="mozosContenedor">
                            <div class="col-12 text-center text-muted py-4">Cargando billeteras...</div>
                        </div>
                    </div>
                </main>
                <?php include '../footer.php';?>
            </div>

        </div>

        <div class="modal fade" id="abrirBilleteraModal" tabindex="-1" aria-labelledby="abrirBilleteraModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="abrirBilleteraModalLabel">Abrir Billetera</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="abrirBilleteraError" class="alert alert-danger d-none"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th>Cantidad</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="abrirBilleteraBilletesBody"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <span class="fw-semibold">Total contado:</span>
                            <span id="abrirBilleteraTotal" class="fw-bold fs-5">$ 0,00</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="abrirBilleteraConfirmar" class="btn btn-primary">Abrir</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="cerrarBilleteraModal" tabindex="-1" aria-labelledby="cerrarBilleteraModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="cerrarBilleteraModalLabel">Cerrar Billetera</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="cerrarBilleteraError" class="alert alert-danger d-none"></div>
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span>Saldo esperado</span>
                            <strong id="cerrarMontoEsperado">$ 0,00</strong>
                        </div>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Billete</th>
                                        <th style="width: 160px;">Cantidad</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="cerrarBilleteraBilletesBody"></tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body py-3">
                                        <div class="text-muted small">Efectivo contado</div>
                                        <strong id="cerrarMontoContado" class="fs-4">$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body py-3">
                                        <div class="text-muted small">Diferencia</div>
                                        <strong id="cerrarDiferencia" class="fs-4">$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body py-3">
                                        <div class="text-muted small">Cobrado Cafeteria</div>
                                        <strong id="cerrarCobradoCafeteria" class="fs-4">$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="cerrarBilleteraConfirmar" class="btn btn-danger">Confirmar Cierre</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="compensarModal" tabindex="-1" aria-labelledby="compensarModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="compensarModalLabel">Compensar <span id="compensarNombre"></span></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="compensarModalError" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label>Cobrar</label>
                            <input type="text" id="compensarCobrar" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Importe a Pagar</label>
                            <input type="number" id="compensarPagar" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label>Resto</label>
                            <input type="text" id="compensarResto" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="compensarModalConfirmar" class="btn btn-primary">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
