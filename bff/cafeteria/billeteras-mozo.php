<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';

use App\Http\JsonResponse;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

const BILLETERAS_MOZO_EQUIPO = 'Mozos';

try {
    $cuentasMovimientosPDO = new CuentasMovimientos();
    $pdo = $cuentasMovimientosPDO->pdo;

    $stmEquipo = $pdo->prepare('SELECT id FROM equipos WHERE equipo = :equipo LIMIT 1');
    $stmEquipo->execute(array(':equipo' => BILLETERAS_MOZO_EQUIPO));
    $equipo = $stmEquipo->fetch(PDO::FETCH_ASSOC);

    if (!is_array($equipo) || empty($equipo)) {
        JsonResponse::enviar(array(
            'data' => array('mozos' => array()),
            'meta' => array('version' => 'v1', 'aviso' => 'EQUIPO_MOZOS_NO_ENCONTRADO'),
        ), 200);
    }

    $idEquipo = (int) $equipo['id'];

    $stmEmpleados = $pdo->prepare(
        'SELECT e.id id_empleado, e.apellido, e.nombre,
                c.id id_cuenta, c.nombre cuenta_nombre, c.saldo_inicial, c.activa cuenta_activa
         FROM equipo_empleado ee
         JOIN empleados e ON e.id = ee.id_empleado
         LEFT JOIN cuentas c ON c.id_empleado = e.id AND c.ambito_uso = "EMPLEADO"
         WHERE ee.id_equipo = :idEquipo
         ORDER BY e.apellido, e.nombre'
    );
    $stmEmpleados->execute(array(':idEquipo' => $idEquipo));
    $filas = $stmEmpleados->fetchAll(PDO::FETCH_ASSOC);

    $idsCuentas = array();
    foreach ($filas as $fila) {
        if (!empty($fila['id_cuenta'])) {
            $idsCuentas[] = (int) $fila['id_cuenta'];
        }
    }

    $saldoPorCuenta = array();
    $turnoAbiertoPorCuenta = array();

    if (!empty($idsCuentas)) {
        $placeholders = implode(',', array_fill(0, count($idsCuentas), '?'));

        $stmSaldo = $pdo->prepare(
            "SELECT id_cuenta,
                    COALESCE(SUM(CASE WHEN tipo_movimiento = 'C' THEN monto ELSE -monto END), 0) saldo
             FROM cuentas_movimientos
             WHERE id_cuenta IN ($placeholders)
             GROUP BY id_cuenta"
        );
        $stmSaldo->execute($idsCuentas);
        foreach ($stmSaldo->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $saldoPorCuenta[(int) $row['id_cuenta']] = (float) $row['saldo'];
        }

        $stmTurno = $pdo->prepare(
            "SELECT id, id_cuenta, fecha_apertura, monto_apertura
             FROM billetera_turnos
             WHERE id_cuenta IN ($placeholders) AND estado = 'A'"
        );
        $stmTurno->execute($idsCuentas);
        foreach ($stmTurno->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $turnoAbiertoPorCuenta[(int) $row['id_cuenta']] = array(
                'id' => (int) $row['id'],
                'fechaApertura' => $row['fecha_apertura'],
                'montoApertura' => (float) $row['monto_apertura'],
            );
        }
    }

    $mozos = array();
    foreach ($filas as $fila) {
        $idCuenta = !empty($fila['id_cuenta']) ? (int) $fila['id_cuenta'] : null;

        $saldo = 0.0;
        if ($idCuenta !== null) {
            $saldoInicial = (float) $fila['saldo_inicial'];
            $saldo = $saldoInicial + (isset($saldoPorCuenta[$idCuenta]) ? $saldoPorCuenta[$idCuenta] : 0.0);
        }

        $mozos[] = array(
            'empleado' => array(
                'id' => (int) $fila['id_empleado'],
                'nombre' => $fila['nombre'],
                'nombreCompleto' => trim($fila['apellido'] . ', ' . $fila['nombre'], ', '),
            ),
            'cuenta' => $idCuenta !== null ? array(
                'id' => $idCuenta,
                'nombre' => $fila['cuenta_nombre'],
                'activa' => (int) $fila['cuenta_activa'] === 1,
            ) : null,
            'saldo' => round($saldo, 2),
            'turnoAbierto' => $idCuenta !== null && isset($turnoAbiertoPorCuenta[$idCuenta]) ? $turnoAbiertoPorCuenta[$idCuenta] : null,
        );
    }

    JsonResponse::enviar(array(
        'data' => array(
            'equipo' => BILLETERAS_MOZO_EQUIPO,
            'mozos' => $mozos,
        ),
        'meta' => array(
            'total' => count($mozos),
            'version' => 'v1',
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
