<?php
session_start();
header('Content-Type: application/json');

include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'ok' => false,
        'errores' => ['Sesión no válida. Inicie sesión nuevamente.']
    ]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$campos = ['nombre', 'apellido', 'email', 'localidad', 'provincia', 'pais'];
$datos = [];
$errores = [];

foreach ($campos as $campo) {
    $valor = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    if ($valor === '') {
        $errores[] = "El campo $campo es obligatorio";
    }
    $datos[$campo] = $valor;
}

if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no es válido';
}

if (!empty($errores)) {
    echo json_encode([
        'ok' => false,
        'errores' => $errores,
    ]);
    exit;
}

$nombre = mysqli_real_escape_string($conexion, $datos['nombre']);
$apellido = mysqli_real_escape_string($conexion, $datos['apellido']);
$email = mysqli_real_escape_string($conexion, $datos['email']);
$localidad = mysqli_real_escape_string($conexion, $datos['localidad']);
$provincia = mysqli_real_escape_string($conexion, $datos['provincia']);
$pais = mysqli_real_escape_string($conexion, $datos['pais']);

$query = "UPDATE usuarios SET 
            nombre = '$nombre',
            apellido = '$apellido',
            email = '$email',
            localidad = '$localidad',
            provincia = '$provincia',
            pais = '$pais'
          WHERE id_usuario = '$id_usuario' 
          LIMIT 1";

if (mysqli_query($conexion, $query)) {
    echo json_encode([
        'ok' => true,
        'errores' => [],
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'errores' => ['Error al actualizar en la base de datos.'],
    ]);
}
