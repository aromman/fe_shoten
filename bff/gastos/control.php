<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/canal.php';
require_once dirname(__DIR__, 2) . '/app/models/gastosClase.php';
require_once dirname(__DIR__, 2) . '/app/models/medioPago.php';
require_once dirname(__DIR__, 2) . '/app/models/gastos.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../../login.php');
    exit;
}

function gastos_bff_normalizar_fecha($valor)
{
    $texto = trim((string) $valor);
    if ($texto === '') {
        return date('Y-m-d');
    }

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $texto, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }

    $timestamp = strtotime($texto);
    return $timestamp === false ? $texto : date('Y-m-d', $timestamp);
}

function gastos_bff_cargar_listados()
{
    $canalPDO = new Canal();
    $gastosClasePDO = new GastosClase();
    $medioPagoPDO = new MedioPago();

    $canales = $canalPDO->getAllActive('nombre ASC');
    $clases = $gastosClasePDO->getAll('nombre ASC');
    $mediosPago = $medioPagoPDO->getAll('nombre ASC');

    return array(
        'canales' => is_array($canales) ? $canales : array(),
        'clases' => is_array($clases) ? $clases : array(),
        'mediosPago' => is_array($mediosPago) ? $mediosPago : array(),
    );
}

function gastos_bff_cargar_gastos_del_periodo($desde = null, $hasta = null)
{
    $gastosPDO = new Gastos();
    $desde = $desde ? gastos_bff_normalizar_fecha($desde) : date('Y-m-01');
    $hasta = $hasta ? gastos_bff_normalizar_fecha($hasta) : date('Y-m-t');

    $sql = "
        SELECT
            g.id,
            g.fecha,
            c.nombre AS canal,
            g.detalle,
            g.monto,
            gc.nombre AS clase,
            mp.nombre AS medio_pago
        FROM gastos g
        INNER JOIN gastos_clase gc ON gc.id = g.id_clase
        INNER JOIN medio_pago mp ON mp.id = g.id_medio_pago
        INNER JOIN canal c ON c.id = g.id_canal
        WHERE g.tipo = 'F'
          AND g.fecha >= :desde
          AND g.fecha <= :hasta
        ORDER BY g.fecha DESC, c.nombre
    ";

    $stm = $gastosPDO->pdo->prepare($sql);
    $stm->execute(array(
        ':desde' => $desde,
        ':hasta' => $hasta,
    ));

    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
    return is_array($rows) ? $rows : array();
}

function gastos_bff_render_row()
{
    $listados = gastos_bff_cargar_listados();
    $canalSeleccionado = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date('Y-m-d'); ?>"></td>
        <td>
            <select name="canal[]" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php foreach ($listados['canales'] as $val) { ?>
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
                <?php foreach ($listados['clases'] as $val) { ?>
                    <option value="<?php echo (int) $val['id']; ?>" data-subtext="(<?php echo (int) $val['id']; ?>)"><?php echo mb_strtoupper($val['nombre'], 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </td>
        <td>
            <select name="medioPago[]" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php foreach ($listados['mediosPago'] as $val) { ?>
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

function gastos_bff_guardar_lote(array $detalles, array $fechas, array $montos, array $clases, array $mediosPago, array $canales)
{
    $gastosPDO = new Gastos();
    $medioPagoPDO = new MedioPago();

    $gastosPDO->pdo->beginTransaction();
    try {
        foreach ($detalles as $key => $detalle) {
            $fecha = gastos_bff_normalizar_fecha($fechas[$key] ?? '');
            $monto = (float) str_replace(',', '.', str_replace(' ', '', (string) ($montos[$key] ?? 0)));
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
}
