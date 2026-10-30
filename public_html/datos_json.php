<?php
// Especificar encabezado JSON
header('Content-Type: application/json; charset=utf-8');

// Carga de conexión con ruta absoluta
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';

if (!$conexion) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

// Obtener y validar el id_modulo recibido por GET (opcional pero recomendado)
$id_modulo = filter_input(INPUT_GET, 'id_modulo', FILTER_VALIDATE_INT);

if ($id_modulo) {
    // Si se pasa un módulo específico, filtrar por él
    $sql = "SELECT fecha_hora, tension, corriente, potencia FROM datos WHERE id_modulo = ? ORDER BY id_dato DESC LIMIT 20";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_modulo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    // Si no se especifica módulo, consultar las últimas mediciones generales
    $sql = "SELECT fecha_hora, tension, corriente, potencia FROM datos ORDER BY id_dato DESC LIMIT 20";
    $resultado = mysqli_query($conexion, $sql);
}

$datos = [];

if ($resultado && mysqli_num_rows($resultado) > 0) {
    // Recorremos los resultados y convertimos los valores numéricos a su tipo correspondiente
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $datos[] = [
            "fecha_hora" => $fila['fecha_hora'],
            "tension"    => floatval($fila['tension']),
            "corriente"  => floatval($fila['corriente']),
            "potencia"   => floatval($fila['potencia'])
        ];
    }
}

// Invertimos el arreglo para que el registro más antiguo quede primero (orden cronológico)
echo json_encode(array_reverse($datos));
exit;
?>