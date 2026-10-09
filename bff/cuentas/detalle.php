<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentas.php';
require_once dirname(__DIR__, 2) . '/app/models/canal.php';

use App\Http\JsonResponse;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    JsonResponse::enviar(array('error' => array('codigo' => 'PARAMETRO_INVALIDO')), 422);
}

try {
    $id = (int) $_GET['id'];
    $cuentasPDO = new Cuentas();
    $canalPDO = new Canal();

    $cuenta = $cuentasPDO->getById($id);
    if (!is_array($cuenta) || empty($cuenta)) {
        JsonResponse::enviar(array('error' => array('codigo' => 'NO_ENCONTRADO')), 404);
    }

    $movimientos = array();
    $stm = $cuentasPDO->pdo->prepare(
        "SELECT cm.*, c.nombre canal
         FROM cuentas_movimientos cm
         LEFT JOIN canal c ON cm.id_canal = c.id
         WHERE cm.id_cuenta = :id
         ORDER BY cm.fecha, c.nombre"
    );
    $stm->execute(array(':id' => $id));
    $movimientos = $stm->fetchAll(PDO::FETCH_ASSOC);

    if (isset($cuenta['tipo_saldo']) && $cuenta['tipo_saldo'] === 'E') {
        $stmPrimerPendiente = $cuentasPDO->pdo->prepare(
            "SELECT MIN(fecha) fecha
             FROM cuentas_movimientos
             WHERE id_cuenta = :id
               AND (
                    consolidado IS NULL
                    OR UPPER(TRIM(CAST(consolidado AS CHAR))) IN ('', '0', 'FALSE', 'NO', 'N')
               )"
        );
        $stmPrimerPendiente->execute(array(':id' => $id));
        $primerPendiente = $stmPrimerPendiente->fetch(PDO::FETCH_ASSOC);
        $fechaPrimerPendiente = isset($primerPendiente['fecha']) ? $primerPendiente['fecha'] : null;

        if (!$fechaPrimerPendiente) {
            $fechaPrimerPendiente = null;
        }

        if ($fechaPrimerPendiente !== null) {
        $canalId = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
        $paramsArqueos = array(':fechaPrimerPendiente' => $fechaPrimerPendiente);
        $filtroCanal = '';
        $filtroCanalComparacion = '';

        if ($canalId > 0) {
            $filtroCanal = 'AND tp.id_canal = :canal';
            $filtroCanalComparacion = 'AND tp2.id_canal = tp.id_canal';
            $paramsArqueos[':canal'] = $canalId;
        }

        $stmArqueos = $cuentasPDO->pdo->prepare(
            "SELECT tp.id,
                    tp.fecha,
                    tp.monto_cierre,
                    tp.id_canal,
                    tp.updateDate,
                    c.nombre canal
             FROM turnos_parcial tp
             LEFT JOIN canal c ON tp.id_canal = c.id
             WHERE 1 = 1
               AND tp.fecha >= :fechaPrimerPendiente
               {$filtroCanal}
               AND NOT EXISTS (
                    SELECT 1
                    FROM turnos_parcial tp2
                    WHERE tp2.fecha = tp.fecha
                      {$filtroCanalComparacion}
                      AND (
                            COALESCE(tp2.updateDate, '0000-00-00 00:00:00') > COALESCE(tp.updateDate, '0000-00-00 00:00:00')
                            OR (
                                COALESCE(tp2.updateDate, '0000-00-00 00:00:00') = COALESCE(tp.updateDate, '0000-00-00 00:00:00')
                                AND tp2.id > tp.id
                            )
                      )
               )"
        );
        $stmArqueos->execute($paramsArqueos);
        $arqueos = $stmArqueos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($arqueos as $arqueo) {
            $movimientos[] = array(
                'id' => 'arqueo-' . $arqueo['id'],
                'fecha' => $arqueo['fecha'],
                'id_canal' => $arqueo['id_canal'],
                'canal' => $arqueo['canal'],
                'descripcion' => 'Arqueo #' . $arqueo['id'],
                'tipo_movimiento' => '',
                'monto' => $arqueo['monto_cierre'],
                'consolidado' => '',
                'es_referencia' => true,
                'referencia_tipo' => 'arqueo',
                'orden_referencia' => 1,
                'updatedate' => $arqueo['updateDate'],
            );
        }

        $paramsTurnos = array(':fechaPrimerPendiente' => $fechaPrimerPendiente);
        $filtroCanalTurnos = '';
        $filtroCanalComparacionTurnos = '';

        if ($canalId > 0) {
            $filtroCanalTurnos = 'AND t.id_canal = :canal';
            $filtroCanalComparacionTurnos = 'AND t2.id_canal = t.id_canal';
            $paramsTurnos[':canal'] = $canalId;
        }

        $stmTurnos = $cuentasPDO->pdo->prepare(
            "SELECT t.id,
                    t.fecha,
                    t.monto_cierre,
                    t.id_canal,
                    t.updateDate,
                    c.nombre canal
             FROM turnos t
             LEFT JOIN canal c ON t.id_canal = c.id
             WHERE 1 = 1
               AND t.fecha >= :fechaPrimerPendiente
               AND t.estado = 'C'
               {$filtroCanalTurnos}
               AND NOT EXISTS (
                    SELECT 1
                    FROM turnos t2
                    WHERE t2.fecha = t.fecha
                      AND t2.estado = 'C'
                      {$filtroCanalComparacionTurnos}
                      AND (
                            COALESCE(t2.updateDate, '0000-00-00 00:00:00') > COALESCE(t.updateDate, '0000-00-00 00:00:00')
                            OR (
                                COALESCE(t2.updateDate, '0000-00-00 00:00:00') = COALESCE(t.updateDate, '0000-00-00 00:00:00')
                                AND t2.id > t.id
                            )
                      )
               )"
        );
        $stmTurnos->execute($paramsTurnos);
        $turnos = $stmTurnos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($turnos as $turno) {
            $movimientos[] = array(
                'id' => 'turno-' . $turno['id'],
                'fecha' => $turno['fecha'],
                'id_canal' => $turno['id_canal'],
                'canal' => $turno['canal'],
                'descripcion' => 'Cierre #' . $turno['id'],
                'tipo_movimiento' => '',
                'monto' => $turno['monto_cierre'],
                'consolidado' => '',
                'es_referencia' => true,
                'referencia_tipo' => 'turno',
                'orden_referencia' => 2,
                'updatedate' => $turno['updateDate'],
            );
        }

        usort($movimientos, function ($a, $b) {
            $fechaA = isset($a['fecha']) ? strtotime($a['fecha']) : false;
            $fechaB = isset($b['fecha']) ? strtotime($b['fecha']) : false;

            if ($fechaA !== $fechaB) {
                return $fechaA < $fechaB ? -1 : 1;
            }

            $ordenA = isset($a['orden_referencia']) ? (int) $a['orden_referencia'] : 0;
            $ordenB = isset($b['orden_referencia']) ? (int) $b['orden_referencia'] : 0;

            if ($ordenA !== $ordenB) {
                return $ordenA - $ordenB;
            }

            if ($ordenA > 0) {
                $updateA = isset($a['updatedate']) ? strtotime($a['updatedate']) : false;
                $updateB = isset($b['updatedate']) ? strtotime($b['updatedate']) : false;

                if ($updateA !== $updateB) {
                    return $updateA > $updateB ? -1 : 1;
                }
            }

            return 0;
        });
        }
    }

    $canales = $canalPDO->getAllActive('nombre ASC');
    if (!is_array($canales)) {
        $canales = array();
    }

    JsonResponse::enviar(array(
        'data' => array(
            'cuenta' => $cuenta,
            'movimientos' => $movimientos,
            'canales' => $canales,
        ),
        'meta' => array(
            'version' => 'v1',
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
