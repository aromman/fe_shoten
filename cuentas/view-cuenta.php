<?php

session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if(!isset($_GET["id"]) || trim($_GET["id"]) === ""){
    header("location: error.php");
    exit;
}

$id = (int) $_GET["id"];

$forwardOkPermitidos = array('cuentas.php', 'cuentas-personales.php', 'cuentas-empleados.php');
$forwardOk = isset($_GET['forwardOk']) && in_array($_GET['forwardOk'], $forwardOkPermitidos, true)
    ? $_GET['forwardOk']
    : 'cuentas.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include '../topBar.php';?>
<div id="layoutSidenav">
    <?php include '../sidebar.php';?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Cuenta</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($forwardOk); ?>">Cuentas</a></li>
                    <li class="breadcrumb-item active">Ver Cuenta</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-table me-1"></i>Datos Cuenta</span>
                        <form method="post" action="../bff/cuentas/actions.php" class="d-inline">
                            <input type="hidden" name="action" value="consolidarTodoCuenta">
                            <input type="hidden" name="idCuenta" value="<?php echo $id; ?>">
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Esto marcara como consolidados todos los movimientos de esta cuenta con fecha hasta hoy. Desea continuar?')">
                                <i class="fa fa-fw fa-check-double"></i> Consolidar todo a la fecha
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-end">Saldo Inicial</th>
                                    <th class="text-end">Saldo Consolidado</th>
                                    <th class="text-end">Saldo a Consolidar</th>
                                    <th class="text-end">Saldo Actual</th>
                                </tr>
                            </thead>
                            <tbody id="cuentaBody">
                                <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-table me-1"></i>Movimientos</span>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#nuevoMovimientoModal">
                            <i class="fa fa-fw fa-plus-circle"></i> Nuevo movimiento
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3 align-items-end">
                            <div class="col-md-4">
                                <label for="filtroPeriodo" class="form-label">Periodo</label>
                                <select id="filtroPeriodo" class="form-control">
                                    <option value="0">Hoy</option>
                                    <option value="7">Semana</option>
                                    <option value="15">Quincena</option>
                                    <option value="30">Mes</option>
                                    <option value="90">Trimestre</option>
                                    <option value="180">Semestre</option>
                                    <option value="365">A�o</option>
                                    <option value="all" selected>Todos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="verConsolidado">
                                    <label class="form-check-label" for="verConsolidado">Ver consolidado</label>
                                </div>
                            </div>
                        </div>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Canal</th>
                                    <th class="text-center">Descripcion</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Monto</th>
                                    <th class="text-center">Consolidado</th>
                                    <th class="text-center">Saldo</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="movimientosBody">
                                <tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <form>
                    <a href="<?php echo htmlspecialchars($forwardOk); ?>" class="btn btn-secondary ml-2">Volver</a>
                </form>
            </div>
        </main>
        <?php include '../footer.php';?>
    </div>
