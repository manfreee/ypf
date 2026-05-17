<?php
$opcion = $_GET['opcion'] ?? 'cuenta';

switch ($opcion) {
    case 'cuenta':
        include __DIR__ . '/configuracion/cuenta.php';
        break;
    case 'panel':
        include __DIR__ . '/configuracion/panel.php';
        break;
    case 'modulos':
        include __DIR__ . '/configuracion/modulos.php';
        break;
    case 'pedidos':
        include __DIR__ . '/configuracion/mis-pedidos.php';
        break;
    default:
        echo "<p>No se encontró la opción solicitada.</p>";
        break;
}
