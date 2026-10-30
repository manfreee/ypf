<?php
/*Este código actúa como un enrutador dinámico de vistas dentro de la sección de configuración. Evalúa la variable recibida por parámetro URL ($_GET['opcion']), asignando por defecto la vista cuenta si no se especifica ninguna, y utiliza una estructura switch para incluir y renderizar la plantilla PHP correspondiente (cuenta, panel, modulos o mis-pedidos). En caso de recibir un valor no reconocido, muestra un mensaje de error simple.*/

// Obtiene y sanitiza el valor del parámetro 'opcion' desde la URL ($_GET)
$opcion = filter_input(INPUT_GET, 'opcion', FILTER_DEFAULT) ?? 'cuenta';

// Evalúa el valor de $opcion para incluir la vista/sección correspondiente
switch ($opcion) {
    case 'cuenta':
        // Incluye la plantilla de configuración de la cuenta del usuario
        include __DIR__ . '/configuracion/cuenta.php';
        break;
    case 'panel':
        // Incluye la plantilla del panel general
        include __DIR__ . '/configuracion/panel.php';
        break;
    case 'modulos':
        // Incluye la plantilla para la gestión de módulos
        include __DIR__ . '/configuracion/modulos.php';
        break;
    case 'pedidos':
        // Incluye la plantilla para consultar el historial de pedidos
        include __DIR__ . '/configuracion/mis-pedidos.php';
        break;
    default:
        // Mensaje de reserva en caso de que el valor introducido en la URL no coincida con ningún caso
        echo "<p>No se encontró la opción solicitada.</p>";
        break;
}