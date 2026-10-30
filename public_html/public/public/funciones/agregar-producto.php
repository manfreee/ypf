<?php
// Gestiona la accion de agregar al carrito.
$mensaje = '';

// Inicia la sesión
session_start();

// Carga la conexión a la base de datos
require_once$_SERVER['DOCUMENT_ROOT'] . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAccion'])) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../usuarios/iniciar-sesion.php");
    }
    if ($_POST['btnAccion'] === 'Agregar') {
        // Validar que el ID sea numérico
        if (is_numeric($_POST['id_producto'])) {

            $id_producto = $_POST['id_producto'];
            $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1; // Cantidad por defecto 1
            $incluir_servicio = isset($_POST['incluir_servicio']) ? true : false; // Checkbox

            // Inicializar carrito si no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            // Guardar el producto en la sesion
            $_SESSION['carrito'][] = [
                'id_producto' => $id_producto,
                'cantidad' => $cantidad,
                'incluir_servicio' => $incluir_servicio
            ];
            combinar_carrito($_SESSION['carrito']);

            header("Location: /public/usuarios/carrito.php");
            exit;
        } else {
            $mensaje = "ID de producto no válido";
        }
    }
    if ($_POST['btnAccion'] === 'Eliminar') {
        $id_producto = $_POST['id_producto'];
        $incluir_servicio = isset($_POST['incluir_servicio']) && $_POST['incluir_servicio'] == '1';

        foreach ($_SESSION['carrito'] as $key => $item) {
            if ($item['id_producto'] == $id_producto && $item['incluir_servicio'] == $incluir_servicio) {
                unset($_SESSION['carrito'][$key]);
            }
        }
        // Reindexar el array para evitar huecos en los indices
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);

        // Redirigir para evitar que se vuelva a enviar el POST al refrescar
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
function combinar_carrito(&$carrito)
{
    $nuevo_carrito = [];
    foreach ($carrito as $item) {
        // Determinar un ID unico para el producto + servicio
        $id_unico = $item['id_producto'] . '-' . (!empty($item['incluir_servicio']) ? '1' : '0');

        // Si ya existe en el nuevo carrito, sumamos la cantidad
        if (isset($nuevo_carrito[$id_unico])) {
            $nuevo_carrito[$id_unico]['cantidad'] += $item['cantidad'];
        } else {
            // Si no existe, lo agregamos
            $nuevo_carrito[$id_unico] = $item;
        }
    }
    // Reindexamos el array para que sea int otra vez
    $carrito = array_values($nuevo_carrito);
}
?>