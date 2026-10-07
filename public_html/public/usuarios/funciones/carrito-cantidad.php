<?php
/*Endpoint de API interna en PHP diseñado para actualizar de forma dinámica la cantidad de un ítem en el carrito de compras mediante AJAX. Recibe los datos enviados desde el cliente en formato JSON (id_producto, incluir_servicio y cantidad), busca el producto correspondiente dentro de la variable de sesión $_SESSION['carrito'] y, si lo encuentra, modifica su cantidad antes de responder con una confirmación json_encode estructurada*/

// Inicia o recupera la sesión del usuario para poder acceder al carrito
session_start();

// Establece la cabecera de la respuesta indicando que la salida será en formato JSON
header('Content-Type: application/json');

// Comprueba que la solicitud haya sido enviada mediante el método HTTP POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lee el cuerpo de la petición (JSON) y lo convierte a un array asociativo PHP
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Obtiene las variables recibidas o asigna valores por defecto si no existen
    $id_producto = $data['id_producto'] ?? null;
    $incluir_servicio = $data['incluir_servicio'] ?? 0;
    $nueva_cantidad = $data['cantidad'] ?? 1;

    // Valida que el ID del producto esté presente y la nueva cantidad sea mayor a cero
    if ($id_producto && $nueva_cantidad > 0) {
        
        // Verifica si la variable de sesión 'carrito' existe y contiene elementos
        if (isset($_SESSION['carrito'])) {
            
            // Recorre los productos del carrito por referencia (&) para poder modificar el elemento original
            foreach ($_SESSION['carrito'] as &$item) {
                
                // Compara el id_producto y el estado del servicio adicional para identificar la variante exacta
                if ($item['id_producto'] == $id_producto && 
                    ((isset($item['incluir_servicio']) ? $item['incluir_servicio'] : 0) == $incluir_servicio)) {
                    
                    // Actualiza la cantidad con el nuevo valor
                    $item['cantidad'] = $nueva_cantidad;
                    
                    // Devuelve respuesta de éxito y detiene la ejecución del script
                    echo json_encode(['success' => true, 'mensaje' => 'Cantidad actualizada']);
                    exit;
                }
            }
        }
        
        // Respuesta en caso de que el producto no exista en el carrito guardado en la sesión
        echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado en el carrito']);
    } else {
        // Respuesta en caso de que los datos recibidos no cumplan las validaciones básicas
        echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    }
} else {
    // Respuesta enviada si la petición no fue realizada mediante el método POST
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
}