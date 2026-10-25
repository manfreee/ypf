<?php
header('Content-Type: application/json');
// include_once('conexion.php'); // Conexión a BD comentada para pruebas

// Leer el JSON que envía el Arduino
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data || !isset($data["id_modulo"])) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido o incompleto", "recibido" => $json]);
    exit;
}

/* 
// Comentado para omitir la validación de BD en la prueba
if (!isset($conexion) || !$conexion) {
    echo json_encode(["error" => "Conexión a la base de datos no disponible"]);
    exit;
}
*/

// Extraer los datos enviados
$datos_recibidos = [
    "id_modulo"  => $data["id_modulo"] ?? null,
    "tension"    => floatval($data["tension"] ?? $data["voltaje"] ?? 0),
    "corriente"  => floatval($data["corriente"] ?? 0),
    "potencia"   => floatval($data["potencia"] ?? $data["energia"] ?? 0),
    "energia"    => floatval($data["energia"] ?? 0),
    "angulo"     => floatval($data["angulo"] ?? 0),
    "intensidad" => floatval($data["intensidad"] ?? $data["lux"] ?? 0)
];


/* ==========================================================================
   OPERACIONES DE BASE DE DATOS COMENTADAS (NO INSERTA NADA)
   ==========================================================================

// 1️⃣ Insertar nuevo dato
$sql_insert = "
INSERT INTO datos (id_modulo, tension, corriente, potencia, energia, angulo, intensidad)
VALUES ('{$datos_recibidos['id_modulo']}', '{$datos_recibidos['tension']}', '{$datos_recibidos['corriente']}', '{$datos_recibidos['potencia']}', '{$datos_recibidos['energia']}', '{$datos_recibidos['angulo']}', '{$datos_recibidos['intensidad']}')
";
if (!mysqli_query($conexion, $sql_insert)) {
    echo json_encode(["error" => "Error al insertar en datos: " . mysqli_error($conexion)]);
    exit;
}

// 2️⃣ Contar registros actuales
$sql_count = "SELECT COUNT(*) AS cantidad FROM datos WHERE id_modulo = '{$datos_recibidos['id_modulo']}'";
$result_count = mysqli_query($conexion, $sql_count);
$cantidad = intval(mysqli_fetch_assoc($result_count)["cantidad"]);

// Límite de registros diarios
$limite_prueba = 8640;

if ($cantidad >= $limite_prueba) {
    // 3️⃣ Calcular promedio diario
    $sql_prom = "
    SELECT 
        AVG(tension) AS tension_prom,
        AVG(corriente) AS corriente_prom,
        AVG(potencia) AS potencia_prom,
        AVG(energia) AS energia_prom,
        AVG(angulo) AS angulo_prom,
        AVG(intensidad) AS intensidad_prom
    FROM datos
    WHERE id_modulo = '{$datos_recibidos['id_modulo']}'
    ";
    $result_prom = mysqli_query($conexion, $sql_prom);
    $r = mysqli_fetch_assoc($result_prom);

    // 4️⃣ Insertar en promedio_diario
    $sql_insert_prom = "
    INSERT INTO promedio_diario
    (id_modulo, tension, corriente, potencia, energia, angulo, intensidad)
    VALUES
    ('{$datos_recibidos['id_modulo']}', '{$r['tension_prom']}', '{$r['corriente_prom']}', '{$r['potencia_prom']}', '{$r['energia_prom']}', '{$r['angulo_prom']}', '{$r['intensidad_prom']}')
    ";
    mysqli_query($conexion, $sql_insert_prom);

    // Limpiar tabla datos
    mysqli_query($conexion, "DELETE FROM datos WHERE id_modulo = '{$datos_recibidos['id_modulo']}'");
}

mysqli_close($conexion);
========================================================================== */

// Devolver la respuesta con OK y mostrar los datos recibidos por la consola / salida HTTP
echo json_encode([
    "status"          => "OK",
    "mensaje"         => "Datos recibidos correctamente (sin guardar en BD ni archivos)",
    "datos_recibidos" => $datos_recibidos
], JSON_PRETTY_PRINT);
?>