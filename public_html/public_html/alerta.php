<?php
/*idea de que lo llamen desde arduino:
usando el id del modulo y ademas el estado y eso se manda con json y lo recibimos y creamos seria todo lo demas para buscar todos los datos necesario usariamos la bd usando modulo id para sacar el id usuario y con eso el email y nombre y demas datos necesarios para el mail

como se llamaria desde el arduinoo:
 if (WiFi.status() == WL_CONNECTED) {

    HTTPClient http;
    WiFiClient client;

    // URL de tu archivo alerta.php
    String url = "https://necotracking.com.ar/alerta.php";

    http.begin(client, url);
    http.addHeader("Content-Type", "application/json");

    // JSON a enviar (ejemplo de un módulo fallando)
    String jsonData = "{";
    jsonData += "\"id_modulo\": 5,";
    jsonData += "\"estado_anterior\": \"OK\",";
    jsonData += "\"estado_actual\": \"Falla de voltaje\",";
    jsonData += "\"ubicacion\": \"Campo 3 - Norte\"";   si se quiere guardar no es obligatorio
    jsonData += "}";

    Serial.println("Enviando JSON:");
    Serial.println(jsonData);

    int httpResponseCode = http.POST(jsonData);

    Serial.print("Respuesta: ");
    Serial.println(httpResponseCode);

    String response = http.getString();
    Serial.println("Respuesta del servidor:");
    Serial.println(response);

    http.end();
  }
*/
// alerta.php — Recibe JSON desde Arduino, busca módulo y envía mail

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

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

$id_modulo = intval($data['id_modulo']);
$estado_anterior = $data['estado_anterior'];
$estado_actual = $data['estado_actual'];
$ubicacion = $data['ubicacion'] ?? "No informada";
$fecha_hora = date("Y-m-d H:i:s");

include "conexion.php";

// Buscar módulo
$sql_mod = "SELECT id_usuario, nombre AS modulo_nombre 
            FROM modulos 
            WHERE id_modulo = $id_modulo";

$result_mod = mysqli_query($conexion, $sql_mod);

if (!$result_mod || mysqli_num_rows($result_mod) === 0) {
    echo json_encode(["status" => "error", "message" => "Módulo no encontrado"]);
    exit;
}

$modData = mysqli_fetch_assoc($result_mod);
$id_usuario     = $modData['id_usuario'];
$modulo_nombre  = $modData['modulo_nombre'];

// Buscar usuario 
$sql_user = "SELECT nombre, email 
             FROM usuarios 
             WHERE id_usuario = $id_usuario";

$result_user = mysqli_query($conexion, $sql_user);

if (!$result_user || mysqli_num_rows($result_user) === 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit;
}

$user = mysqli_fetch_assoc($result_user);
$nombre_usuario = $user['nombre'];
$email_usuario  = $user['email'];

// URL del panel
/*revisar esto GIGEEEE si no se quiere usar id de modulo en la url*/
$url_panel = "https://necotracking.com.ar/public/usuarios/panel.control.php?id_modulo=" . $id_modulo;

// correo
$asunto = "🔔 Alerta de estado en tu módulo solar - Necotracking";

$mensaje = "
Hola, {$nombre_usuario} de Necotracking:

Se detectó un cambio de estado en uno de los módulos solares asociados a tu cuenta.

📘 Detalles del evento

Módulo: {$modulo_nombre} (ID {$id_modulo})
Estado anterior: {$estado_anterior}
Nuevo estado: {$estado_actual}
Fecha y hora: {$fecha_hora}
Ubicación (si aplica): {$ubicacion}

⚠️ Información importante
El sistema registró un estado que puede requerir atención.
Te recomendamos ingresar al panel para ver el estado completo del módulo.

🔗 Acceso al panel
{$url_panel}

Si no esperabas esta alerta, comunicate con el administrador de Necotracking.

Mensaje generado automáticamente, no responda este correo.
";

// Headers
$headers  = "From: Necotracking <no-reply@necotracking.com.ar>\r\n";
$headers .= "Reply-To: no-reply@necotracking.com.ar>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";

// 4️⃣ Enviar correo
if (mail($email_usuario, $asunto, $mensaje, $headers)) {
    echo json_encode(["status" => "success", "message" => "Alerta enviada"]);
} else {
    echo json_encode(["status" => "error", "message" => "Fallo al enviar correo"]);
}

?>