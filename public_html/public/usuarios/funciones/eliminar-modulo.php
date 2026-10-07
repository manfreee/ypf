<?php
/*Este script actúa como el controlador de eliminación de un módulo. Este archivo maneja solicitudes mediante peticiones GET y realiza redirecciones explícitas en el navegador (header('Location: ...')). Se encarga de verificar que el usuario esté autenticado, validar que el id_modulo sea un entero válido e impedir eliminaciones no autorizadas corroborando que el módulo le pertenezca. Para evitar datos huérfanos o errores de clave foránea, abre una transacción SQL (begin_transaction) para eliminar primero los registros dependientes (sensores) y luego el módulo en sí; si todo sale bien aplica los cambios (commit), de lo contrario deshace todo (rollback) y registra el fallo en logs.*/

// Iniciar o recuperar la sesión del usuario
session_start();

// Incluir la conexión a la BD y las funciones auxiliares de verificación
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/modulo-funciones.php';

// Verificar si el usuario está autenticado; de lo contrario, redirigir al inicio
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit();
}

// Verificar si el parámetro GET 'id_modulo' viene presente en la URL
if (!isset($_GET['id_modulo'])) {
    header('Location: /public/usuarios/configuracion/panel.php');
    exit();
}

// Obtener y validar que el ID del módulo sea un número entero
$id_modulo = filter_input(INPUT_GET, 'id_modulo', FILTER_VALIDATE_INT);

if (!$id_modulo) {
    header('Location: /public/usuarios/configuracion/panel.php?error=id_invalido');
    exit();
}

// Comprobar mediante la función de seguridad que el módulo pertenece al usuario en sesión
$usuario_id = $_SESSION['id_usuario'];
if (!verificarPropiedadModulo($conexion, $id_modulo, $usuario_id)) {
    header('Location: /public/usuarios/configuracion/panel.php?error=acceso_denegado');
    exit();
}

try {
    // Iniciar una transacción SQL para asegurar la atomicidad e integridad de la eliminación
    $conexion->begin_transaction();

    // 1. Eliminar primero los registros dependientes (sensores asociados al módulo)
    $query_delete_sensores = "DELETE FROM sensores WHERE id_modulo = ?";
    $stmt_sensores = $conexion->prepare($query_delete_sensores);
    $stmt_sensores->bind_param("i", $id_modulo);
    $stmt_sensores->execute();
    $stmt_sensores->close();

    // 2. Eliminar el registro principal del módulo de la tabla 'modulos'
    $query_delete_modulo = "DELETE FROM modulos WHERE id_modulo = ? AND id_usuario = ?";
    $stmt_modulo = $conexion->prepare($query_delete_modulo);
    $stmt_modulo->bind_param("ii", $id_modulo, $usuario_id);
    $stmt_modulo->execute();
    
    // Validar si la consulta afectó al menos a una fila
    if ($stmt_modulo->affected_rows > 0) {
        // Confirmar y consolidar la transacción en la base de datos
        $conexion->commit();
        header('Location: /public/usuarios/configuracion/panel.php?exito=modulo_eliminado');
    } else {
        throw new Exception('No se pudo eliminar el módulo');
    }
    
    $stmt_modulo->close();
    
} catch (Exception $e) {
    // Si algo falla, revertir todos los cambios realizados en la transacción
    if (isset($conexion)) {
        $conexion->rollback();
    }
    
    // Registrar el detalle del error internamente en los logs del servidor
    error_log('Error en eliminar-modulo.php: ' . $e->getMessage());
    
    // Redirigir notificando el fallo
    header('Location: /public/usuarios/configuracion/panel.php?error=error_eliminar');
} finally {
    // Asegurar el cierre de la conexión a la base de datos
    if (isset($conexion)) {
        $conexion->close();
    }
}

// Finalizar la ejecución del script
exit();