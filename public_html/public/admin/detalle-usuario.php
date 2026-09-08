<?php
// Este archivo verifica si llega un parámetro "id" en la URL.
// Se suele usar para obtener información de un usuario, producto o registro específico.
// Es parte de la API

    // Si no existe el valor "id" dentro de la URL, se ejecuta este bloque.
    if(!isset($_GET['id'])) {
        // Se comenta esta línea porque todavía no se quiere redirigir.
        // header("location: usuarios.php");
        exit();
    }
    else{
        // Si llega el valor "id", se ejecuta este bloque.
        // $_GET['id'] obtiene el dato enviado por la URL.
        // Ejemplo: detalle-usuario.php?id=10  -> $_GET['id'] = 10

        // var_dump() muestra el contenido de la variable con detalles.
        // Se usa normalmente para verificar o depurar datos.
        var_dump($_GET['id']);
    }
?>