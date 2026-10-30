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

$id_usuario = (int)$_SESSION['id_usuario'];

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

// Verificar que el correo no esté ocupado por OTRO usuario
$stmt_check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
$stmt_check->bind_param("si", $datos['email'], $id_usuario);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check && $res_check->num_rows > 0) {
    echo json_encode([
        'ok' => false,
        'errores' => ['El correo electrónico ya se encuentra registrado por otro usuario.'],
    ]);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// Sentencia preparada para actualizar los datos
$stmt_update = $conexion->prepare("
    UPDATE usuarios SET 
        nombre = ?,
        apellido = ?,
        email = ?,
        localidad = ?,
        provincia = ?,
        pais = ?
    WHERE id_usuario = ? 
    LIMIT 1
");

$stmt_update->bind_param(
    "ssssssi",
    $datos['nombre'],
    $datos['apellido'],
    $datos['email'],
    $datos['localidad'],
    $datos['provincia'],
    $datos['pais'],
    $id_usuario
);

if ($stmt_update->execute()) {
    // Actualizar las variables de sesión activas
    $_SESSION['nombre']   = $datos['nombre'];
    $_SESSION['apellido'] = $datos['apellido'];
    $_SESSION['email']    = $datos['email'];

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

$stmt_update->close();