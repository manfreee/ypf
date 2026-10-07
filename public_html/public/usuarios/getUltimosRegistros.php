<?php
/*Este código funciona como un endpoint de una API REST que devuelve datos en formato JSON. Se encarga de recibir mediante parámetros GET el identificador de un módulo (id_modulo) y un límite opcional de registros, consulta las mediciones de energía en la base de datos ordenándolas por fecha descendente, procesa y convierte el valor de energía de kilovatios-hora (kWh) a vatios-hora (Wh), e invierte el orden del array para devolver la serie temporal cronológicamente lista para ser graficada en el frontend.*/

// Incluye el archivo de conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Sanitización rápida convirtiendo los parámetros URL recibidos por GET a números enteros
$id_modulo = intval($_GET['id_modulo'] ?? 0);
$limite = intval($_GET['limite'] ?? 20);

// Valida que el ID de módulo proporcionado sea un número mayor a cero
if($id_modulo > 0){
    // Consulta a la BD para obtener la fecha/hora y la energía del módulo especificado, limitado al número solicitado
    $res = mysqli_query($conexion, "
        SELECT fecha_hora, energia
        FROM datos
        WHERE id_modulo=$id_modulo
        ORDER BY fecha_hora DESC
        LIMIT $limite
    ");

    $datos = [];
    // Comprueba si la consulta se ejecutó correctamente y trajo filas
    if($res && mysqli_num_rows($res) > 0){
        // Recorre cada fila devuelta por la base de datos
        while($fila = mysqli_fetch_assoc($res)){
            $datos[] = [
                "fecha" => $fila['fecha_hora'], // Guarda la fecha y hora original
                "hora"  => date("H:i:s", strtotime($fila['fecha_hora'])), // Extrae solo el formato de hora HH:MM:SS
                "valor" => floatval($fila['energia'])*1000 // Convierte la lectura de energía a Wh (Vatios-hora)
            ];
        }
    }
    // Invierte el arreglo para que los datos queden ordenados cronológicamente (de más antiguo a más reciente)
    $datos = array_reverse($datos);
    
    // Imprime la respuesta codificada en JSON y finaliza la ejecución del script
    echo json_encode(["datos"=>$datos]);
    exit;
}

// Si no se proporcionó un id_modulo válido, retorna una estructura JSON vacía
echo json_encode(["datos"=>[]]);