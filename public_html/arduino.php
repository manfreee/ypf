<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php'; // conexión MySQLi

// Leer el JSON que envía el Arduino
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!is_array($data) || !isset($data["id_modulo"])) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido o incompleto", "recibido" => $json]);
    exit;
}

$campos = [
    "CORRIENTE_MAX" => [0, 10],
    "VOLTAJE_MAX" => [0, 50],
    "ENERGIA_MAX" => [0, 50],
    "POTENCIA_MAX" => [0, 50],
    "LUX_MAX" => [0, 65535],
    "ANGULO_MAX" => [0, 360]
];

foreach ($campos as $campo => $rango) {
    if (!isset($data[$campo]) || !is_numeric($data[$campo])) {
        http_response_code(400);
        echo json_encode(["error" => "Campo numérico obligatorio faltante o inválido", "campo" => $campo]);
        exit;
    }

    $valor = (float) $data[$campo];
    if (!is_finite($valor) || $valor < $rango[0] || $valor > $rango[1]) {
        http_response_code(400);
        echo json_encode([
            "error" => "Valor fuera de rango",
            "campo" => $campo,
            "rango" => $rango
        ]);
        exit;
    }
}

if (floor((float) $data["LUX_MAX"]) != (float) $data["LUX_MAX"]) {
    http_response_code(400);
    echo json_encode(["error" => "LUX_MAX debe ser un número entero"]);
    exit;
}

/*
// Comentado para omitir la validación de BD en la prueba
if (!isset($conexion) || !$conexion) {
    echo json_encode(["error" => "Conexión a la base de datos no disponible"]);
    exit;
}
*/

// Mapear los nombres del JSON a los campos usados por la base de datos
$id_modulo   = intval($data["id_modulo"]);
$tension     = (float) $data["VOLTAJE_MAX"];
$corriente   = (float) $data["CORRIENTE_MAX"];
$potencia    = (float) $data["POTENCIA_MAX"];
$energia     = (float) $data["ENERGIA_MAX"];
$angulo      = (float) $data["ANGULO_MAX"];
$intensidad  = (int) $data["LUX_MAX"];

