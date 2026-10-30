<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';

if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// 1. Consulta datos registrados
echo "<br><br>----- REGISTRO DE DATOS -----<br><br>";
$sql = "SELECT * FROM datos";
$resultado = mysqli_query($conexion, $sql);
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "ID: " . $fila["id_dato"] .
        " - Fecha: " . $fila["fecha_hora"] .
        " - Tensión: " . $fila["tension"] .
        " - Corriente: " . $fila["corriente"] .
        " - Potencia: " . $fila["potencia"] .
        " - Energía: " . $fila["energia"] .
        " - Ángulo: " . $fila["angulo"] .
        " - Intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}

// 2. Consulta promedio diario
echo "<br><br>----- REGISTRO DIARIO -----<br><br>";
$sql_diario = "SELECT * FROM promedio_diario";
$res_diario = mysqli_query($conexion, $sql_diario);
if ($res_diario && mysqli_num_rows($res_diario) > 0) {
    while ($fila = mysqli_fetch_assoc($res_diario)) {
        echo "ID: " . $fila["id_promd"] .
        " - Tensión: " . $fila["tension"] .
        " - Corriente: " . $fila["corriente"] .
        " - Potencia: " . $fila["potencia"] .
        " - Energía: " . $fila["energia"] .
        " - Ángulo: " . $fila["angulo"] .
        " - Intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}

// 3. Consulta promedio semanal
echo "<br><br>----- PROMEDIO SEMANAL -----<br><br>";
$sql_semanal = "SELECT * FROM promedio_semanal";
$res_semanal = mysqli_query($conexion, $sql_semanal);
if ($res_semanal && mysqli_num_rows($res_semanal) > 0) {
    while ($fila = mysqli_fetch_assoc($res_semanal)) {
        echo "ID: " . $fila["id_proms"] .
        " - Tensión: " . $fila["tension"] .
        " - Corriente: " . $fila["corriente"] .
        " - Potencia: " . $fila["potencia"] .
        " - Energía: " . $fila["energia"] .
        " - Ángulo: " . $fila["angulo"] .
        " - Intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros</title>
</head>
<body>
    
    <!-- Botón para ver la tabla en tiempo real -->
    <form action="/index.php">
        <input type="submit" value="Ver tabla en tiempo real" />
    </form>

</body>
</html>