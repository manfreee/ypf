<?php
/**
 * Obtiene el número de serie de un módulo
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $id_modulo ID del módulo
 * @param int $usuario_id ID del usuario
 * @return string|null Número de serie del módulo o null si no se encuentra
 */
function obtenerNumeroSerieModulo($conexion, $id_modulo, $usuario_id) {
    $query = "SELECT numero_serie FROM modulos WHERE id_modulo = ? AND id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $id_modulo, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        return $row['numero_serie'];
    }
    
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
    $query = "SELECT id_modulo FROM modulos WHERE id_modulo = ? AND id_usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $id_modulo, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 1;
}
?>
