<?php
$host = "localhost";
$bbdd = "a0030581_modulos"; // Nombre de la base de datos registrada en DON WEB

//$contrasena = "";
//$usuario = "root";

// Credenciales de la base de datos en DON WEB
$contrasena = "mawuDEfo16";
$usuario = "a0030581_modulos";

// Crear la conexión
$conexion = mysqli_connect($host, $usuario, $contrasena, $bbdd);

// Verificar si hubo error en la conexión
if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a utf8mb4 (coincide con la BD)
mysqli_set_charset($conexion, "utf8mb4");
?>