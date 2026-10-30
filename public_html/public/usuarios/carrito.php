<?php
/*Se encarga de validar la sesión del usuario, consultar la base de datos para obtener los precios e imágenes de los productos agregados, calcular subtotales, envío e impuestos (21%), y desplegar la interfaz visual con el listado detallado de ítems o un mensaje de aviso si el carrito está vacío.*/

// Carga de dependencias necesarias: encabezado de la página, funciones auxiliares y conexión a BD
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/agregar-producto.php';
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Verificación de autenticación: Se valida contra 'id_usuario' que coincide con el resto de la app
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['usuario'])) {
    header('Location: /public/public/iniciar-sesion.php');
    exit();
}

// Obtiene los elementos del carrito guardados en la sesión (o asigna un arreglo vacío si no existen)
$carrito = $_SESSION['carrito'] ?? [];

// Inicialización de variables para el cálculo global de la compra
$subtotal = 0;
$total_productos = 0;

// Recorrido inicial para calcular los montos globales consultando los precios actualizados desde la BD
foreach ($carrito as $item) {
    // Preparación y ejecución de la consulta de precios del producto
    $stmt = $conexion->prepare("SELECT precio, precio_servicio FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $item['id_producto']);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Validar si el producto existe en la BD
    if ($prod) {
        // Cálculo del costo base por cantidad de producto
        $precio_item = $prod['precio'] * $item['cantidad'];
        
        // Suma el costo del servicio adicional si el ítem lo tiene activado
        if (!empty($item['incluir_servicio'])) {
            $precio_item += $prod['precio_servicio'];
        }
        
        // Acumulación de importes y total de unidades físicas
        $subtotal += $precio_item;
        $total_productos += $item['cantidad'];
    }
}

// Configuración de costos adicionales e impuestos
$envio = 5000; // Costo fijo asignado al envío
$impuestos = $subtotal * 0.21; // Cálculo del 21% de IVA/Impuestos sobre el subtotal
$total = $subtotal + $envio + $impuestos; // Cálculo del monto final a pagar
?>

<!-- Enlace a los estilos CSS específicos de la vista del carrito -->
<link rel="stylesheet" href="/css/carrito.css">

<main>
    <?php if (!empty($carrito)): ?>
        <!-- SECCIÓN 1: Listado de productos en el carrito -->
        <div class="section-productos">
            <div class="contenedor-productos">
                <?php foreach ($carrito as $item):
                    // Consulta detallada de cada producto para renderizar su tarjeta (nombre, precios e imagen)
                    $stmt = $conexion->prepare("SELECT nombre, precio, precio_servicio, imagen FROM productos WHERE id_producto = ?");
                    $stmt->bind_param("i", $item['id_producto']);
                    $stmt->execute();
                    $producto = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$producto) continue; // Si el producto ya no existe en la BD, lo omite

                    // Ruta relativa de la imagen del producto
                    $imagen = "/css/recursos/img/productos/" . htmlspecialchars($producto['imagen']);

                    // Cálculo individual del precio total por ítem (producto x cantidad + servicios adicionales)
                    $precio_total = $producto['precio'] * $item['cantidad'];
                    if (!empty($item['incluir_servicio'])) {
                        $precio_total += $producto['precio_servicio'];
                    }
                    ?>
                    
                    <!-- Estructura HTML de la tarjeta individual del producto -->
                    <div class="producto-especifico" data-id-producto="<?= $item['id_producto'] ?>"
                        data-incluir-servicio="<?= !empty($item['incluir_servicio']) ? '1' : '0' ?>">
                        
                        <!-- Imagen del producto -->
                        <div class="contenedor-img">
                            <img class="borde-redondeado" src="<?= $imagen ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                        </div>
                        
                        <!-- Detalles del producto -->
                        <div class="contenedor-informacion">
                            <div class="fila superior">
                                <h6 class="medium"><?= htmlspecialchars($producto['nombre']) ?></h6>
                                <p class="pequenio color-gris">$<?= number_format($precio_total, 2, ',', '.') ?></p>
                            </div>
                            
                            <p class="fila media pequenio">
                                <?= !empty($item['incluir_servicio']) ? 'Incluye servicio' : 'Sin servicio' ?>
                            </p>
                            
                            <div class="fila inferior">
                                <div class="contenedor-izquierda">
                                    <!-- Controles interactivos para modificar la cantidad (manejados por JavaScript) -->
                                    <div class="elegir-cantidad">
                                        <p class="btn-cantidad" id="restar">-</p>
                                        <input class="btn-cantidad cantidad-input" type="tel"
                                            value="<?= $item['cantidad'] ?>" min="1"
                                            data-id-producto="<?= $item['id_producto'] ?>"
                                            data-incluir-servicio="<?= !empty($item['incluir_servicio']) ? '1' : '0' ?>">
                                        <p class="btn-cantidad" id="sumar">+</p>
                                    </div>
                                    
                                    <!-- Formulario para eliminar el producto individual del carrito -->
                                    <form action="" method="POST">
                                        <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                                        <input type="hidden" name="incluir_servicio"
                                            value="<?= !empty($item['incluir_servicio']) ? '1' : '0' ?>">
                                        <button class="btn btn-secundario" type="submit" name="btnAccion" value="Eliminar">
                                            <iconify-icon icon="material-symbols:delete-rounded"
                                                class="color-error icon-h6"></iconify-icon>
                                            <p class="pequenio color-error">Eliminar</p>
                                        </button>
                                    </form>
                                </div>
                                
                                <p class="contenedor-derecha pequenio media-negrita">
                                    $<?= number_format($precio_total, 2, ',', '.') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SECCIÓN 2: Resumen de compra / Prefactura -->
        <div class="contenedor-prefactura">
            <div class="contenedor-header">
                <p class="media-negrita pequenio">Total de Orden</p>
                <!-- Formateo plural/singular según la cantidad de productos -->
                <p class="pequenio color-gris"> <?= $total_productos ?> producto<?= $total_productos != 1 ? 's' : '' ?></p>
            </div>

            <!-- Desglose numérico de subtotal, envío e impuestos -->
            <div class="contenedor-precios">
                <div class="sub-contenedor-precio">
                    <p class="pequenio color-gris"> Subtotal</p>
                    <p class="pequenio color-gris"> $<?= number_format($subtotal, 2, ',', '.') ?></p>
                </div>
                <div class="sub-contenedor-precio">
                    <p class="pequenio color-gris"> Envio</p>
                    <p class="pequenio color-gris"> $<?= number_format($envio, 2, ',', '.') ?></p>
                </div>
                <div class="sub-contenedor-precio">
                    <p class="pequenio color-gris"> Impuestos nacionales</p>
                    <p class="pequenio color-gris"> $<?= number_format($impuestos, 2, ',', '.') ?></p>
                </div>
            </div>

            <!-- Mostrar el Total Final acumulado -->
            <div class="contenedor-footer">
                <p class="media-negrita pequenio">Total</p>
                <p class="media-negrita">$<?= number_format($total, 2, ',', '.') ?></p>
            </div>

            <!-- Botón para procesar y finalizar el pedido -->
            <form action="compra-finalizada.php" method="POST">
                <button class="btn btn-primario" type="submit">
                    <p class="color-blanco pequenio">Confirmar compra</p>
                </button>
            </form>
        </div>

    <?php else: ?>
        <!-- Estado alternativo: Se muestra cuando el carrito no tiene elementos -->
        <div class="contenedor-sin-productos">
            <iconify-icon icon="mage:robot-sad-fill" class="color-gris icon-h3"></iconify-icon>
            <p class="media-negrita color-gris pequenio">Estamos muy tristes. No tenes ningun producto en tu carrito.</p>
        </div>
    <?php endif; ?>
</main>
</body>

<!-- Carga del JS dinamizador para actualizar cantidades/eventos en tiempo real -->
<script src="/js/carrito.js"></script>
</html>