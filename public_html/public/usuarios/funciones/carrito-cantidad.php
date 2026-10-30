<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id_producto = isset($data['id_producto']) ? (int)$data['id_producto'] : null;
    $incluir_servicio = isset($data['incluir_servicio']) ? (int)$data['incluir_servicio'] : 0;
    $nueva_cantidad = isset($data['cantidad']) ? (int)$data['cantidad'] : 1;

    if ($id_producto && $nueva_cantidad > 0) {
        if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as &$item) {
                $item_id = (int)$item['id_producto'];
                $item_servicio = isset($item['incluir_servicio']) ? (int)$item['incluir_servicio'] : 0;

                if ($item_id === $id_producto && $item_servicio === $incluir_servicio) {
                    $item['cantidad'] = $nueva_cantidad;
                    echo json_encode(['success' => true, 'mensaje' => 'Cantidad actualizada']);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false, 'mensaje' => 'Producto no encontrado en el carrito']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    }
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
}