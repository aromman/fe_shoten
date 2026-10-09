<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/TurnosBootstrap.php';

use App\Http\JsonResponse;
use App\Turnos\TurnosBootstrap;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$csrfToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
$sessionToken = isset($_SESSION['turno.csrf']) ? $_SESSION['turno.csrf'] : '';
if ($sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    JsonResponse::enviar(array('error' => array('codigo' => 'CSRF_INVALIDO')), 403);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    JsonResponse::enviar(array('error' => array('codigo' => 'JSON_INVALIDO')), 400);
}

$canalId = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;
$username = isset($_SESSION['user.username']) ? $_SESSION['user.username'] : '';
$idempotencyKey = isset($_SERVER['HTTP_IDEMPOTENCY_KEY']) ? $_SERVER['HTTP_IDEMPOTENCY_KEY'] : '';

try {
    $arqueo = TurnosBootstrap::registrarArqueo()->ejecutar(
        $canalId,
        isset($payload['billetes']) ? $payload['billetes'] : null,
        $username,
        $idempotencyKey
    );

    JsonResponse::enviar(array('data' => array('arqueo' => $arqueo)), 201);
} catch (\InvalidArgumentException $error) {
    JsonResponse::enviar(array('error' => array('codigo' => $error->getMessage())), 422);
} catch (\DomainException $error) {
    JsonResponse::enviar(array('error' => array('codigo' => $error->getMessage())), 409);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
