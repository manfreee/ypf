<?php
/*Este script es el endpoint controlador (guardar-cuenta.php) que procesa la actualización de datos personales del usuario. Valida que exista una sesión activa, recibe los campos enviados por POST, verifica que ningún dato esté vacío y valida el formato del correo electrónico (FILTER_VALIDATE_EMAIL). Si encuentra errores de validación, responde inmediatamente con un JSON estructurado ('ok' => false). Si los datos son válidos, escapa las variables con mysqli_real_escape_string y ejecuta un UPDATE en la tabla usuarios retornando una respuesta JSON de éxito o fallo.*/

// Inicia o reanuda la sesión del usuario
session_start();

// Establece la cabecera HTTP para indicar que la respuesta será devuelta en formato JSON
header('Content-Type: application/json');

// Incluye el archivo de conexión a la base de datos de manera única
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Verifica si existe la sesión activa del usuario; de lo contrario, retorna un error en JSON
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'ok' => false,
        'errores' => ['Sesión no válida. Inicie sesión nuevamente.']
    ]);
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];

// Define el listado de campos obligatorios requeridos desde el formulario
$campos = ['nombre', 'apellido', 'email', 'localidad', 'provincia', 'pais'];
$datos = [];
$errores = [];

// Recorre cada campo para validar que no llegue vacío y remueve espacios innecesarios
foreach ($campos as $campo) {
    $valor = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    if ($valor === '') {
        $errores[] = "El campo $campo es obligatorio";
    }
    $datos[$campo] = $valor;
}

// Valida que la sintaxis de la dirección de correo sea correcta
if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no es válido';
}

// Si se acumularon errores de validación, interrumpe el script y devuelve la lista de fallos
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
