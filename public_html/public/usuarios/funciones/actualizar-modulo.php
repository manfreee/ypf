<?php
// Iniciar la sesión
session_start();

// Incluir archivos necesarios
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/modulo-funciones.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    exit();
}

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}

// Obtener y validar los datos del formulario
$id_modulo = filter_input(INPUT_POST, 'id_modulo', FILTER_VALIDATE_INT);
$nombre_modulo = isset($_POST['nombre_modulo']) ? trim($_POST['nombre_modulo']) : '';
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';

// Validar los datos
if (!$id_modulo || empty($nombre_modulo) || $tipo !== 'modulo') {
    exit();
}

// Verificar que el módulo pertenece al usuario
$usuario_id = (int)$_SESSION['id_usuario'];
if (function_exists('verificarPropiedadModulo')) {
    if (!verificarPropiedadModulo($conexion, $id_modulo, $usuario_id)) {
        exit();
    }
}

try {
    // Preparar la consulta para actualizar el nombre del módulo
    $query = "UPDATE modulos SET nombre = ? WHERE id_modulo = ? AND id_usuario = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta');
    }
    
    $stmt->bind_param("sii", $nombre_modulo, $id_modulo, $usuario_id);
    $stmt->execute();
    $stmt->close();
    
} catch (Exception $e) {
    // Solo registrar el error, no mostrar nada al usuario
    error_log('Error en actualizar-modulo.php: ' . $e->getMessage());
}

// No redirigir, solo terminar la ejecución
exit();