<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_producto = $data['id_producto'] ?? null;
    $incluir_servicio = $data['incluir_servicio'] ?? 0;
    $nueva_cantidad = $data['cantidad'] ?? 1;

    if ($id_producto && $nueva_cantidad > 0) {
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id_producto'] == $id_producto && 
                    ((isset($item['incluir_servicio']) ? $item['incluir_servicio'] : 0) == $incluir_servicio)) {
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