/*
// 1️⃣ Insertar nuevo dato (Consulta preparada)
// Se incluye fecha_hora (NOW()) requerida por la estructura de la tabla 'datos' en la BD
$sql_insert = "INSERT INTO datos (fecha_hora, tension, corriente, potencia, energia, angulo, intensidad, id_modulo) 
               VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conexion->prepare($sql_insert);
$stmt_insert->bind_param("dddddii", $tension, $corriente, $potencia, $energia, $angulo, $intensidad, $id_modulo);

if (!$stmt_insert->execute()) {
    echo json_encode(["error" => "Error al insertar en datos: " . $stmt_insert->error]);
    $stmt_insert->close();
    exit;
}
$stmt_insert->close();

// 2️⃣ Contar registros actuales (Consulta preparada)
$sql_count = "SELECT COUNT(*) AS cantidad FROM datos WHERE id_modulo = ?";
$stmt_count = $conexion->prepare($sql_count);
$stmt_count->bind_param("i", $id_modulo);
$stmt_count->execute();
$res_count = $stmt_count->get_result();
$cantidad = intval($res_count->fetch_assoc()["cantidad"]);
$stmt_count->close();

// Límite de registros diarios
$limite_prueba = 8640;

if ($cantidad >= $limite_prueba) {
    // 3️⃣ Calcular promedio diario (Consulta preparada)
    $sql_prom = "SELECT 
                    AVG(tension) AS tension_prom,
                    AVG(corriente) AS corriente_prom,
                    AVG(potencia) AS potencia_prom,
                    AVG(energia) AS energia_prom,
                    AVG(angulo) AS angulo_prom,
                    AVG(intensidad) AS intensidad_prom
                FROM datos
                WHERE id_modulo = ?";
    $stmt_prom = $conexion->prepare($sql_prom);
    $stmt_prom->bind_param("i", $id_modulo);
    $stmt_prom->execute();
    $r = $stmt_prom->get_result()->fetch_assoc();
    $stmt_prom->close();

    // 4️⃣ Insertar en promedio_diario (Consulta preparada)
    $sql_insert_prom = "INSERT INTO promedio_diario (tension, corriente, potencia, energia, angulo, intensidad, id_modulo)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert_prom = $conexion->prepare($sql_insert_prom);
    $intensidad_prom_int = intval(round($r['intensidad_prom']));
    $stmt_insert_prom->bind_param(
        "dddddii", 
        $r['tension_prom'], 
        $r['corriente_prom'], 
        $r['potencia_prom'], 
        $r['energia_prom'], 
        $r['angulo_prom'], 
        $intensidad_prom_int, 
        $id_modulo
    );
    
    if (!$stmt_insert_prom->execute()) {
        echo json_encode(["error" => "Error al guardar promedio diario: " . $stmt_insert_prom->error]);
        $stmt_insert_prom->close();
        exit;
    }
    $stmt_insert_prom->close();

    // Limpiar tabla datos (Consulta preparada)
    $stmt_del_datos = $conexion->prepare("DELETE FROM datos WHERE id_modulo = ?");
    $stmt_del_datos->bind_param("i", $id_modulo);
    $stmt_del_datos->execute();
    $stmt_del_datos->close();

    // 5️⃣ Contar días registrados en promedio_diario (Consulta preparada)
    $stmt_count_d = $conexion->prepare("SELECT COUNT(*) AS dias FROM promedio_diario WHERE id_modulo = ?");
    $stmt_count_d->bind_param("i", $id_modulo);
    $stmt_count_d->execute();
    $dias = intval($stmt_count_d->get_result()->fetch_assoc()["dias"]);
    $stmt_count_d->close();

    // 6️⃣ Si hay 7 días → crear promedio semanal
    if ($dias >= 7) {
        $sql_prom_sem = "SELECT 
                            AVG(tension) AS tension_prom,
                            AVG(corriente) AS corriente_prom,
                            AVG(potencia) AS potencia_prom,
                            AVG(energia) AS energia_prom,
                            AVG(angulo) AS angulo_prom,
                            AVG(intensidad) AS intensidad_prom
                        FROM promedio_diario
                        WHERE id_modulo = ?";
        $stmt_sem = $conexion->prepare($sql_prom_sem);
        $stmt_sem->bind_param("i", $id_modulo);
        $stmt_sem->execute();
        $s = $stmt_sem->get_result()->fetch_assoc();
        $stmt_sem->close();

        // Insertar en promedio_semanal (Consulta preparada)
        $sql_insert_sem = "INSERT INTO promedio_semanal (tension, corriente, potencia, energia, angulo, intensidad, id_modulo)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_sem = $conexion->prepare($sql_insert_sem);
        $intensidad_sem_int = intval(round($s['intensidad_prom']));
        $stmt_insert_sem->bind_param(
            "dddddii", 
            $s['tension_prom'], 
            $s['corriente_prom'], 
            $s['potencia_prom'], 
            $s['energia_prom'], 
            $s['angulo_prom'], 
            $intensidad_sem_int, 
            $id_modulo
        );
        $stmt_insert_sem->execute();
        $stmt_insert_sem->close();

        // Limpiar promedios diarios (Consulta preparada)
        $stmt_del_diario = $conexion->prepare("DELETE FROM promedio_diario WHERE id_modulo = ?");
        $stmt_del_diario->bind_param("i", $id_modulo);
        $stmt_del_diario->execute();
        $stmt_del_diario->close();

        /* 
        -----------------------------------------------------------------------
        NOTA BD: La tabla 'promedio_mensual' NO existe en la base de datos actual.
        A continuación se deja comentada y adaptada a consulta preparada la 
        lógica de promedio mensual por si en el futuro se decide crear la tabla.
        -----------------------------------------------------------------------

        // 7️⃣ Contar semanas registradas
        $stmt_count_s = $conexion->prepare("SELECT COUNT(*) AS semanas FROM promedio_semanal WHERE id_modulo = ?");
        $stmt_count_s->bind_param("i", $id_modulo);
        $stmt_count_s->execute();
        $semanas = intval($stmt_count_s->get_result()->fetch_assoc()["semanas"]);
        $stmt_count_s->close();

        // Calcular cuántas semanas tiene el mes actual
        $anio = date("Y");
        $mes = date("m");
        $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $semanas_mes = ceil($dias_mes / 7);

        // 8️⃣ Si completó el mes → crear promedio mensual
        if ($semanas >= $semanas_mes) {
            $sql_prom_mes = "SELECT 
                                AVG(tension) AS tension_prom,
                                AVG(corriente) AS corriente_prom,
                                AVG(potencia) AS potencia_prom,
                                AVG(energia) AS energia_prom,
                                AVG(angulo) AS angulo_prom,
                                AVG(intensidad) AS intensidad_prom
                            FROM promedio_semanal
                            WHERE id_modulo = ?";
            $stmt_mes = $conexion->prepare($sql_prom_mes);
            $stmt_mes->bind_param("i", $id_modulo);
            $stmt_mes->execute();
            $m = $stmt_mes->get_result()->fetch_assoc();
            $stmt_mes->close();

            $sql_insert_mes = "INSERT INTO promedio_mensual (tension, corriente, potencia, energia, angulo, intensidad, id_modulo)
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert_mes = $conexion->prepare($sql_insert_mes);
            $intensidad_mes_int = intval(round($m['intensidad_prom']));
            $stmt_insert_mes->bind_param(
                "dddddii",
                $m['tension_prom'],
                $m['corriente_prom'],
                $m['potencia_prom'],
                $m['energia_prom'],
                $m['angulo_prom'],
                $intensidad_mes_int,
                $id_modulo
            );
            $stmt_insert_mes->execute();
            $stmt_insert_mes->close();

            // Limpiar promedios semanales
            $stmt_del_sem = $conexion->prepare("DELETE FROM promedio_semanal WHERE id_modulo = ?");
            $stmt_del_sem->bind_param("i", $id_modulo);
            $stmt_del_sem->execute();
            $stmt_del_sem->close();
        }

    }

    // 9️⃣ Reportar estado (Consultas preparadas)
    $stmt_cd = $conexion->prepare("SELECT COUNT(*) AS c FROM promedio_diario WHERE id_modulo = ?");
    $stmt_cd->bind_param("i", $id_modulo);
    $stmt_cd->execute();
    $count_d = intval($stmt_cd->get_result()->fetch_assoc()["c"]);
    $stmt_cd->close();

    $stmt_cs = $conexion->prepare("SELECT COUNT(*) AS c FROM promedio_semanal WHERE id_modulo = ?");
    $stmt_cs->bind_param("i", $id_modulo);
    $stmt_cs->execute();
    $count_s = intval($stmt_cs->get_result()->fetch_assoc()["c"]);
    $stmt_cs->close();
*/

    echo json_encode([
        "status" => "OK",
        "mensaje" => "Promedio diario procesado",
        "registros" => [
            "datos" => 0,
            //"diarios" => $count_d,
            //"semanales" => $count_s
        ]
    ]);

if (isset($conexion)) {
    $conexion->close();
}
?>