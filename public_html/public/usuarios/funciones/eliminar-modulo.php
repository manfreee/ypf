<?php
// Iniciar la sesión
session_start();

// Incluir archivos necesarios
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/modulo-funciones.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit();
}

// Verificar si se proporcionó un ID de módulo
if (!isset($_GET['id_modulo'])) {
    header('Location: /public/usuarios/configuracion/panel.php');
    exit();
}

// Obtener y validar el ID del módulo
$id_modulo = filter_input(INPUT_GET, 'id_modulo', FILTER_VALIDATE_INT);

if (!$id_modulo) {
    header('Location: /public/usuarios/configuracion/panel.php?error=id_invalido');
    exit();
}

// Verificar que el módulo pertenece al usuario
$usuario_id = $_SESSION['id_usuario'];
if (!verificarPropiedadModulo($conexion, $id_modulo, $usuario_id)) {
    header('Location: /public/usuarios/configuracion/panel.php?error=acceso_denegado');
    exit();
}

try {
    // Iniciar transacción para asegurar la integridad de los datos
    $conexion->begin_transaction();

    // 1. Eliminar datos asociados al módulo en la tabla datos
    $query_delete_datos = "DELETE FROM datos WHERE id_modulo = ?";
    $stmt_datos = $conexion->prepare($query_delete_datos);
    $stmt_datos->bind_param("i", $id_modulo);
    $stmt_datos->execute();
    $stmt_datos->close();

    // 2. Eliminar promedios diarios asociados
    $query_delete_promd = "DELETE FROM promedio_diario WHERE id_modulo = ?";
    $stmt_promd = $conexion->prepare($query_delete_promd);
    $stmt_promd->bind_param("i", $id_modulo);
    $stmt_promd->execute();
    $stmt_promd->close();

    // 3. Eliminar promedios semanales asociados
    $query_delete_proms = "DELETE FROM promedio_semanal WHERE id_modulo = ?";
    $stmt_proms = $conexion->prepare($query_delete_proms);
    $stmt_proms->bind_param("i", $id_modulo);
    $stmt_proms->execute();
    $stmt_proms->close();

    // 4. Desvincular productos asociados (o eliminar si es el comportamiento deseado)
    $query_update_productos = "DELETE FROM productos WHERE id_modulo = ?";
    $stmt_productos = $conexion->prepare($query_update_productos);
    $stmt_productos->bind_param("i", $id_modulo);
    $stmt_productos->execute();
    $stmt_productos->close();

    // 5. Finalmente, eliminar el módulo
    $query_delete_modulo = "DELETE FROM modulos WHERE id_modulo = ? AND id_usuario = ?";
    $stmt_modulo = $conexion->prepare($query_delete_modulo);
    $stmt_modulo->bind_param("ii", $id_modulo, $usuario_id);
    $stmt_modulo->execute();
    
    // Verificar si se eliminó correctamente
    if ($stmt_modulo->affected_rows > 0) {
        $conexion->commit();
        header('Location: /public/usuarios/configuracion/panel.php?exito=modulo_eliminado');
    } else {
        throw new Exception('No se pudo eliminar el módulo');
    }
    
    $stmt_modulo->close();
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    if (isset($conexion)) {
        $conexion->rollback();
    }
    
    // Registrar el error
    error_log('Error en eliminar-modulo.php: ' . $e->getMessage());
    
    // Redirigir con mensaje de error
    header('Location: /public/usuarios/configuracion/panel.php?error=error_eliminar');
} finally {
    // Cerrar la conexión
    if (isset($conexion)) {
        $conexion->close();
    }
}

exit();