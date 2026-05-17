<?php 
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php';
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /public/public/iniciar-sesion.php");
    exit;
}

// Verificar que haya productos en el carrito
// if (empty($_SESSION['carrito'])) {
//     header("Location: /Luminora/carrito");
//     exit;
// }

$carrito = $_SESSION['carrito'];
$id_usuario = $_SESSION['id_usuario'];

// Calcular el total del pedido
$subtotal = 0;
foreach ($carrito as $item) {
    $stmt = $conexion->prepare("SELECT precio, precio_servicio FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $item['id_producto']);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $precio_item = $prod['precio'] * $item['cantidad'];
    if ($item['incluir_servicio']) {
        $precio_item += $prod['precio_servicio'];
    }
    $subtotal += $precio_item;
}

$envio = 5000;
$impuestos = $subtotal * 0.21;
$total = $subtotal + $envio + $impuestos;

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Insertar el pedido
    $stmt = $conexion->prepare("INSERT INTO venta (id_usuario, total) VALUES (?, ?)");
    $stmt->bind_param("id", $id_usuario, $total);
    $stmt->execute();
    $id_pedido = $conexion->insert_id;
    $stmt->close();
    
    // Insertar los detalles del pedido
    $stmt = $conexion->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, incluir_servicio, precio_unitario) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($carrito as $item) {
        // Obtener el precio del producto
        $stmt_precio = $conexion->prepare("SELECT precio, precio_servicio FROM productos WHERE id_producto = ?");
        $stmt_precio->bind_param("i", $item['id_producto']);
        $stmt_precio->execute();
        $producto = $stmt_precio->get_result()->fetch_assoc();
        $stmt_precio->close();
        
        $precio_unitario = $producto['precio'];
        if ($item['incluir_servicio']) {
            $precio_unitario += $producto['precio_servicio'];
        }
        
        $stmt->bind_param("iiiid", $id_pedido, $item['id_producto'], $item['cantidad'], $item['incluir_servicio'], $precio_unitario);
        $stmt->execute();
    }
    $stmt->close();
    
    // Confirmar la transacción
    $conexion->commit();
    
    // Limpiar el carrito después de guardar el pedido
    $_SESSION['carrito'] = [];
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conexion->rollback();
    die("Error al procesar el pedido: " . $e->getMessage());
}
?>
<link rel="stylesheet" href="/css/compra-finalizada.css">
<main class="contenedor-compra-finalizada">
    <div class="tarjeta-confirmacion">
        <div class="icono-exito">
            <iconify-icon icon="mdi:check-circle" class="color-primario icon-grande"></iconify-icon>
        </div>
        <h2 class="super-negrita color-primario">¡Compra realizada con éxito!</h2>
        <p class="texto-confirmacion">
            Tu pedido ha sido procesado correctamente. Recibirás un correo electrónico con los detalles de tu compra.
        </p>
        <div class="contenedor-botones">
            <a href="/index.php" class="btn btn-primario">
                <p class="color-blanco pequenio">Volver al inicio</p>
            </a>
            <a href="/public/usuarios/panel-control.php" class="btn btn-secundario">
                <p class="pequenio">Ver mi panel</p>
            </a>
        </div>
    </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/footer.php'; ?>