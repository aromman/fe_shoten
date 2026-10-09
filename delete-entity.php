<?php

/**
 * Baja genérica de registros.
 *
 * Params
 * entityName : Nombre de entidad a eliminar (debe estar en $entidadesPermitidas)
 * delId      : Identificador numérico
 * forwardOk  : Ruta interna a la que redirigir al terminar
 *
 * Solo usuarios logueados con rol Administrador (0).
 */

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/config/url.php';

// Entidades que se pueden borrar desde las pantallas del sistema.
$entidadesPermitidas = array(
    'canal',
    'clientes',
    'compras',
    'descuentos',
    'editoriales',
    'empleados',
    'equipo_empleado',
    'equipos',
    'gastos',
    'gastos_clase',
    'inversiones',
    'medio_pago',
    'orden_compra',
    'presupuesto',
    'productos',
    'productos_formato',
    'productos_serie',
    'productos_stock',
    'productos_ubicacion',
    'productos_ubicacion_tipo',
    'proveedores',
    'roles',
    'salario',
    'users',
    'ventas',
);

function delete_entity_denegar($status, $mensaje)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><a href="javascript:history.back()">Volver</a></p>';
    exit;
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('Location: ' . app_url('/login.php'));
    exit;
}

if (!isset($_SESSION['user.rol']) || (int) $_SESSION['user.rol'] !== 0) {
    delete_entity_denegar(403, 'No tenés permisos para eliminar registros.');
}

$entidad = isset($_REQUEST['entityName']) ? (string) $_REQUEST['entityName'] : '';
$id = isset($_REQUEST['delId']) ? trim((string) $_REQUEST['delId']) : '';
$forward = isset($_REQUEST['forwardOk']) ? (string) $_REQUEST['forwardOk'] : '';

if ($entidad === '' || $id === '' || $forward === '') {
    delete_entity_denegar(400, 'Faltan datos para eliminar el registro.');
}

if (!in_array($entidad, $entidadesPermitidas, true)) {
    delete_entity_denegar(400, 'Entidad no permitida.');
}

if (!ctype_digit($id)) {
    delete_entity_denegar(400, 'Identificador inválido.');
}

if ($entidad === 'users' && isset($_SESSION['user.id']) && (int) $_SESSION['user.id'] === (int) $id) {
    delete_entity_denegar(400, 'No podés eliminar tu propio usuario.');
}

// Solo rutas internas: nada de esquemas (http:, javascript:), hosts (//) ni saltos de línea.
if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $forward) || strpos($forward, '//') === 0
    || strpos($forward, '\\') !== false || preg_match('/[\r\n]/', $forward)) {
    delete_entity_denegar(400, 'Redirección inválida.');
}

// El nombre de tabla sale de la lista permitida, el id va como parámetro.
$stmt = $db->prepare('DELETE FROM `' . $entidad . '` WHERE id = ?');
$idEntero = (int) $id;
$stmt->bind_param('i', $idEntero);
$stmt->execute();
$stmt->close();

unset($_SESSION['productosRows']);

header('Location: ' . app_url('/' . ltrim($forward, '/')));
exit;
