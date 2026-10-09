<?php
session_start();

require_once __DIR__ . '/app/config/url.php';
require_once __DIR__ . '/app/config/navigation.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('Location: ' . app_url('/login.php'));
    exit;
}

$role = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
header('Location: ' . app_url(navigation_default_path($role)));
exit;
