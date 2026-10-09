<?php

echo "Comenzando normalizacion efectivo<BR>";

chdir(dirname(__FILE__));

require_once "../app/models/generica.php";

$genericPDO = new Generica();
$idCuentaEfectivo = 1;

$genericPDO->updateSql("UPDATE cuentas_movimientos SET consolidado = 0 WHERE id_cuenta = " . $idCuentaEfectivo);
echo "Movimientos de cuenta efectivo marcados como no consolidados<BR>";

function imprimirLinea($texto)
{
    echo $texto . "<BR>";
}

function formatoMonto($valor)
{
    return number_format((float) $valor, 2, ".", "");
}

function obtenerSaldoEfectivoHastaFecha($genericPDO, $idCuenta, $fecha)
{
    $fecha = substr((string) $fecha, 0, 10);
    $idCuenta = (int) $idCuenta;

    $sql = "
        SELECT
            COALESCE(c.saldo_inicial, 0) +
            COALESCE(SUM(
                CASE
                    WHEN UPPER(COALESCE(cm.tipo_movimiento, '')) = 'D' THEN -COALESCE(cm.monto, 0)
                    ELSE COALESCE(cm.monto, 0)
                END
            ), 0) saldo
        FROM cuentas c
        LEFT JOIN cuentas_movimientos cm
            ON cm.id_cuenta = c.id
           AND DATE(cm.fecha) <= '" . $fecha . "'
        WHERE c.id = " . $idCuenta . "
        GROUP BY c.id, c.saldo_inicial";

    $resultado = $genericPDO->getSql($sql);
    if (is_array($resultado) && isset($resultado[0]['saldo'])) {
        return (float) $resultado[0]['saldo'];
    }

    return 0.0;
}

function crearMovimientoAjuste($genericPDO, $idCuenta, $referencia, $saldoEfectivo, $montoCierre)
{
    if ($montoCierre == $saldoEfectivo) {
        return null;
    }

    $fecha = isset($referencia['fecha']) ? substr((string) $referencia['fecha'], 0, 10) : date('Y-m-d');
    $idCanal = isset($referencia['id_canal']) ? (int) $referencia['id_canal'] : 0;
    $tipoMovimiento = $montoCierre > $saldoEfectivo ? 'C' : 'D';
    $monto = $montoCierre > $saldoEfectivo
        ? ((float) $montoCierre - (float) $saldoEfectivo)
        : ((float) $saldoEfectivo - (float) $montoCierre);
    $descripcion = $referencia['tipo'] === 'Cierre' ? 'Ajuste por Cierre' : 'Ajuste por Arqueo';

    $sql = "INSERT INTO cuentas_movimientos
            (id_canal, id_cuenta, fecha, tipo_movimiento, descripcion, monto, consolidado, id_causal, origen_tipo, origen_id)
            VALUES
            (" . $idCanal . ",
             " . (int) $idCuenta . ",
             '" . $fecha . "',
             '" . $tipoMovimiento . "',
             '" . $descripcion . "',
             " . $monto . ",
             0,
             3,
             NULL,
             NULL)";

    return $genericPDO->insertSql($sql);
}

$sqlReferencias = "
    SELECT
        'Arqueo' tipo,
        1 orden_tipo,
        tp.id,
        tp.fecha,
        tp.monto_cierre,
        tp.id_canal,
        c.nombre canal
    FROM turnos_parcial tp
    LEFT JOIN canal c ON c.id = tp.id_canal
    WHERE tp.id_canal = 1
      AND DATE(tp.fecha) < CURDATE()
      AND NOT EXISTS (
        SELECT 1
        FROM turnos t
        WHERE t.estado = 'C'
          AND t.id_canal = tp.id_canal
          AND DATE(t.fecha) = DATE(tp.fecha)
      )
      AND NOT EXISTS (
        SELECT 1
        FROM turnos_parcial tp2
        WHERE tp2.id_canal = tp.id_canal
          AND DATE(tp2.fecha) = DATE(tp.fecha)
          AND (
            tp2.updateDate > tp.updateDate
            OR (tp2.updateDate = tp.updateDate AND tp2.id > tp.id)
          )
      )
    UNION ALL
    SELECT
        'Cierre' tipo,
        2 orden_tipo,
        t.id,
        t.fecha,
        t.monto_cierre,
        t.id_canal,
        c.nombre canal
    FROM turnos t
    LEFT JOIN canal c ON c.id = t.id_canal
    WHERE t.estado = 'C'
      AND t.id_canal = 1
      AND DATE(t.fecha) < CURDATE()
    ORDER BY fecha ASC, orden_tipo ASC, id ASC";

$referencias = $genericPDO->getSql($sqlReferencias);

if (!is_array($referencias) || count($referencias) === 0) {
    imprimirLinea("No se encontraron arqueos ni cierres.");
    imprimirLinea("Fin");
    exit;
}

imprimirLinea("Cuenta efectivo: " . $idCuentaEfectivo);
imprimirLinea("Tipo | ID | Fecha | Canal | Monto cierre | Saldo efectivo | Diferencia | Hay ajuste | Ajuste");

$cantidadAjustes = 0;
$maxAjustes = 30;

foreach ($referencias as $referencia) {
    $fecha = isset($referencia['fecha']) ? substr((string) $referencia['fecha'], 0, 10) : '';
    $montoCierre = isset($referencia['monto_cierre']) ? (float) $referencia['monto_cierre'] : 0.0;
    $saldoEfectivo = obtenerSaldoEfectivoHastaFecha($genericPDO, $idCuentaEfectivo, $fecha);
    $idAjuste = null;

    if ($montoCierre < $saldoEfectivo || $montoCierre > $saldoEfectivo) {
        $idAjuste = crearMovimientoAjuste($genericPDO, $idCuentaEfectivo, $referencia, $saldoEfectivo, $montoCierre);

        imprimirLinea(
            $referencia['tipo'] . " | " .
            $referencia['id'] . " | " .
            $fecha . " | " .
            (isset($referencia['canal']) ? $referencia['canal'] : '') . " | " .
            formatoMonto($montoCierre) . " | " .
            formatoMonto($saldoEfectivo) . " | " .
            formatoMonto($montoCierre - $saldoEfectivo) . " | " .
            "Si | " .
            "Ajuste #" . $idAjuste
        );

        $cantidadAjustes++;
        if ($cantidadAjustes >= $maxAjustes) {
            imprimirLinea("Se aplicaron " . $cantidadAjustes . " ajustes. Fin de la corrida.");
            exit;
        }
    }
}

imprimirLinea("Fin");

?>
