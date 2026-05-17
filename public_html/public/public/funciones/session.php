<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sesion_activa = false;
$usuario_id = null;
$usuario_nombre = null;
$usuario_apellido = null;
$usuario_email = null;
$usuario_rol = null;

if (isset($_SESSION['id_usuario'])) {
    $sesion_activa = true;
    $usuario_id = $_SESSION['id_usuario'];
    $usuario_nombre = $_SESSION['nombre'] ?? null;
    $usuario_apellido = $_SESSION['apellido'] ?? null;
    $usuario_email = $_SESSION['email'] ?? null;
    $usuario_rol = $_SESSION['id_rol'] ?? null;
}
?>