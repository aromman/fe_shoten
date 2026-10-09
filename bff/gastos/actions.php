<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/canal.php';
require_once dirname(__DIR__, 2) . '/app/models/gastosClase.php';
require_once dirname(__DIR__, 2) . '/app/models/gastos.php';
require_once dirname(__DIR__, 2) . '/app/models/medioPago.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../../login.php');
    exit;
}

function gastos_bff_normalizar_fecha($valor)
{
    $texto = trim((string) $valor);
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $texto, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }

    $timestamp = strtotime($texto);
    return $timestamp === false ? $texto : date('Y-m-d', $timestamp);
}

function gastos_bff_normalizar_monto($valor)
{
    $texto = trim((string) $valor);
    $texto = str_replace(' ', '', $texto);
    if (strpos($texto, ',') !== false) {
        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);
    } else {
        $texto = str_replace(',', '', $texto);
    }

    return (float) $texto;
}

function gastos_bff_redirigir($url)
{
    header('location: ' . $url);
    exit;
}

function gastos_bff_render_row()
{
    $data = gastos_bff_cargar_listados();
    $canales = $data['canales'];
    $clases = $data['clases'];
    $mediosPago = $data['mediosPago'];
    $canalSeleccionado = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date('Y-m-d'); ?>"></td>
        <td>
            <select name="canal[]" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php foreach ($canales as $val) { ?>
                    <option value="<?php echo (int) $val['id']; ?>" data-subtext="(<?php echo (int) $val['id']; ?>)" <?php if ((int) $val['id'] === $canalSeleccionado) echo 'selected="selected"'; ?>>
                        <?php echo mb_strtoupper($val['nombre'], 'UTF-8'); ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <td><input type="text" name="detalle[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>
        <td>
            <select name="clase[]" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php foreach ($clases as $val) { ?>
                    <option value="<?php echo (int) $val['id']; ?>" data-subtext="(<?php echo (int) $val['id']; ?>)"><?php echo mb_strtoupper($val['nombre'], 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </td>
        <td>
            <select name="medioPago[]" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php foreach ($mediosPago as $val) { ?>
                    <option value="<?php echo (int) $val['id']; ?>" data-subtext="(<?php echo (int) $val['id']; ?>)"><?php echo mb_strtoupper($val['nombre'], 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este gasto?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

try {
    if ($action === 'addDataRowGastos') {
        gastos_bff_render_row();
        echo '|***|addmore';
        exit;
    }

    if ($action === 'savegastosAddMore') {
        $detalles = isset($_POST['detalle']) && is_array($_POST['detalle']) ? $_POST['detalle'] : array();
        $fechas = isset($_POST['fecha']) && is_array($_POST['fecha']) ? $_POST['fecha'] : array();
        $montos = isset($_POST['monto']) && is_array($_POST['monto']) ? $_POST['monto'] : array();
        $clases = isset($_POST['clase']) && is_array($_POST['clase']) ? $_POST['clase'] : array();
        $mediosPago = isset($_POST['medioPago']) && is_array($_POST['medioPago']) ? $_POST['medioPago'] : array();
        $canales = isset($_POST['canal']) && is_array($_POST['canal']) ? $_POST['canal'] : array();

        $gastosPDO = new Gastos();
        $medioPagoPDO = new MedioPago();

        $gastosPDO->pdo->beginTransaction();
        try {
            foreach ($detalles as $key => $detalle) {
                $fecha = gastos_bff_normalizar_fecha($fechas[$key] ?? '');
                $monto = gastos_bff_normalizar_monto($montos[$key] ?? 0);
                $clase = (int) ($clases[$key] ?? 0);
                $medioPago = (int) ($mediosPago[$key] ?? 0);
                $canal = (int) ($canales[$key] ?? 0);
                $detalle = trim((string) $detalle);

                if ($detalle === '' || $clase <= 0 || $medioPago <= 0 || $canal <= 0) {
                    throw new RuntimeException('DATOS_INVALIDOS');
                }

                $stm = $gastosPDO->pdo->prepare('INSERT INTO gastos (fecha, detalle, monto, tipo, id_clase, id_medio_pago, id_canal) VALUES (:fecha, :detalle, :monto, "F", :clase, :medioPago, :canal)');
                $stm->execute(array(
                    ':fecha' => $fecha,
                    ':detalle' => $detalle,
                    ':monto' => $monto,
                    ':clase' => $clase,
                    ':medioPago' => $medioPago,
                    ':canal' => $canal,
                ));
                $idGasto = (int) $gastosPDO->pdo->lastInsertId();

                $medio = $medioPagoPDO->getById($medioPago);
                if (!is_array($medio) || !isset($medio['id_cuenta']) || (int) $medio['id_cuenta'] <= 0) {
                    throw new RuntimeException('MEDIO_PAGO_SIN_CUENTA');
                }

                $movimientoPDO = new CuentasMovimientos();
                $movimientoPDO->setIdCuenta((int) $medio['id_cuenta']);
                $movimientoPDO->setFecha($fecha);
                $movimientoPDO->setTipoMovimiento('D');
                $movimientoPDO->setDescripcion($detalle);
                $movimientoPDO->setMonto($monto);
                $movimientoPDO->setConsolidado('FALSE');
                $movimientoPDO->setIdCausal(4);
                $movimientoPDO->setIdCanal($canal);
                $movimientoPDO->setOrigenTipo('gasto');
                $movimientoPDO->setOrigenId($idGasto);
                $movimientoPDO->create();
            }

            $gastosPDO->pdo->commit();
        } catch (\Throwable $e) {
            if ($gastosPDO->pdo->inTransaction()) {
                $gastosPDO->pdo->rollBack();
            }
            throw $e;
        }

        echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
        exit;
    }

    if ($action === 'update') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            throw new RuntimeException('GASTO_INVALIDO');
        }

        $gastosPDO = new Gastos();
        $stm = $gastosPDO->pdo->prepare('UPDATE gastos SET fecha = :fecha, detalle = :detalle, monto = :monto, id_clase = :clase, id_medio_pago = :medioPago, id_canal = :canal WHERE id = :id');
        $stm->execute(array(
            ':fecha' => gastos_bff_normalizar_fecha($_POST['fecha'] ?? ''),
            ':detalle' => trim((string) ($_POST['detalle'] ?? '')),
            ':monto' => gastos_bff_normalizar_monto($_POST['monto'] ?? 0),
            ':clase' => (int) ($_POST['clase'] ?? 0),
            ':medioPago' => (int) ($_POST['medioPago'] ?? 0),
            ':canal' => (int) ($_POST['canal'] ?? 0),
            ':id' => $id,
        ));

        gastos_bff_redirigir('../../gastos/gastos.php');
    }

    gastos_bff_redirigir('../../gastos/gastos.php');
} catch (\Throwable $error) {
    error_log($error->getMessage());
    gastos_bff_redirigir('../../errors.php');
}

