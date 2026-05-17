<?php
include("conexion.php");

header('Content-Type: application/json');

$sql = "SELECT fecha_hora, tension, corriente, potencia FROM datos ORDER BY id_dato DESC LIMIT 20";
$resultado = mysqli_query($conexion, $sql);

$datos = [];

// Recorremos los resultados y los almacenamos en un arreglo
while ($fila = mysqli_fetch_assoc($resultado)) {
    $datos[] = $fila;
}

echo json_encode(array_reverse($datos)); // invertimos para que el más antiguo quede primero
?>