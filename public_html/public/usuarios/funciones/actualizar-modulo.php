<?php
/*Actúa como un endpoint de backend (procesador AJAX) exclusivo para actualizar el nombre de un módulo existente en la base de datos. Se encarga de iniciar la sesión, validar que el usuario esté autenticado, verificar que la petición sea de tipo POST y sanitizar/validar los datos recibidos (id_modulo y nombre_modulo). Antes de ejecutar la actualización mediante una consulta preparada (UPDATE), comprueba la propiedad del módulo a través de una función auxiliar para garantizar la seguridad. Al finalizar el proceso (exitoso o con error silenciado en logs), termina silenciosamente la ejecución sin redirección ni salidas de texto, ideal para ser consumido por un script JS de fondo.*/

// Iniciar o reanudar la sesión del usuario
session_start();

// Incluir archivos necesarios: conexión a la BD y funciones específicas para módulos
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/modulo-funciones.php';

// Verificar si el usuario está autenticado; de lo contrario, cancela la ejecución
if (!isset($_SESSION['id_usuario'])) {
    exit();
}

// Verificar que la solicitud entrante utilice el método HTTP POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}

// Obtener, sanitizar y validar los datos enviados desde el formulario o JS
$id_modulo = filter_input(INPUT_POST, 'id_modulo', FILTER_VALIDATE_INT);
$nombre_modulo = isset($_POST['nombre_modulo']) ? trim($_POST['nombre_modulo']) : '';
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';

// Validar que los datos recibidos sean correctos y requeridos
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
    // Preparar la consulta SQL con Sentencias Preparadas para actualizar el nombre del módulo
    $query = "UPDATE modulos SET nombre = ? WHERE id_modulo = ? AND id_usuario = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta');
    }
    
    // Vincular los parámetros e interpretar los tipos (s = string, i = integer)
    $stmt->bind_param("sii", $nombre_modulo, $id_modulo, $usuario_id);
    $stmt->execute();
    $stmt->close();
    
} catch (Exception $e) {
    // En caso de fallo, registra el error internamente en los logs del servidor sin exponer datos al cliente
    error_log('Error en actualizar-modulo.php: ' . $e->getMessage());
}

// No redirigir, solo terminar la ejecución
exit();