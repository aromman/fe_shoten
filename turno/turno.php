<?php

session_start();

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../app/config/url.php';

if (!isset($_SESSION['turno.csrf'])) {
    $_SESSION['turno.csrf'] = bin2hex(random_bytes(32));
}

$fechaHoy = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d');
$denominaciones = array(10, 20, 50, 100, 200, 500, 1000, 2000, 10000, 20000);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Caja</title>
    <link href="<?php echo app_url('/css/styles.css'); ?>" rel="stylesheet">
    <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php include_once __DIR__ . '/../topBar.php'; ?>
    <div id="layoutSidenav">
        <?php include_once __DIR__ . '/../sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 mb-3">
                        <div>
                            <h1 class="h3 mb-1">Gestión de Caja</h1>
                            <p class="text-muted mb-0">Nueva experiencia de gestión de caja.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button id="nuevoMovimientoButton" class="btn btn-outline-primary d-none" type="button" data-bs-toggle="modal" data-bs-target="#nuevoMovimientoModal">
                                <i class="fas fa-money-bill-transfer me-1"></i> Nuevo Movimiento
                            </button>
                            <button id="cerrarTurnoButton" class="btn btn-danger d-none" type="button" data-bs-toggle="modal" data-bs-target="#registrarArqueoModal">
                                <i class="fas fa-lock me-1"></i> Cerrar Turno
                            </button>
                            <button id="registrarArqueoButton" class="btn btn-success d-none" type="button" data-bs-toggle="modal" data-bs-target="#registrarArqueoModal">
                                <i class="fas fa-calculator me-1"></i> Registrar Arqueo
                            </button>
                            <button id="abrirTurnoButton" class="btn btn-primary d-none" type="button" data-bs-toggle="modal" data-bs-target="#abrirTurnoModal">
                                <i class="fas fa-circle-plus me-1"></i> Abrir Turno
                            </button>
                        </div>
                    </div>
                    <div id="turnoAlert" class="alert alert-info">Cargando información del turno…</div>
                    <div id="turnoCards" class="row g-3 mb-4"></div>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-xl-8">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-receipt me-1"></i> Movimientos del turno · Hoy</span>
                                    <span id="turnoEstado" class="badge bg-secondary">Consultando</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle mb-0">
                                            <thead><tr><th>Fecha</th><th>Detalle</th><th>Tipo</th><th class="text-end">Monto</th><th class="text-end">Saldo</th></tr></thead>
                                            <tbody id="turnoMovimientos"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="fas fa-money-bill-wave me-1"></i> Detalle de billetes
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Billete</th>
                                                    <th class="text-center">Cantidad</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="turnoBilletes"></tbody>
                                            <tfoot id="turnoBilletesTotal"></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-clock-rotate-left me-1"></i> Últimos Arqueos del Turno
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Registrado</th>
                                            <th class="text-end">Apertura</th>
                                            <th class="text-end">Cierre</th>
                                            <th class="text-end">Diferencia</th>
                                        </tr>
                                    </thead>
                                    <tbody id="turnoCierresParciales"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include_once __DIR__ . '/../footer.php'; ?>
        </div>
    </div>

    <div class="modal fade" id="nuevoMovimientoModal" tabindex="-1" aria-labelledby="nuevoMovimientoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="nuevoMovimientoForm">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="nuevoMovimientoModalLabel">Nuevo Movimiento</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div id="nuevoMovimientoError" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label class="form-label" for="movimientoFecha">Fecha</label>
                            <input class="form-control" id="movimientoFecha" type="date" value="<?php echo $fechaHoy; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="movimientoTipo">Tipo</label>
                            <select class="form-select" id="movimientoTipo" required>
                                <option value="">Seleccionar</option>
                                <option value="C">Ingreso</option>
                                <option value="D">Egreso</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="movimientoDetalle">Detalle</label>
                            <input class="form-control" id="movimientoDetalle" type="text" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="movimientoMonto">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input class="form-control" id="movimientoMonto" type="number" min="0.01" max="999999999.99" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirmarMovimiento" type="submit" class="btn btn-primary">Registrar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registrarArqueoModal" tabindex="-1" aria-labelledby="registrarArqueoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="registrarArqueoForm">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="registrarArqueoModalLabel">Registrar Arqueo</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div id="registrarArqueoError" class="alert alert-danger d-none"></div>
                        <div id="cerrarTurnoAdvertencia" class="alert alert-warning d-none">
                            Este arqueo será definitivo y dejará el turno cerrado.
                        </div>
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span>Saldo esperado</span>
                            <strong id="arqueoMontoEsperado">$ 0,00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <div>
                                <label class="form-label mb-0">Inventario actual de billetes</label>
                                <div class="form-text">Corregí las cantidades según el efectivo físico contado.</div>
                            </div>
                            <button id="limpiarArqueoBilletes" class="btn btn-sm btn-outline-secondary" type="button">Limpiar</button>
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
                                <tbody>
                                    <?php foreach ($denominaciones as $denominacion) { ?>
                                        <tr>
                                            <td class="fw-semibold">$ <?php echo number_format($denominacion, 0, ',', '.'); ?></td>
                                            <td>
                                                <input
                                                    class="form-control form-control-sm arqueo-cantidad-billetes"
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    value="0"
                                                    inputmode="numeric"
                                                    data-denominacion="<?php echo $denominacion; ?>"
                                                    aria-label="Cantidad actual de billetes de <?php echo $denominacion; ?>"
                                                >
                                            </td>
                                            <td class="text-end arqueo-subtotal-billetes" data-denominacion="<?php echo $denominacion; ?>">$ 0,00</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-3">
                                        <div class="text-muted small">Efectivo contado</div>
                                        <strong id="arqueoMontoContado" class="fs-4">$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-3">
                                        <div class="text-muted small">Diferencia</div>
                                        <strong id="arqueoDiferencia" class="fs-4">$ 0,00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirmarArqueo" type="submit" class="btn btn-success">Registrar Arqueo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="abrirTurnoModal" tabindex="-1" aria-labelledby="abrirTurnoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="abrirTurnoForm">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="abrirTurnoModalLabel">Abrir Turno</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div id="abrirTurnoError" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label class="form-label" for="fechaApertura">Fecha</label>
                            <input class="form-control" id="fechaApertura" name="fecha" type="date" value="<?php echo $fechaHoy; ?>" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <div>
                                    <label class="form-label mb-0">Conteo de billetes</label>
                                    <div class="form-text">Ingresá la cantidad física de cada denominación.</div>
                                </div>
                                <button id="limpiarBilletes" class="btn btn-sm btn-outline-secondary" type="button">Limpiar</button>
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
                                    <tbody>
                                        <?php foreach ($denominaciones as $denominacion) { ?>
                                            <tr>
                                                <td class="fw-semibold">$ <?php echo number_format($denominacion, 0, ',', '.'); ?></td>
                                                <td>
                                                    <input
                                                        class="form-control form-control-sm cantidad-billetes"
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        value="0"
                                                        inputmode="numeric"
                                                        data-denominacion="<?php echo $denominacion; ?>"
                                                        aria-label="Cantidad de billetes de <?php echo $denominacion; ?>"
                                                    >
                                                </td>
                                                <td class="text-end subtotal-billetes" data-denominacion="<?php echo $denominacion; ?>">$ 0,00</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card bg-light border-primary">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <span class="fw-semibold">Monto de apertura</span>
                                <strong id="montoAperturaTotal" class="fs-4 text-primary">$ 0,00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirmarApertura" type="submit" class="btn btn-primary">Abrir Turno</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
    (function () {
        const endpoint = <?php echo json_encode(app_url('/bff/turno/actual.php')); ?>;
        const abrirEndpoint = <?php echo json_encode(app_url('/bff/turno/abrir.php')); ?>;
        const arqueoEndpoint = <?php echo json_encode(app_url('/bff/turno/arqueo.php')); ?>;
        const cerrarEndpoint = <?php echo json_encode(app_url('/bff/turno/cerrar.php')); ?>;
        const movimientoEndpoint = <?php echo json_encode(app_url('/bff/turno/movimiento.php')); ?>;
        const csrfToken = <?php echo json_encode($_SESSION['turno.csrf']); ?>;
        const money = new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[character]);
        const mensajesError = {
            FECHA_INVALIDA: 'Ingresá una fecha válida.',
            FECHA_ANTERIOR_AL_TURNO: 'La fecha no puede ser anterior a la apertura del turno.',
            MONTO_INVALIDO: 'Ingresá un monto de apertura válido.',
            TIPO_MOVIMIENTO_INVALIDO: 'Seleccioná un tipo de movimiento.',
            DETALLE_MOVIMIENTO_INVALIDO: 'Ingresá un detalle de hasta 50 caracteres.',
            CONTEO_BILLETES_INVALIDO: 'El conteo de billetes no es válido.',
            CANTIDAD_BILLETES_INVALIDA: 'Las cantidades deben ser números enteros iguales o mayores que cero.',
            SOLO_ADMINISTRADOR: 'Sólo un administrador puede realizar esta operación.',
            TURNO_YA_ABIERTO: 'El canal ya tiene un turno abierto.',
            TURNO_NO_ABIERTO: 'No hay un turno abierto para registrar el arqueo.',
            IDEMPOTENCY_KEY_INVALIDA: 'No se pudo identificar la operación. Recargá la página.',
            IDEMPOTENCY_KEY_REUTILIZADA: 'La operación ya fue utilizada con otros datos.',
            CSRF_INVALIDO: 'La sesión venció. Recargá la página e intentá nuevamente.'
        };
        const cantidadesBilletes = Array.from(document.querySelectorAll('.cantidad-billetes'));
        const cantidadesArqueo = Array.from(document.querySelectorAll('.arqueo-cantidad-billetes'));
        let montoEsperadoArqueo = 0;
        let modoArqueo = 'arqueo';
        let inventarioBilletesActual = {};
        let aperturaIdempotencyKey = crearIdempotencyKey();
        let arqueoIdempotencyKey = crearIdempotencyKey();
        let movimientoIdempotencyKey = crearIdempotencyKey();

        function crearIdempotencyKey() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }

            return Date.now().toString(36) + '-' + Math.random().toString(36).slice(2);
        }

        function calcularBilletes() {
            let total = 0;

            cantidadesBilletes.forEach((input) => {
                const denominacion = Number(input.dataset.denominacion);
                const cantidad = Math.max(0, Number.parseInt(input.value || '0', 10) || 0);
                const subtotal = denominacion * cantidad;
                input.value = cantidad;
                total += subtotal;

                document.querySelector(`.subtotal-billetes[data-denominacion="${denominacion}"]`).textContent = money.format(subtotal);
            });

            document.getElementById('montoAperturaTotal').textContent = money.format(total);
            return total;
        }

        function obtenerBilletes() {
            return cantidadesBilletes.reduce((billetes, input) => {
                billetes[input.dataset.denominacion] = Number.parseInt(input.value || '0', 10) || 0;
                return billetes;
            }, {});
        }

        cantidadesBilletes.forEach((input) => input.addEventListener('input', calcularBilletes));
        document.getElementById('limpiarBilletes').addEventListener('click', () => {
            cantidadesBilletes.forEach((input) => {
                input.value = 0;
            });
            calcularBilletes();
            cantidadesBilletes[0].focus();
        });

        function calcularArqueo() {
            let total = 0;

            cantidadesArqueo.forEach((input) => {
                const denominacion = Number(input.dataset.denominacion);
                const cantidad = Math.max(0, Number.parseInt(input.value || '0', 10) || 0);
                const subtotal = denominacion * cantidad;
                input.value = cantidad;
                total += subtotal;

                document.querySelector(`.arqueo-subtotal-billetes[data-denominacion="${denominacion}"]`).textContent = money.format(subtotal);
            });

            const diferencia = total - montoEsperadoArqueo;
            const diferenciaElement = document.getElementById('arqueoDiferencia');
            document.getElementById('arqueoMontoContado').textContent = money.format(total);
            diferenciaElement.textContent = money.format(diferencia);
            diferenciaElement.className = 'fs-4 ' + (diferencia < 0 ? 'text-danger' : (diferencia > 0 ? 'text-success' : ''));

            return total;
        }

        function obtenerBilletesArqueo() {
            return cantidadesArqueo.reduce((billetes, input) => {
                billetes[input.dataset.denominacion] = Number.parseInt(input.value || '0', 10) || 0;
                return billetes;
            }, {});
        }

        function poblarBilletesArqueo(usarInventario) {
            cantidadesArqueo.forEach((input) => {
                input.value = usarInventario
                    ? (inventarioBilletesActual[input.dataset.denominacion] || 0)
                    : 0;
            });
            calcularArqueo();
        }

        cantidadesArqueo.forEach((input) => input.addEventListener('input', calcularArqueo));
        document.getElementById('limpiarArqueoBilletes').addEventListener('click', () => {
            cantidadesArqueo.forEach((input) => {
                input.value = 0;
            });
            calcularArqueo();
            cantidadesArqueo[0].focus();
        });

        function configurarModalArqueo(modo) {
            modoArqueo = modo;
            arqueoIdempotencyKey = crearIdempotencyKey();
            const esCierre = modo === 'cierre';
            document.getElementById('registrarArqueoModalLabel').textContent = esCierre ? 'Cerrar Turno' : 'Registrar Arqueo';
            document.getElementById('confirmarArqueo').textContent = esCierre ? 'Confirmar Cierre' : 'Registrar Arqueo';
            document.getElementById('confirmarArqueo').className = 'btn ' + (esCierre ? 'btn-danger' : 'btn-success');
            document.getElementById('cerrarTurnoAdvertencia').classList.toggle('d-none', !esCierre);
            document.getElementById('registrarArqueoError').classList.add('d-none');
            poblarBilletesArqueo(esCierre);
        }

        document.getElementById('registrarArqueoButton').addEventListener('click', () => configurarModalArqueo('arqueo'));
        document.getElementById('cerrarTurnoButton').addEventListener('click', () => configurarModalArqueo('cierre'));
        document.getElementById('abrirTurnoButton').addEventListener('click', () => {
            aperturaIdempotencyKey = crearIdempotencyKey();
        });
        document.getElementById('nuevoMovimientoButton').addEventListener('click', () => {
            movimientoIdempotencyKey = crearIdempotencyKey();
            document.getElementById('nuevoMovimientoError').classList.add('d-none');
        });

        function cargarTurno() {
            return fetch(endpoint, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then((response) => {
                if (!response.ok) throw new Error('No se pudo consultar el turno');
                return response.json();
            })
            .then(({ data }) => {
                const abierto = data.turno !== null;
                const alert = document.getElementById('turnoAlert');
                alert.className = 'alert ' + (abierto ? 'alert-success' : 'alert-warning');
                alert.textContent = abierto
                    ? `Turno abierto desde ${data.turno.fecha} · Canal ${data.canal.nombre}`
                    : `No hay un turno abierto para ${data.canal.nombre}`;

                const estado = document.getElementById('turnoEstado');
                estado.className = 'badge ' + (abierto ? 'bg-success' : 'bg-secondary');
                estado.textContent = abierto ? 'Abierto' : 'Cerrado';
                document.getElementById('abrirTurnoButton').classList.toggle('d-none', !data.acciones.puedeAbrir);
                document.getElementById('registrarArqueoButton').classList.toggle('d-none', !data.acciones.puedeArqueo);
                document.getElementById('cerrarTurnoButton').classList.toggle('d-none', !data.acciones.puedeCerrar);
                document.getElementById('nuevoMovimientoButton').classList.toggle('d-none', !data.acciones.puedeArqueo);

                document.getElementById('turnoCards').innerHTML = data.tarjetas.map((tarjeta) => `
                    <div class="col-12 col-md-6 col-xl">
                        <div class="card h-100 border-${escapeHtml(tarjeta.tono)}">
                            <div class="card-body">
                                <div class="text-muted small">${escapeHtml(tarjeta.titulo)}</div>
                                <div class="fs-4 fw-bold text-${escapeHtml(tarjeta.tono)}">${money.format(tarjeta.monto)}</div>
                            </div>
                        </div>
                    </div>
                `).join('');

                document.getElementById('turnoMovimientos').innerHTML = data.movimientos.length
                    ? data.movimientos.map((movimiento) => `
                        <tr>
                            <td>${escapeHtml(movimiento.fecha)}</td>
                            <td>${escapeHtml(movimiento.detalle)}</td>
                            <td><span class="badge ${
                                movimiento.tipo === 'I' ? 'bg-success' : (movimiento.tipo === 'E' ? 'bg-danger' : 'bg-secondary')
                            }">${
                                movimiento.tipo === 'I' ? 'Ingreso' : (movimiento.tipo === 'E' ? 'Egreso' : 'Inicial')
                            }</span></td>
                            <td class="text-end">${money.format(Number(movimiento.monto || 0))}</td>
                            <td class="text-end fw-semibold">${money.format(Number(movimiento.saldo || 0))}</td>
                        </tr>
                    `).join('')
                    : '<tr><td colspan="5" class="text-center text-muted py-4">No hay movimientos registrados hoy.</td></tr>';

                const billetes = data.billetes || [];
                const billetesVisibles = billetes.filter((billete) => Number(billete.cantidad || 0) !== 0);
                document.getElementById('turnoBilletes').innerHTML = billetesVisibles.length
                    ? billetesVisibles.map((billete) => `
                        <tr>
                            <td>${money.format(Number(billete.denominacion || 0))}</td>
                            <td class="text-center">${escapeHtml(billete.cantidad)}</td>
                            <td class="text-end">${money.format(Number(billete.subtotal || 0))}</td>
                        </tr>
                    `).join('')
                    : '<tr><td colspan="3" class="text-center text-muted py-4">No hay un conteo de billetes registrado.</td></tr>';

                const totalBilletes = billetesVisibles.reduce((total, billete) => total + Number(billete.subtotal || 0), 0);
                document.getElementById('turnoBilletesTotal').innerHTML = billetesVisibles.length
                    ? `<tr class="table-light fw-bold"><td colspan="2">Total</td><td class="text-end">${money.format(totalBilletes)}</td></tr>`
                    : '';

                montoEsperadoArqueo = Number(data.desglose.saldoEsperado || 0);
                document.getElementById('arqueoMontoEsperado').textContent = money.format(montoEsperadoArqueo);
                inventarioBilletesActual = billetes.reduce((resultado, billete) => {
                    resultado[String(billete.denominacion)] = Number(billete.cantidad || 0);
                    return resultado;
                }, {});
                poblarBilletesArqueo(false);

                const cierresParciales = data.arqueos || [];
                document.getElementById('turnoCierresParciales').innerHTML = cierresParciales.length
                    ? cierresParciales.map((cierre) => {
                        const apertura = Number(cierre.monto_apertura || 0);
                        const cierreMonto = Number(cierre.monto_cierre || 0);
                        const diferencia = cierreMonto - apertura;
                        const diferenciaClass = diferencia < 0 ? 'text-danger' : (diferencia > 0 ? 'text-success' : '');

                        return `
                            <tr>
                                <td>${escapeHtml(cierre.fecha)}</td>
                                <td>${escapeHtml(cierre.username)}</td>
                                <td>${escapeHtml(cierre.updateDate)}</td>
                                <td class="text-end">${money.format(apertura)}</td>
                                <td class="text-end">${money.format(cierreMonto)}</td>
                                <td class="text-end fw-semibold ${diferenciaClass}">${money.format(diferencia)}</td>
                            </tr>
                        `;
                    }).join('')
                    : '<tr><td colspan="6" class="text-center text-muted py-4">No hay cierres parciales para mostrar.</td></tr>';
            })
            .catch((error) => {
                const alert = document.getElementById('turnoAlert');
                alert.className = 'alert alert-danger';
                alert.textContent = error.message;
            });
        }

        document.getElementById('abrirTurnoForm').addEventListener('submit', (event) => {
            event.preventDefault();

            const button = document.getElementById('confirmarApertura');
            const errorAlert = document.getElementById('abrirTurnoError');
            button.disabled = true;
            button.textContent = 'Abriendo...';
            errorAlert.classList.add('d-none');

            fetch(abrirEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'Idempotency-Key': aperturaIdempotencyKey
                },
                body: JSON.stringify({
                    fecha: document.getElementById('fechaApertura').value,
                    billetes: obtenerBilletes()
                })
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok) {
                        const codigo = payload.error?.codigo;
                        throw new Error(mensajesError[codigo] || 'No se pudo abrir el turno.');
                    }
                    return payload;
                })
                .then(() => {
                    aperturaIdempotencyKey = crearIdempotencyKey();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('abrirTurnoModal')).hide();
                    return cargarTurno();
                })
                .catch((error) => {
                    errorAlert.textContent = error.message;
                    errorAlert.classList.remove('d-none');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = 'Abrir Turno';
                });
        });

        document.getElementById('registrarArqueoForm').addEventListener('submit', (event) => {
            event.preventDefault();

            const button = document.getElementById('confirmarArqueo');
            const errorAlert = document.getElementById('registrarArqueoError');
            button.disabled = true;
            button.textContent = 'Registrando...';
            errorAlert.classList.add('d-none');

            const esCierre = modoArqueo === 'cierre';
            fetch(esCierre ? cerrarEndpoint : arqueoEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'Idempotency-Key': arqueoIdempotencyKey
                },
                body: JSON.stringify({ billetes: obtenerBilletesArqueo() })
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok) {
                        const codigo = payload.error?.codigo;
                        throw new Error(mensajesError[codigo] || (esCierre
                            ? 'No se pudo cerrar el turno.'
                            : 'No se pudo registrar el arqueo.'));
                    }
                    return payload;
                })
                .then(() => {
                    arqueoIdempotencyKey = crearIdempotencyKey();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('registrarArqueoModal')).hide();
                    return cargarTurno();
                })
                .catch((error) => {
                    errorAlert.textContent = error.message;
                    errorAlert.classList.remove('d-none');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = esCierre ? 'Confirmar Cierre' : 'Registrar Arqueo';
                });
        });

        document.getElementById('nuevoMovimientoForm').addEventListener('submit', (event) => {
            event.preventDefault();

            const button = document.getElementById('confirmarMovimiento');
            const errorAlert = document.getElementById('nuevoMovimientoError');
            button.disabled = true;
            button.textContent = 'Registrando...';
            errorAlert.classList.add('d-none');

            fetch(movimientoEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'Idempotency-Key': movimientoIdempotencyKey
                },
                body: JSON.stringify({
                    fecha: document.getElementById('movimientoFecha').value,
                    tipo: document.getElementById('movimientoTipo').value,
                    detalle: document.getElementById('movimientoDetalle').value,
                    monto: document.getElementById('movimientoMonto').value
                })
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok) {
                        const codigo = payload.error?.codigo;
                        throw new Error(mensajesError[codigo] || 'No se pudo registrar el movimiento.');
                    }
                    return payload;
                })
                .then(() => {
                    movimientoIdempotencyKey = crearIdempotencyKey();
                    document.getElementById('nuevoMovimientoForm').reset();
                    document.getElementById('movimientoFecha').value = <?php echo json_encode($fechaHoy); ?>;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('nuevoMovimientoModal')).hide();
                    return cargarTurno();
                })
                .catch((error) => {
                    errorAlert.textContent = error.message;
                    errorAlert.classList.remove('d-none');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = 'Registrar Movimiento';
                });
        });

        calcularBilletes();
        calcularArqueo();
        cargarTurno();
    })();
    </script>
</body>
</html>
