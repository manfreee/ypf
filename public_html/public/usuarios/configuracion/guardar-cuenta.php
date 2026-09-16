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

// Guarda el identificador del usuario extraído de la sesión
$id_usuario = $_SESSION['id_usuario'];

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

// Escapa caracteres especiales en las cadenas para mitigar riesgos de inyección SQL
$nombre = mysqli_real_escape_string($conexion, $datos['nombre']);
$apellido = mysqli_real_escape_string($conexion, $datos['apellido']);
$email = mysqli_real_escape_string($conexion, $datos['email']);
$localidad = mysqli_real_escape_string($conexion, $datos['localidad']);
$provincia = mysqli_real_escape_string($conexion, $datos['provincia']);
$pais = mysqli_real_escape_string($conexion, $datos['pais']);

// Prepara la consulta de actualización para modificar el perfil del usuario activo
$query = "UPDATE usuarios SET 
            nombre = '$nombre',
            apellido = '$apellido',
            email = '$email',
            localidad = '$localidad',
            provincia = '$provincia',
            pais = '$pais'
          WHERE id_usuario = '$id_usuario' 
          LIMIT 1";

// Ejecuta la consulta SQL y devuelve la respuesta en formato JSON según el resultado
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