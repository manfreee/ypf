<?php
header('Content-Type: application/json');
include_once('conexion.php'); // conexión MySQLi

// Leer el JSON recibido
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data || !isset($data["id_modulo"])) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido o incompleto", "recibido" => $json]);
    exit;
}

if (!isset($conexion) || !$conexion) {
    echo json_encode(["error" => "Conexión a la base de datos no disponible"]);
    exit;
}

// Extraer datos
$id_modulo   = mysqli_real_escape_string($conexion, $data["id_modulo"]);
$tension     = floatval($data["tension"] ?? 0);
$corriente   = floatval($data["corriente"] ?? 0);
$potencia    = floatval($data["potencia"] ?? 0);
$energia     = floatval($data["energia"] ?? 0);
$angulo      = floatval($data["angulo"] ?? 0);
$intensidad  = floatval($data["intensidad"] ?? 0);

// 1️⃣ Insertar nuevo dato
$sql_insert = "
INSERT INTO datos (id_modulo, tension, corriente, potencia, energia, angulo, intensidad)
VALUES ('$id_modulo', '$tension', '$corriente', '$potencia', '$energia', '$angulo', '$intensidad')
";
if (!mysqli_query($conexion, $sql_insert)) {
    echo json_encode(["error" => "Error al insertar en datos: " . mysqli_error($conexion)]);
    exit;
}

// 2️⃣ Contar registros actuales
$sql_count = "SELECT COUNT(*) AS cantidad FROM datos WHERE id_modulo = '$id_modulo'";
$result_count = mysqli_query($conexion, $sql_count);
$cantidad = intval(mysqli_fetch_assoc($result_count)["cantidad"]);

// Límite de registros diarios
$limite_prueba = 8640; // usar 8640 en producción

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
    WHERE id_modulo = '$id_modulo'
    ";
    $result_prom = mysqli_query($conexion, $sql_prom);
    $r = mysqli_fetch_assoc($result_prom);

    // 4️⃣ Insertar en promedio_diario (sin columna fecha)
    $sql_insert_prom = "
    INSERT INTO promedio_diario
    (id_modulo, tension, corriente, potencia, energia, angulo, intensidad)
    VALUES
    ('$id_modulo', '{$r['tension_prom']}', '{$r['corriente_prom']}', '{$r['potencia_prom']}', '{$r['energia_prom']}', '{$r['angulo_prom']}', '{$r['intensidad_prom']}')
    ";
    if (!mysqli_query($conexion, $sql_insert_prom)) {
        echo json_encode(["error" => "Error al guardar promedio diario: " . mysqli_error($conexion)]);
        exit;
    }

    // Limpiar tabla datos
    mysqli_query($conexion, "DELETE FROM datos WHERE id_modulo = '$id_modulo'");

    // 5️⃣ Contar días registrados
    $sql_count_d = "SELECT COUNT(*) AS dias FROM promedio_diario WHERE id_modulo = '$id_modulo'";
    $dias = intval(mysqli_fetch_assoc(mysqli_query($conexion, $sql_count_d))["dias"]);

    // 6️⃣ Si hay 7 días → crear promedio semanal
    if ($dias >= 7) {
        $sql_prom_sem = "
        SELECT 
            AVG(tension) AS tension_prom,
            AVG(corriente) AS corriente_prom,
            AVG(potencia) AS potencia_prom,
            AVG(energia) AS energia_prom,
            AVG(angulo) AS angulo_prom,
            AVG(intensidad) AS intensidad_prom
        FROM promedio_diario
        WHERE id_modulo = '$id_modulo'
        ";
        $result_sem = mysqli_query($conexion, $sql_prom_sem);
        $s = mysqli_fetch_assoc($result_sem);

        $sql_insert_sem = "
        INSERT INTO promedio_semanal
        (id_modulo, tension, corriente, potencia, energia, angulo, intensidad)
        VALUES
        ('$id_modulo', '{$s['tension_prom']}', '{$s['corriente_prom']}', '{$s['potencia_prom']}', '{$s['energia_prom']}', '{$s['angulo_prom']}', '{$s['intensidad_prom']}')
        ";
        mysqli_query($conexion, $sql_insert_sem);

        // Limpiar promedios diarios
        mysqli_query($conexion, "DELETE FROM promedio_diario WHERE id_modulo = '$id_modulo'");

        // 7️⃣ Contar semanas registradas
        $sql_count_s = "SELECT COUNT(*) AS semanas FROM promedio_semanal WHERE id_modulo = '$id_modulo'";
        $semanas = intval(mysqli_fetch_assoc(mysqli_query($conexion, $sql_count_s))["semanas"]);

        // Calcular cuántas semanas tiene el mes actual
        $anio = date("Y");
        $mes = date("m");
        $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $semanas_mes = ceil($dias_mes / 7);

        // 8️⃣ Si completó el mes → crear promedio mensual
        if ($semanas >= $semanas_mes) {
            $sql_prom_mes = "
            SELECT 
                AVG(tension) AS tension_prom,
                AVG(corriente) AS corriente_prom,
                AVG(potencia) AS potencia_prom,
                AVG(energia) AS energia_prom,
                AVG(angulo) AS angulo_prom,
                AVG(intensidad) AS intensidad_prom
            FROM promedio_semanal
            WHERE id_modulo = '$id_modulo'
            ";
            $result_mes = mysqli_query($conexion, $sql_prom_mes);
            $m = mysqli_fetch_assoc($result_mes);

            $sql_insert_mes = "
            INSERT INTO promedio_mensual
            (id_modulo, tension, corriente, potencia, energia, angulo, intensidad)
            VALUES
            ('$id_modulo', '{$m['tension_prom']}', '{$m['corriente_prom']}', '{$m['potencia_prom']}', '{$m['energia_prom']}', '{$m['angulo_prom']}', '{$m['intensidad_prom']}')
            ";
            mysqli_query($conexion, $sql_insert_mes);

            // Limpiar promedios semanales
            mysqli_query($conexion, "DELETE FROM promedio_semanal WHERE id_modulo = '$id_modulo'");
        }
    }

    // 9️⃣ Reportar estado
    $count_d = intval(mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) AS c FROM promedio_diario WHERE id_modulo='$id_modulo'"))["c"]);
    $count_s = intval(mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) AS c FROM promedio_semanal WHERE id_modulo='$id_modulo'"))["c"]);
    $count_m = intval(mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) AS c FROM promedio_mensual WHERE id_modulo='$id_modulo'"))["c"]);

    echo json_encode([
        "status" => "OK",
        "mensaje" => "Promedio diario procesado",
        "registros" => [
            "datos" => 0,
            "diarios" => $count_d,
            "semanales" => $count_s,
            "mensuales" => $count_m
        ]
    ]);

} else {
    echo json_encode([
        "status" => "OK",
        "mensaje" => "Dato insertado correctamente",
        "registros_actuales" => $cantidad
    ]);
}

mysqli_close($conexion);
?>
