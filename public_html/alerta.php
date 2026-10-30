<?php
/*
Ejemplo de llamada desde Arduino ESP32 / ESP8266:

if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;

    String url = "https://necotracking.com.ar/alerta.php";

    http.begin(client, url);
    http.addHeader("Content-Type", "application/json");

    String jsonData = "{";
    jsonData += "\"id_modulo\": 5,";
    jsonData += "\"estado_anterior\": \"OK\",";
    jsonData += "\"estado_actual\": \"Falla de voltaje\",";
    jsonData += "\"ubicacion\": \"Campo 3 - Norte\"";
    jsonData += "}";

    int httpResponseCode = http.POST(jsonData);
    String response = http.getString();
    http.end();
}
*/

// alerta.php — Recibe JSON desde Arduino, actualiza estado, busca datos y envía mail

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Obtener JSON desde Arduino
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

// Validar parámetros
if (!$data || 
    !isset($data['id_modulo']) || 
    !isset($data['estado_anterior']) || 
    !isset($data['estado_actual'])) 
{
    echo json_encode(["status" => "error", "message" => "Parámetros faltantes"]);
    exit;
}

$id_modulo       = intval($data['id_modulo']);
$estado_anterior = trim($data['estado_anterior']);
$estado_actual   = trim($data['estado_actual']);
$ubicacion       = isset($data['ubicacion']) ? trim($data['ubicacion']) : "No informada";
$fecha_hora      = date("Y-m-d H:i:s");

// Incluir conexión con ruta absoluta
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';

// 1. Buscar módulo y usuario asociado con Prepared Statements
$sql_mod = "SELECT m.id_usuario, m.nombre AS modulo_nombre, u.nombre AS nombre_usuario, u.email AS email_usuario 
            FROM modulos m 
            INNER JOIN usuarios u ON m.id_usuario = u.id_usuario 
            WHERE m.id_modulo = ?";

$stmt = $conexion->prepare($sql_mod);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error en la consulta de BD"]);
    exit;
}

$stmt->bind_param("i", $id_modulo);
$stmt->execute();
$res_mod = $stmt->get_result();

if ($res_mod->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Módulo o usuario no encontrado"]);
    $stmt->close();
    exit;
}

$datos           = $res_mod->fetch_assoc();
$id_usuario      = $datos['id_usuario'];
$modulo_nombre   = $datos['modulo_nombre'];
$nombre_usuario  = $datos['nombre_usuario'];
$email_usuario   = $datos['email_usuario'];
$stmt->close();

// 2. Actualizar el estado del módulo en la tabla 'modulos'
$sql_update = "UPDATE modulos SET estado = ? WHERE id_modulo = ?";
$stmt_up = $conexion->prepare($sql_update);
if ($stmt_up) {
    // Truncar si supera el tamaño de columna (varchar 30)
    $estado_db = substr($estado_actual, 0, 30);
    $stmt_up->bind_param("si", $estado_db, $id_modulo);
    $stmt_up->execute();
    $stmt_up->close();
}

// 3. Preparar correo de notificación
$url_panel = "https://necotracking.com.ar/public/usuarios/configuracion/panel.php?id_modulo=" . $id_modulo;

$asunto = "🔔 Alerta de estado en tu módulo solar - Necotracking";

$mensaje = "Hola, {$nombre_usuario}:\n\n"
         . "Se detectó un cambio de estado en uno de los módulos solares asociados a tu cuenta.\n\n"
         . "📘 Detalles del evento\n"
         . "-----------------------------------\n"
         . "Módulo: {$modulo_nombre} (ID {$id_modulo})\n"
         . "Estado anterior: {$estado_anterior}\n"
         . "Nuevo estado: {$estado_actual}\n"
         . "Fecha y hora: {$fecha_hora}\n"
         . "Ubicación: {$ubicacion}\n\n"
         . "⚠️ Información importante\n"
         . "El sistema registró un estado que puede requerir atención.\n"
         . "Te recomendamos ingresar al panel para ver el estado completo del módulo:\n"
         . "{$url_panel}\n\n"
         . "Mensaje generado automáticamente, por favor no responda este correo.";

// Encabezados del correo
$headers  = "From: Necotracking <no-reply@necotracking.com.ar>\r\n";
$headers .= "Reply-To: no-reply@necotracking.com.ar\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 4. Enviar correo
if (mail($email_usuario, $asunto, $mensaje, $headers)) {
    echo json_encode(["status" => "success", "message" => "Alerta procesada y correo enviado"]);
} else {
    echo json_encode(["status" => "warning", "message" => "Estado actualizado, pero falló el envío del correo"]);
}