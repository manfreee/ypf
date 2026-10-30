<?php
/*Este código funciona como un endpoint de una API REST que devuelve datos en formato JSON. Se encarga de recibir mediante parámetros GET el identificador de un módulo (id_modulo) y un límite opcional de registros, consulta las mediciones de energía en la base de datos ordenándolas por fecha descendente, procesa y convierte el valor de energía de kilovatios-hora (kWh) a vatios-hora (Wh), e invierte el orden del array para devolver la serie temporal cronológicamente lista para ser graficada en el frontend.*/

// Iniciar sesión para validar propiedad del módulo si aplica
session_start();

// Especificar encabezado JSON para respuestas API
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Sanitización de parámetros URL recibidos por GET
$id_modulo = filter_input(INPUT_GET, 'id_modulo', FILTER_VALIDATE_INT) ?? 0;
$limite = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?? 20;

// Garantizar valores por defecto coherentes
if ($limite <= 0) {
    $limite = 20;
}

// Valida que el ID de módulo proporcionado sea un número mayor a cero
if ($id_modulo > 0) {
    // Consulta preparada para obtener la fecha/hora y la energía del módulo especificado
    $query = "SELECT fecha_hora, energia 
              FROM datos 
              WHERE id_modulo = ? 
              ORDER BY fecha_hora DESC 
              LIMIT ?";

    $stmt = $conexion->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ii", $id_modulo, $limite);
        $stmt->execute();
        $res = $stmt->get_result();

        $datos = [];
        // Recorre cada fila devuelta por la base de datos
        while ($fila = $res->fetch_assoc()) {
            $datos[] = [
                "fecha" => $fila['fecha_hora'], // Guarda la fecha y hora original
                "hora"  => date("H:i:s", strtotime($fila['fecha_hora'])), // Extrae solo el formato de hora HH:MM:SS
                "valor" => floatval($fila['energia']) * 1000 // Convierte la lectura de energía a Wh (Vatios-hora)
            ];
        }

        $stmt->close();

        // Invierte el arreglo para que los datos queden ordenados cronológicamente (de más antiguo a más reciente)
        $datos = array_reverse($datos);

        // Imprime la respuesta codificada en JSON y finaliza la ejecución del script
        echo json_encode(["datos" => $datos]);
        exit;
    }
}

// Si no se proporcionó un id_modulo válido o falla la consulta, retorna una estructura JSON vacía
echo json_encode(["datos" => []]);
exit;