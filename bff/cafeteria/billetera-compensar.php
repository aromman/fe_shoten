<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/billeteraTurno.php';

use App\Http\JsonResponse;

const BILLETERA_MOZO_EQUIPO = 'Mozos';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    JsonResponse::enviar(array('error' => array('codigo' => 'JSON_INVALIDO')), 400);
}

$idCuenta = isset($payload['idCuenta']) ? (int) $payload['idCuenta'] : 0;
$importePagar = isset($payload['importePagar']) ? (float) $payload['importePagar'] : 0;
$username = isset($_SESSION['user.username']) ? $_SESSION['user.username'] : '';
$idCanal = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;

if ($idCuenta <= 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CUENTA_INVALIDA')), 422);
}

if ($idCanal <= 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CANAL_REQUERIDO')), 400);
}

try {
    $billeteraPDO = new BilleteraTurno();

    $stmValidacion = $billeteraPDO->pdo->prepare(
        "SELECT e.nombre
         FROM cuentas c
         JOIN empleados e ON e.id = c.id_empleado
         JOIN equipo_empleado ee ON ee.id_empleado = e.id
         JOIN equipos eq ON eq.id = ee.id_equipo
         WHERE c.id = :idCuenta AND c.ambito_uso = 'EMPLEADO' AND eq.equipo = :equipo
         LIMIT 1"
    );
    $stmValidacion->execute(array(':idCuenta' => $idCuenta, ':equipo' => BILLETERA_MOZO_EQUIPO));
    $nombreEmpleado = $stmValidacion->fetchColumn();
    if ($nombreEmpleado === false) {
        JsonResponse::enviar(array('error' => array('codigo' => 'CUENTA_NO_ES_BILLETERA_MOZO')), 422);
    }

    $resultado = $billeteraPDO->compensar($idCuenta, $importePagar, $username, $idCanal, $nombreEmpleado);

    JsonResponse::enviar(array('data' => array('compensacion' => $resultado)), 200);
} catch (\InvalidArgumentException $error) {
    JsonResponse::enviar(array('error' => array('codigo' => $error->getMessage())), 422);
} catch (\DomainException $error) {
    JsonResponse::enviar(array('error' => array('codigo' => $error->getMessage())), 409);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
