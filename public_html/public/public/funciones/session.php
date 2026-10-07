<?php
//Inicia la sesión solo cuando todavía no está activa y expone variables
// Evita iniciar la misma sesión más de una vez.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Valores predeterminados para usuarios no autenticados.
$sesion_activa = false;
$usuario_id = null;
$usuario_nombre = null;
$usuario_apellido = null;
$usuario_email = null;
$usuario_rol = null;

// Si existe un id de usuario, copia sus datos a variables de uso más directo.
if (isset($_SESSION['id_usuario'])) {
    $sesion_activa = true;
    $usuario_id = $_SESSION['id_usuario'];
    $usuario_nombre = $_SESSION['nombre'] ?? null;
    $usuario_apellido = $_SESSION['apellido'] ?? null;
    $usuario_email = $_SESSION['email'] ?? null;
    $usuario_rol = $_SESSION['id_rol'] ?? null;
}
?>