</div>
<script>
$(function() {
    var movimientosOrigen = [];
    var cuentaOrigen = {};
    var movimientoEdicion = {};
    var editarMovimientoModal = null;
    var ajusteMovimientoModal = null;
    var fechaSolo = function(fecha) {
        return String(fecha || '').substring(0, 10);
    };

    var parseFecha = function(fecha) {
        if (!fecha) {
            return null;
        }

        var texto = String(fecha).trim();
        var match = texto.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
        }

        var parsed = new Date(texto);
        if (isNaN(parsed.getTime())) {
            return null;
        }

        parsed.setHours(0, 0, 0, 0);
        return parsed;
    };

    var formatoFecha = function(fecha) {
        if (!fecha) {
            return '';
        }

        var texto = String(fecha).trim();
        var match = texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
        if (match) {
            var resultadoTexto = match[3] + '/' + match[2] + '/' + match[1];
            if (match[4] !== undefined && (match[4] !== '00' || match[5] !== '00')) {
                resultadoTexto += ' ' + match[4] + ':' + match[5];
            }
            return resultadoTexto;
        }

        var parsed = new Date(texto);
        if (isNaN(parsed.getTime())) {
            return texto;
        }

        var day = String(parsed.getDate()).padStart(2, '0');
        var month = String(parsed.getMonth() + 1).padStart(2, '0');
        var year = parsed.getFullYear();
        var resultado = day + '/' + month + '/' + year;
        var hours = parsed.getHours();
        var minutes = parsed.getMinutes();
        if (hours !== 0 || minutes !== 0) {
            resultado += ' ' + String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        }
        return resultado;
    };

    var fechaParaInput = function(fecha) {
        if (!fecha) {
            return '';
        }

        var texto = String(fecha).trim();
        var match = texto.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return match[1] + '-' + match[2] + '-' + match[3];
        }

        var parsed = new Date(texto);
        if (isNaN(parsed.getTime())) {
            return '';
        }

        var year = parsed.getFullYear();
        var month = String(parsed.getMonth() + 1).padStart(2, '0');
        var day = String(parsed.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    };

    var formatoContable = function(valor) {
        var numero = parseFloat(valor || 0);
        if (isNaN(numero)) {
            numero = 0;
        }

        return numero.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    var calcularSaldoPorConsolidado = function(movimientos, consolidadoEsperado) {
        var saldo = 0;

        $.each(movimientos || [], function(_, row) {
            if (esReferencia(row)) {
                return true;
            }

            if (esConsolidado(row.consolidado) !== consolidadoEsperado) {
                return true;
            }

            var monto = parseFloat(row.monto || 0);
            if (String(row.tipo_movimiento || '').toUpperCase() === 'D') {
                saldo -= monto;
            } else {
                saldo += monto;
            }
        });

        return saldo;
    };

    var claveMesAnio = function(fecha) {
        var texto = String(fecha || '').trim();
        var match = texto.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return match[1] + '-' + match[2];
        }

        var parsed = parseFecha(texto);
        if (!parsed) {
            return '';
        }

        var year = parsed.getFullYear();
        var month = String(parsed.getMonth() + 1).padStart(2, '0');
        return year + '-' + month;
    };

    var etiquetaMesAnio = function(clave) {
        var partes = String(clave || '').split('-');
        if (partes.length !== 2) {
            return 'Consolidados del mes';
        }

        var anio = parseInt(partes[0], 10);
        var mes = parseInt(partes[1], 10) - 1;
        var fecha = new Date(anio, mes, 1);

        return 'Consolidados de ' + fecha.toLocaleDateString('es-AR', {
            month: 'long',
            year: 'numeric'
        });
    };

    var agruparConsolidadosPorMes = function(movimientos) {
        var resumen = null;
        var noConsolidados = [];

        $.each(movimientos || [], function(_, row) {
            if (!esConsolidado(row.consolidado)) {
                noConsolidados.push(row);
                return true;
            }

            if (!resumen) {
                resumen = {
                    fecha: '',
                    canal: '',
                    descripcion: 'Consolidados',
                    tipo_movimiento: 'M',
                    consolidado: 'TRUE',
                    monto: 0,
                    saldo: 0,
                    acciones: '',
                    _cantidad: 0,
                    es_resumen_consolidado: true
                };
            }

            var monto = parseFloat(row.monto || 0);
            if (String(row.tipo_movimiento || '').toUpperCase() === 'D') {
                resumen.monto -= monto;
            } else {
                resumen.monto += monto;
            }
            resumen._cantidad += 1;
        });

        var orden = [];
        if (resumen) {
            resumen.descripcion = 'Consolidados (' + resumen._cantidad + ')';
            orden.push(resumen);
        }

        return orden.concat(noConsolidados);
    };
    var esConsolidado = function(valor) {
        var texto = String(valor || '').trim().toUpperCase();
        return texto === 'TRUE' || texto === '1' || texto === 'SI' || texto === 'S' || texto === 'YES' || texto === 'Y';
    };

    var esReferencia = function(row) {
        return row && (row.es_referencia === true || String(row.es_referencia || '').toLowerCase() === 'true' || String(row.referencia_tipo || '') !== '');
    };


    var etiquetaReferencia = function(row) {
        return String((row && row.referencia_tipo) || '').toLowerCase() === 'turno' ? 'Cierre' : 'Arqueo';
    };

    var ordenMovimiento = function(row) {
        if (!esReferencia(row)) {
            return 0;
        }

        if (String(row.referencia_tipo || '').toLowerCase() === 'turno') {
            return 2;
        }

        return 1;
    };

    var parseMontoEditable = function(valor) {
        var texto = String(valor || '').trim();
        if (!texto) {
            return 0;
        }

        texto = texto.replace(/\s+/g, '');

        if (texto.indexOf(',') !== -1) {
            texto = texto.replace(/\./g, '').replace(',', '.');
        } else {
            texto = texto.replace(/,/g, '');
        }

        var numero = parseFloat(texto);
        return isNaN(numero) ? 0 : numero;
    };

    var abrirModalAjusteMovimiento = function($btn) {
        var tipoOriginal = String($btn.data('tipo-original') || '').toUpperCase();
        var tipoAjuste = String($btn.data('tipo-ajuste') || '').toUpperCase();
        var fecha = String($btn.data('fecha') || '');
        var fechaAjusteTexto = fecha;
        var saldoPosterior = parseMontoEditable($btn.data('saldo-posterior') || 0);
        var montoAjuste = parseMontoEditable($btn.data('monto-ajuste') || 0);
        var descripcion = String($btn.data('descripcion') || '');

        $('#ajusteMovimientoIdCuenta').val($btn.data('id-cuenta') || '<?php echo $id; ?>');
        $('#ajusteMovimientoIdCanal').val($btn.data('id-canal') || '');
        $('#ajusteMovimientoFechaAjuste').val(fechaAjusteTexto);
        $('#ajusteMovimientoFechaOriginal').val(fecha);
        $('#ajusteMovimientoTipoOriginal').val(tipoOriginal);
        $('#ajusteMovimientoTipoAjuste').val(tipoAjuste);
        $('#ajusteMovimientoMontoAjuste').val(montoAjuste.toFixed(2));
        $('#ajusteMovimientoSaldoPosterior').val(saldoPosterior.toFixed(2));
        $('#ajusteMovimientoCuentaVista').text('<?php echo htmlspecialchars($nombreCuenta ?? '', ENT_QUOTES, "UTF-8"); ?>');
        $('#ajusteMovimientoFechaVista').text(formatoFecha(fecha));
        $('#ajusteMovimientoTipoOriginalVista').text(tipoOriginal);
        $('#ajusteMovimientoSaldoVista').text(formatoContable(saldoPosterior));
        $('#ajusteMovimientoMontoVista').text(formatoContable(montoAjuste) + ' (' + (tipoAjuste === 'D' ? 'Debito' : 'Credito') + ')');
        $('#ajusteMovimientoDescripcionOriginal').text(descripcion);
        $('#ajusteMovimientoFechaAjusteVista').text(formatoFecha(fechaAjusteTexto));
        $('#ajusteMovimientoResumen').text(
            'Tipo ajuste: ' + (tipoAjuste === 'D' ? 'Debito' : 'Credito') +
            ' | Monto: ' + formatoContable(montoAjuste) +
            ' | Saldo posterior: ' + formatoContable(saldoPosterior)
        );

        if (!ajusteMovimientoModal) {
            ajusteMovimientoModal = new bootstrap.Modal(document.getElementById('ajusteMovimientoModal'));
        }
        ajusteMovimientoModal.show();
    };

    var confirmarAjusteMovimiento = function(datos) {
        var tipoOriginal = String(datos.tipoMovimiento || '').toUpperCase() === 'D' ? 'D�bito' : 'Cr�dito';
        var tipoAjuste = tipoOriginal === 'D�bito' ? 'Cr�dito' : 'D�bito';
        var mensaje = [
            'Se generaro un ajuste automatico con estos datos:',
            '',
            'Cuenta: ' + (datos.cuentaNombre || ''),
            'Movimiento original: ' + (datos.descripcion || ''),
            'Fecha: ' + formatoFecha(datos.fecha || ''),
            'Tipo original: ' + tipoOriginal,
            'Saldo posterior: ' + formatoContable(datos.saldoPosterior || 0),
            'Ajuste: ' + tipoAjuste + ' ' + formatoContable(datos.montoAjuste || 0),
            '',
            'Desea continuar?'
        ].join('\n');

        return window.confirm(mensaje);
    };

    var aplicarFiltros = function() {
        var periodo = $('#filtroPeriodo').val();
        var verConsolidado = $('#verConsolidado').is(':checked');
        var hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        var desde = null;
        var hasta = new Date(hoy);
        hasta.setHours(23, 59, 59, 999);

        if (periodo === '0') {
            desde = new Date(hoy);
        } else if (periodo === '7' || periodo === '15') {
            var dias = parseInt(periodo, 10);
            desde = new Date(hoy);
            desde.setDate(desde.getDate() - dias);
            desde.setHours(0, 0, 0, 0);
        } else if (periodo === '30') {
            desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        } else if (periodo === '90') {
            var inicioTrimestre = Math.floor(hoy.getMonth() / 3) * 3;
            desde = new Date(hoy.getFullYear(), inicioTrimestre, 1);
        } else if (periodo === '180') {
            var inicioSemestre = hoy.getMonth() < 6 ? 0 : 6;
            desde = new Date(hoy.getFullYear(), inicioSemestre, 1);
        } else if (periodo === '365') {
            desde = new Date(hoy.getFullYear(), 0, 1);
        }

        var saldoPeriodoAnterior = parseFloat(cuentaOrigen.saldo_inicial || 0);
        $.each(movimientosOrigen, function(_, row) {
            var fechaMovimiento = parseFecha(row.fecha);
            if (!fechaMovimiento) {
                return true;
            }

            if (desde && fechaMovimiento < desde) {
                if (esReferencia(row)) {
                    return true;
                }

                var monto = parseFloat(row.monto || 0);
                if (String(row.tipo_movimiento || '').toUpperCase() === 'D') {
                    saldoPeriodoAnterior -= monto;
                } else {
                    saldoPeriodoAnterior += monto;
                }
            }
        });

        var filtrados = $.grep(movimientosOrigen, function(row) {
            var fechaMovimiento = parseFecha(row.fecha);
            var consolidado = String(row.consolidado || '').toUpperCase();

            if (desde) {
                if (!fechaMovimiento) {
                    return false;
                }

                if (fechaMovimiento < desde || fechaMovimiento > hasta) {
                    return false;
                }
            }

            if (periodo !== 'all' && !desde && !fechaMovimiento) {
                return false;
            }

            return true;
        });

        filtrados.sort(function(a, b) {
            var fechaA = parseFecha(a.fecha);
            var fechaB = parseFecha(b.fecha);
            if (!fechaA || !fechaB) {
                return 0;
            }
            if (fechaA.getTime() !== fechaB.getTime()) {
                return fechaA.getTime() - fechaB.getTime();
            }

            var ordenA = ordenMovimiento(a);
            var ordenB = ordenMovimiento(b);
            if (ordenA !== ordenB) {
                return ordenA - ordenB;
            }

            return 0;
        });

        if (!verConsolidado) {
            filtrados = agruparConsolidadosPorMes(filtrados);
        }

        var primeraFechaSinConciliar = null;
        $.each(filtrados, function(_, row) {
            if (!esReferencia(row) && row.es_resumen_consolidado !== true && !esConsolidado(row.consolidado)) {
                primeraFechaSinConciliar = fechaSolo(row.fecha);
                return false;
            }
        });

        var saldo = parseFloat(saldoPeriodoAnterior || 0);
        var rows = '';

        if (!filtrados.length) {
            if (periodo !== 'all') {
                var fechaBaseSolo = desde ? new Date(desde) : new Date(hoy);
                fechaBaseSolo.setDate(fechaBaseSolo.getDate() - 1);
                var filaBase = '<tr class="table-secondary">' +
                '<td>' + formatoFecha(fechaBaseSolo.toISOString().slice(0, 10)) + '</td>' +
                    '<td></td>' +
                    '<td>Saldo Periodo anterior</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td>' + formatoContable(saldoPeriodoAnterior || 0) + '</td>' +
                    '<td></td>' +
                '</tr>';
                $('#movimientosBody').html(filaBase);
                return;
            }

            $('#movimientosBody').html('<tr><td colspan="8" class="text-center text-muted">No hay movimientos para el filtro seleccionado</td></tr>');
            return;
        }

        $.each(filtrados, function(_, row) {
            var monto = parseFloat(row.monto || 0);
            var idMovimiento = row.id || '';
            var referencia = esReferencia(row);
            var esResumenConsolidado = row.es_resumen_consolidado === true;
            var saldoPosterior = saldo;
            var diferenciaReferencia = referencia ? (saldo - monto) : 0;
            var necesitaAjuste = referencia && Math.abs(diferenciaReferencia) > 0.0001;
            var claseFila = '';
            var acciones = '';

            if (!referencia) {
                if (esResumenConsolidado) {
                    saldoPosterior += monto;
                    claseFila = 'table-success fw-bold fila-consolidado-global';
                } else if (String(row.tipo_movimiento || '').toUpperCase() === 'D') {
                    saldoPosterior -= monto;
                } else {
                    saldoPosterior += monto;
                }
            }
            if (!claseFila && necesitaAjuste) {
                claseFila = 'table-danger fw-bold';
            } else if (!claseFila && referencia) {
                claseFila = 'table-info';
            }
            if (!esResumenConsolidado) {
                if (referencia) {
                    if (necesitaAjuste) {
                        acciones = '<form method="post" action="../bff/cuentas/actions.php" class="d-inline"><input type="hidden" name="action" value="crearAjusteReferenciaCuenta"><input type="hidden" name="idCuenta" value="<?php echo $id; ?>"><input type="hidden" name="fecha" value="' + String(row.fecha || '').substring(0, 10) + '"><input type="hidden" name="diferencia" value="' + diferenciaReferencia + '"><button type="submit" class="btn btn-sm btn-danger" title="Crear ajuste" onclick="return confirmarAjusteMovimiento({})"><i class="fa fa-fw fa-balance-scale"></i></button></form>';
                    }
                } else {
                    acciones = '<a href="#" class="text-primary js-editar-movimiento" data-id="' + idMovimiento + '"><i class="fa fa-fw fa-pencil"></i></a> ' +
                        (fechaSolo(row.fecha) === primeraFechaSinConciliar ? '<a href="conciliar-movimiento.php?id=' + idMovimiento + '" class="text-success"><i class="fa fa-fw fa-check-circle"></i></a> ' : '') +
                        (saldoPosterior < 0 ? '<button type="button" class="btn btn-sm btn-danger js-abrir-ajuste-movimiento" title="Crear ajuste" data-id-cuenta="<?php echo $id; ?>" data-id-canal="' + (row.id_canal || '') + '" data-fecha="' + String(row.fecha || '').substring(0, 10) + '" data-descripcion="' + String(row.descripcion || '').replace(/"/g, '&quot;') + '" data-tipo-original="' + String(row.tipo_movimiento || '').toUpperCase() + '" data-saldo-posterior="' + saldoPosterior + '" data-monto-ajuste="' + Math.abs(saldoPosterior || 0) + '" data-tipo-ajuste="' + (String(row.tipo_movimiento || '').toUpperCase() === 'D' ? 'C' : 'D') + '"><i class="fa fa-fw fa-balance-scale"></i></button> ' : '') +
                        '<form method="post" action="../bff/cuentas/actions.php" class="d-inline ms-1"><input type="hidden" name="action" value="deleteMovimiento"><input type="hidden" name="idMovimiento" value="' + idMovimiento + '"><button type="submit" class="btn p-0 border-0 bg-transparent text-danger" title="Eliminar movimiento" onclick="return confirm(\'Desea eliminar este movimiento?\')"><i class="fa fa-fw fa-trash"></i></button></form>';
                }
            }
            rows += '<tr' + (claseFila ? ' class="' + claseFila + '"' : '') + '>' +
                '<td>' + formatoFecha(row.fecha) + '</td>' +
                '<td>' + (row.canal || '') + '</td>' +
                '<td>' + (row.descripcion || '') + '</td>' +
                '<td>' + (referencia ? 'Referencia' : (esResumenConsolidado ? (monto < 0 ? 'Debito' : 'Credito') : (String(row.tipo_movimiento || '').toUpperCase() === 'D' ? 'Debito' : 'Credito'))) + '</td>' +
                '<td>' + formatoContable(esResumenConsolidado ? Math.abs(monto || 0) : (monto || 0)) + '</td>' +
                '<td class="text-center">' + (referencia ? '<span class="badge bg-info text-dark">' + etiquetaReferencia(row) + '</span>' : (esResumenConsolidado ? '<span class="badge bg-success text-dark">Resumen</span>' : (esConsolidado(row.consolidado) ? '<span class="badge bg-success">Si</span>' : '<span class="badge bg-secondary">No</span>'))) + '</td>' +
                '<td>' + formatoContable(saldoPosterior || 0) + '</td>' +
                '<td class="text-center">' + acciones + '</td>' +
            '</tr>';
            saldo = saldoPosterior;
        });

        if (periodo !== 'all') {
            var fechaBase = desde ? new Date(desde) : new Date(hoy);
            fechaBase.setDate(fechaBase.getDate() - 1);
            var filaBase = '<tr class="table-secondary">' +
                '<td>' + formatoFecha(fechaBase.toISOString().slice(0, 10)) + '</td>' +
                '<td></td>' +
                '<td>Saldo Periodo anterior</td>' +
                '<td></td>' +
                '<td></td>' +
                '<td></td>' +
                '<td>' + formatoContable(saldoPeriodoAnterior || 0) + '</td>' +
                '<td></td>' +
            '</tr>';
            rows = filaBase + rows;
        }

        $('#movimientosBody').html(rows);
    };

    var abrirModalMovimiento = function(idMovimiento) {
        $.getJSON('../bff/cuentas/movimiento.php?id=' + encodeURIComponent(idMovimiento))
            .done(function(response) {
                var movimiento = response.data.movimiento || {};
                movimientoEdicion = movimiento;
                $('#movimientoId').val(movimiento.id || '');
                $('#movimientoIdCuenta').val(movimiento.id_cuenta || '');
                $('#movimientoFecha').val(fechaParaInput(movimiento.fecha || ''));
                $('#movimientoDescripcion').val(movimiento.descripcion || '');
                $('#movimientoMonto').val(formatoContable(movimiento.monto || 0));
                $('#movimientoTipo').val(String(movimiento.tipo_movimiento || '').toUpperCase());
                $('#movimientoConsolidado').prop('checked', String(movimiento.consolidado || '').toUpperCase() === 'TRUE' || String(movimiento.consolidado || '') === '1');
                if (!editarMovimientoModal) {
                    editarMovimientoModal = new bootstrap.Modal(document.getElementById('editarMovimientoModal'));
                }
                editarMovimientoModal.show();
            })
            .fail(function() {
                alert('No se pudo cargar el movimiento.');
            });
    };

    $.getJSON('../bff/cuentas/detalle.php?id=<?php echo $id; ?>')
        .done(function(response) {
            var cuenta = response.data.cuenta || {};
            movimientosOrigen = response.data.movimientos || [];
            cuentaOrigen = cuenta;
            var tipoCuenta = {"A":"Activo","P":"Pasivo"};
            var saldoConsolidado = calcularSaldoPorConsolidado(movimientosOrigen, true);
            var saldoAConsolidar = calcularSaldoPorConsolidado(movimientosOrigen, false);
            var saldoActual = parseFloat(cuenta.saldo_inicial || 0) + saldoConsolidado + saldoAConsolidar;

            $('#cuentaBody').html(
                '<tr>' +
                    '<td>' + (cuenta.nombre || '') + '</td>' +
                    '<td>' + (tipoCuenta[cuenta.tipo] || cuenta.tipo || '') + '</td>' +
                    '<td class="text-end">' +
                        '<span class="me-2">' + formatoContable(cuenta.saldo_inicial || 0) + '</span>' +
                        '<a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#saldoInicialModal"><i class="fa fa-fw fa-pencil"></i></a>' +
                    '</td>' +
                    '<td class="text-end">' + formatoContable(saldoConsolidado || 0) + '</td>' +
                    '<td class="text-end">' + formatoContable(saldoAConsolidar || 0) + '</td>' +
                    '<td class="text-end"><span class="badge bg-dark fs-6 px-3 py-2">' + formatoContable(saldoActual || 0) + '</span></td>' +
                '</tr>'
            );

            $('#saldoInicialModalInput').val(Number(cuenta.saldo_inicial || 0).toFixed(2));
            $('#cuentaIdModal').val(cuenta.id || <?php echo $id; ?>);

            aplicarFiltros();
        })
        .fail(function() {
            $('#cuentaBody').html('<tr><td colspan="3" class="text-center text-danger">No se pudo cargar la cuenta.</td></tr>');
            $('#movimientosBody').html('<tr><td colspan="7" class="text-center text-danger">No se pudieron cargar los movimientos.</td></tr>');
        });

    $('#filtroPeriodo, #verConsolidado').on('change', aplicarFiltros);
    $('#movimientosBody').on('click', '.js-editar-movimiento', function(e) {
        e.preventDefault();
        abrirModalMovimiento($(this).data('id'));
    });

    $('#movimientosBody').on('click', '.js-abrir-ajuste-movimiento', function(e) {
        e.preventDefault();
        abrirModalAjusteMovimiento($(this));
    });

    $('#movimientoMonto').on('focus', function() {
        $(this).val(parseMontoEditable($(this).val()));
    });

    $('#movimientoMonto').on('blur', function() {
        $(this).val(formatoContable($(this).val()));
    });

    $('#formMovimientoEdicion').on('submit', function() {
        $('#movimientoMonto').val(parseMontoEditable($('#movimientoMonto').val()));
        return true;
    });

    $('#nuevoMovimientoMonto').on('focus', function() {
        $(this).val(parseMontoEditable($(this).val()));
    });

    $('#nuevoMovimientoMonto').on('blur', function() {
        $(this).val(formatoContable($(this).val()));
    });

    $('#formNuevoMovimiento').on('submit', function() {
        $('#nuevoMovimientoMonto').val(parseMontoEditable($('#nuevoMovimientoMonto').val()));
        return true;
    });

    $('#nuevoMovimientoModal').on('show.bs.modal', function() {
        $('#nuevoMovimientoFecha').val(new Date().toISOString().slice(0, 10));
        $('#nuevoMovimientoDescripcion').val('');
        $('#nuevoMovimientoTipo').val('');
        $('#nuevoMovimientoMonto').val('');
        $('#nuevoMovimientoConsolidado').prop('checked', false);
    });

    $('#saldoInicialModal').on('show.bs.modal', function() {
        $('#saldoInicialModalInput').val(Number(cuentaOrigen.saldo_inicial || 0).toFixed(2));
    });
});
</script>

<div class="modal fade" id="ajusteMovimientoModal" tabindex="-1" aria-labelledby="ajusteMovimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="ajusteMovimientoModalLabel">Confirmar ajuste autom�tico</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <div id="ajusteMovimientoResumen" class="fw-bold"></div>
                </div>
                <dl class="row mb-0 small">
                    <dt class="col-5">Cuenta</dt>
                    <dd class="col-7" id="ajusteMovimientoCuentaVista"></dd>
                    <dt class="col-5">Fecha original</dt>
                    <dd class="col-7" id="ajusteMovimientoFechaVista"></dd>
                    <dt class="col-5">Fecha ajuste</dt>
                    <dd class="col-7" id="ajusteMovimientoFechaAjusteVista"></dd>
                    <dt class="col-5">Tipo original</dt>
                    <dd class="col-7" id="ajusteMovimientoTipoOriginalVista"></dd>
                    <dt class="col-5">Saldo posterior</dt>
                    <dd class="col-7" id="ajusteMovimientoSaldoVista"></dd>
                    <dt class="col-5">Ajuste</dt>
                    <dd class="col-7" id="ajusteMovimientoMontoVista"></dd>
                    <dt class="col-5">Descripción</dt>
                    <dd class="col-7" id="ajusteMovimientoDescripcionOriginal"></dd>
                </dl>
                <form id="formAjusteMovimiento" action="../bff/cuentas/actions.php" method="post" class="mt-3">
                    <input type="hidden" name="action" value="crearAjusteMovimiento">
                    <input type="hidden" name="idCuenta" id="ajusteMovimientoIdCuenta" value="<?php echo $id; ?>">
                    <input type="hidden" name="fechaAjuste" id="ajusteMovimientoFechaAjuste" value="">
                    <input type="hidden" name="tipoMovimientoAjuste" id="ajusteMovimientoTipoAjuste" value="">
                    <input type="hidden" name="montoAjuste" id="ajusteMovimientoMontoAjuste" value="">
                    <input type="hidden" name="idCanal" id="ajusteMovimientoIdCanal" value="">
                    <input type="hidden" name="descripcionAjuste" id="ajusteMovimientoDescripcionAjuste" value="Ajuste autom�tico por saldo negativo">
                    <button type="submit" class="btn btn-danger w-100">Confirmar ajuste</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saldoInicialModal" tabindex="-1" aria-labelledby="saldoInicialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="saldoInicialModalLabel">Editar saldo inicial</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSaldoInicial" action="../bff/cuentas/actions.php" method="post">
                    <input type="hidden" name="action" value="updateSaldo">
                    <input type="hidden" name="id" id="cuentaIdModal" value="<?php echo $id; ?>">
                    <div class="mb-3">
                        <label>Saldo Inicial</label>
                        <input type="number" step=".01" name="saldoInicial" id="saldoInicialModalInput" class="form-control">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Grabar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoMovimientoModal" tabindex="-1" aria-labelledby="nuevoMovimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="nuevoMovimientoModalLabel">Nuevo movimiento</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoMovimiento" action="../bff/cuentas/actions.php" method="post">
                    <input type="hidden" name="action" value="createMovimiento">
                    <input type="hidden" name="idCuenta" value="<?php echo $id; ?>">
                    <input type="hidden" name="idCausal" value="5">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" id="nuevoMovimientoFecha" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Descripcion</label>
                        <input type="text" name="descripcion" id="nuevoMovimientoDescripcion" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Tipo de movimiento</label>
                        <select name="tipoMovimiento" id="nuevoMovimientoTipo" class="form-control" required="required">
                            <option value="">Seleccione</option>
                            <option value="D">Debito</option>
                            <option value="C">Credito</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Monto</label>
                        <input type="text" name="monto" id="nuevoMovimientoMonto" class="form-control" inputmode="decimal" required="required">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="nuevoMovimientoConsolidado" name="consolidado" value="TRUE">
                        <label class="form-check-label" for="nuevoMovimientoConsolidado">Consolidado</label>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Grabar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarMovimientoModal" tabindex="-1" aria-labelledby="editarMovimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editarMovimientoModalLabel">Editar movimiento</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formMovimientoEdicion" action="../bff/cuentas/actions.php" method="post">
                    <input type="hidden" name="action" value="updateMovimiento">
                    <input type="hidden" name="id" id="movimientoId">
                    <input type="hidden" name="idCuenta" id="movimientoIdCuenta">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" id="movimientoFecha" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Descripcion</label>
                        <input type="text" name="descripcion" id="movimientoDescripcion" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Tipo de movimiento</label>
                        <select name="tipoMovimiento" id="movimientoTipo" class="form-control" required="required">
                            <option value="">Seleccione</option>
                            <option value="D">Debito</option>
                            <option value="C">Credito</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Monto</label>
                        <input type="text" name="monto" id="movimientoMonto" class="form-control" inputmode="decimal" required="required">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="movimientoConsolidado" name="consolidado" value="TRUE">
                        <label class="form-check-label" for="movimientoConsolidado">Consolidado</label>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Grabar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
