<?php
/*Este código define dos funciones auxiliares de consulta y seguridad pertenecientes al módulo de gestión de usuarios (modulo-funciones.php). La primera función, obtenerNumeroSerieModulo(), realiza una consulta SQL para recuperar el número de serie único de un dispositivo validando previamente que pertenezca al usuario activo. La segunda, verificarPropiedadModulo(), sirve como capa de control de acceso (propiedad) verificando si un módulo en particular está asociado a un identificador de usuario específico, devolviendo un valor booleano (true o false). Ambas utilizan sentencias preparadas para prevenir inyecciones SQL.*/
/**
 * Obtiene el número de serie de un módulo
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $id_modulo ID del módulo
 * @param int $usuario_id ID del usuario
 * @return string|null Número de serie del módulo o null si no se encuentra
 */
function obtenerNumeroSerieModulo($conexion, $id_modulo, $usuario_id) {
    // Definir la consulta SELECT para obtener el número de serie filtrando por módulo y usuario
    $query = "SELECT numero_serie FROM modulos WHERE id_modulo = ? AND id_usuario = ?";
    
    // Preparar la sentencia SQL para prevenir inyección SQL
    $stmt = $conexion->prepare($query);
    
    // Vincular los parámetros enteros (i = integer, i = integer)
    $stmt->bind_param("ii", $id_modulo, $usuario_id);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener el conjunto de resultados devuelto por la base de datos
    $result = $stmt->get_result();
    
    // Comprobar si se encontró exactamente un registro coincidente
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        return $row['numero_serie']; // Devuelve la cadena con el número de serie
    }
    
    // Retornar null en caso de que no exista el registro o no pertenezca al usuario
    return null;
}

/**
 * Verifica si el módulo pertenece al usuario
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $id_modulo ID del módulo
 * @param int $usuario_id ID del usuario
 * @return bool True si el módulo pertenece al usuario, false en caso contrario
 */
function verificarPropiedadModulo($conexion, $id_modulo, $usuario_id) {
    // Consulta simple para verificar la existencia del módulo vinculado al ID del usuario
    $query = "SELECT id_modulo FROM modulos WHERE id_modulo = ? AND id_usuario = ?";
    
    // Preparar la consulta SQL
    $stmt = $conexion->prepare($query);
    
    // Vincular los dos IDs de tipo entero
    $stmt->bind_param("ii", $id_modulo, $usuario_id);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener los resultados
    $result = $stmt->get_result();
    
    // Retorna true si encontró exactamente 1 coincidencia, o false si no coincide o no existe
    return $result->num_rows === 1;
}
?>