<?php 
/*Procesa y finaliza la compra guardando los datos directamente en la base de datos dentro de una transacción SQL. Se encarga de verificar que el usuario haya iniciado sesión, calcula el total final del pedido (incluyendo envío e impuestos del 21%), registra la venta general en la tabla venta y desglosa cada ítem en la tabla detalle_venta. Una vez guardada la transacción correctamente, vacía el carrito guardado en la sesión y muestra en pantalla un mensaje de confirmación de éxito con opciones para volver al inicio o ir al panel del usuario.*/

// Carga del componente de encabezado y la conexión a la base de datos
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php';
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Verificar que el usuario esté autenticado en el sistema
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /public/public/iniciar-sesion.php");
    exit;
}

// Redirigir si el carrito está vacío o no existe para evitar procesamiento erroneo
if (empty($_SESSION['carrito'])) {
    header("Location: /public/usuarios/configuracion/panel.php");
    exit;
}

// Lectura de los datos del usuario y del carrito desde la sesión
$carrito = $_SESSION['carrito'];
$id_usuario = $_SESSION['id_usuario'];

// Calcular el subtotal consultando los precios actuales de cada producto
$subtotal = 0;
foreach ($carrito as $item) {
    $stmt = $conexion->prepare("SELECT precio, precio_servicio FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $item['id_producto']);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($prod) {
        $precio_item = $prod['precio'] * $item['cantidad'];
        if (!empty($item['incluir_servicio'])) {
            $precio_item += $prod['precio_servicio'];
        }
        $subtotal += $precio_item;
    }
}

// Definición de costo fijo de envío e impuestos (21%) para obtener el total general
$envio = 5000;
$impuestos = $subtotal * 0.21;
$total = $subtotal + $envio + $impuestos;

// Iniciar transacción de base de datos para garantizar integridad en los registros
$conexion->begin_transaction();

try {
    // 1. Insertar el registro de la orden general en la tabla 'venta'
    $stmt = $conexion->prepare("INSERT INTO venta (fecha_hora, id_usuario, total) VALUES (NOW(), ?, ?)");
    $stmt->bind_param("id", $id_usuario, $total);
    $stmt->execute();
    $id_venta = $conexion->insert_id; // ID generado para la nueva venta
    $stmt->close();
    
    // 2. Preparar inserción de cada producto en la tabla 'detalle_venta' adaptada a la BD exacta
    $stmt_detalle = $conexion->prepare(
        "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, incluir_servicio, precio_servicio, subtotal, comprobante, verificado) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    // Preparar actualización de stock
    $stmt_stock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");

    foreach ($carrito as $item) {
        // Consultar los datos exactos del producto
        $stmt_precio = $conexion->prepare("SELECT precio, precio_servicio FROM productos WHERE id_producto = ?");
        $stmt_precio->bind_param("i", $item['id_producto']);
        $stmt_precio->execute();
        $producto = $stmt_precio->get_result()->fetch_assoc();
        $stmt_precio->close();
        
        if (!$producto) continue;

        $precio_unitario = (float)$producto['precio'];
        $incluir_servicio = !empty($item['incluir_servicio']) ? 1 : 0;
        $precio_servicio = $incluir_servicio ? (float)$producto['precio_servicio'] : 0.00;
        
        // Subtotal individual por detalle
        $subtotal_detalle = ($precio_unitario * $item['cantidad']) + $precio_servicio;
        
        // Valores por defecto para comprobante y verificado según la BD
        $comprobante = ''; // MEDIUMBLOB vacío por defecto
        $verificado = 0;  // Tinyint (No verificado por defecto)

        // Binding con tipos de datos exactos: i (int), d (double/decimal), s (string/blob)
        // Estructura: id_venta(i), id_producto(i), cantidad(i), precio_unitario(d), incluir_servicio(i), precio_servicio(d), subtotal(d), comprobante(s), verificado(i)
        $stmt_detalle->bind_param(
            "iiididdsi", 
            $id_venta, 
            $item['id_producto'], 
            $item['cantidad'], 
            $precio_unitario, 
            $incluir_servicio, 
            $precio_servicio, 
            $subtotal_detalle, 
            $comprobante, 
            $verificado
        );
        $stmt_detalle->execute();

        // Actualizar stock disponible
        $stmt_stock->bind_param("ii", $item['cantidad'], $item['id_producto']);
        $stmt_stock->execute();
    }
    
    $stmt_detalle->close();
    $stmt_stock->close();
    
    // Confirmar y aplicar todos los cambios de la transacción
    $conexion->commit();
    
    // Vaciar el carrito de la sesión tras completar la compra con éxito
    $_SESSION['carrito'] = [];
    
} catch (Exception $e) {
    // Si ocurre algún fallo, revertir todos los registros creados para evitar datos corruptos
    if (isset($conexion)) {
        $conexion->rollback();
    }
    error_log("Error al procesar la compra: " . $e->getMessage());
    die("Error al procesar el pedido. Por favor, intenta de nuevo o ponte en contacto con soporte.");
}
?>

<!-- Enlace al archivo CSS específico para la pantalla de confirmación -->
<link rel="stylesheet" href="/css/compra-finalizada.css">

<main class="contenedor-compra-finalizada">
    <!-- Tarjeta visual con mensaje de éxito de compra -->
    <div class="tarjeta-confirmacion">
        <div class="icono-exito">
            <iconify-icon icon="mdi:check-circle" class="color-primario icon-grande"></iconify-icon>
        </div>
        <h2 class="super-negrita color-primario">¡Compra realizada con éxito!</h2>
        <p class="texto-confirmacion">
            Tu pedido ha sido procesado correctamente. Recibirás un correo electrónico con los detalles de tu compra.
        </p>
        
        <!-- Botones de navegación para redirigir al usuario -->
        <div class="contenedor-botones">
            <a href="/index.php" class="btn btn-primario">
                <p class="color-blanco pequenio">Volver al inicio</p>
            </a>
            <a href="/public/usuarios/configuracion/panel.php" class="btn btn-secundario">
                <p class="pequenio">Ver mi panel</p>
            </a>
        </div>
    </div>
</main>

<!-- Carga del pie de página global -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/footer.php'; ?>