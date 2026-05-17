<?php
include("conexion.php");

if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

//consulta datos registrados
echo "<br><br>----- registro de datos -----<br><br>";
$sql = "SELECT * FROM datos";
$resultado = mysqli_query($conexion, $sql);
if (mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "ID: " . $fila["id_dato"] .
        " fecha : ".$fila["fecha_hora"] .
        " - Tensión: " . $fila["tension"] .
        " - corriente: " . $fila["corriente"] .
        " - potencia: " . $fila["potencia"] .
        " - energia: " . $fila["energia"] .
        " - angulo: " . $fila["angulo"] .
        " - intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}

//consulta promedio diario
echo "<br><br>----- registro diario -----<br><br>";
$sql = "SELECT * FROM promedio_diario";
$resultado = mysqli_query($conexion, $sql);
if (mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "ID: " . $fila["id_promedio"] .
        " fecha : ".$fila["fecha_hora"] .
        " - Tensión: " . $fila["tension"] .
        " - corriente: " . $fila["corriente"] .
        " - potencia: " . $fila["potencia"] .
        " - energia: " . $fila["energia"] .
        " - angulo: " . $fila["angulo"] .
        " - intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}

//consulta promedio semanal
echo "<br><br>----- PROMEDIO SEMANAL -----<br><br>";
$sql2 = "SELECT * FROM promedio_semanal";
$resultado2 = mysqli_query($conexion, $sql2);
if (mysqli_num_rows($resultado2) > 0) {
    
    while ($fila = mysqli_fetch_assoc($resultado2)) {
        echo "ID: " . $fila["id_promedio"] .
        " fecha : ".$fila["fecha_hora"] .
        " - Tensión: " . $fila["tension"] .
        " - corriente: " . $fila["corriente"] .
        " - potencia: " . $fila["potencia"] .
        " - energia: " . $fila["energia"] .
        " - angulo: " . $fila["angulo"] .
        " - intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}

//consulta promedio mensual
echo "<br><br>----- PROMEDIO MENSUAL -----<br><br>";
$sql2 = "SELECT * FROM promedio_mensual";
$resultado2 = mysqli_query($conexion, $sql2);
if (mysqli_num_rows($resultado2) > 0) {
    
    while ($fila = mysqli_fetch_assoc($resultado2)) {
        echo "ID: " . $fila["id_promedio"] .
        " fecha : ".$fila["fecha_hora"] .
        " - Tensión: " . $fila["tension"] .
        " - corriente: " . $fila["corriente"] .
        " - potencia: " . $fila["potencia"] .
        " - energia: " . $fila["energia"] .
        " - angulo: " . $fila["angulo"] .
        " - intensidad: " . $fila["intensidad"] .
        " - Módulo: " . $fila["id_modulo"] .
        "<br>";
    }
} else {
    echo "0 resultados";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros</title>
</head>
<body>
    
    <!-- Botón para ver la tabla en tiempo real -->
    <form action="../index.html">
        <input type="submit" value="Ver tabla en tiempo real" />
    </form>

</body>
</html>