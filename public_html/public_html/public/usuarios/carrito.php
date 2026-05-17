<?php
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php';
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/agregar-producto.php';
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: /public/public/iniciar-sesion.php');
    exit();
}

$carrito = $_SESSION['carrito'] ?? [];
// combinar_carrito($_SESSION['carrito']);

// Calcular totales
$subtotal = 0;
$total_productos = 0;
foreach ($carrito as $item) {
    $stmt = $conexion->prepare("SELECT precio, precio_servicio FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $item['id_producto']);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $precio_item = $prod['precio'] * $item['cantidad'];
    if ($item['incluir_servicio']) {
        $precio_item += ($prod['precio_servicio'] * $item['cantidad']);
    }
    $subtotal += $precio_item;
    $total_productos += $item['cantidad'];
}

$envio = 5000; // Costo fijo de envío
$impuestos = $subtotal * 0.21; // 21% de impuestos
$total = $subtotal + $envio + $impuestos;
?>
<link rel="stylesheet" href="/css/carrito.css">
<main>
    <?php if (!empty($carrito)): ?>
        <div class="section-productos">
            <div class="contenedor-productos">
                <div class="contenedor-productos">
                    <?php foreach ($carrito as $item):
                        // Consulta del producto por ID
                        $stmt = $conexion->prepare("SELECT nombre, precio, precio_servicio, imagen FROM productos WHERE id_producto = ?");
                        $stmt->bind_param("i", $item['id_producto']);
                        $stmt->execute();
                        $producto = $stmt->get_result()->fetch_assoc();
                        $stmt->close();

                        $imagen = "/css/recursos/img/productos/" . $producto['imagen'];

                        // Calcular precio total
                        $precio_total = $producto['precio'] * $item['cantidad'];
                        if ($item['incluir_servicio']) {
                            $precio_total += $producto['precio_servicio'];
                        }
                        ?>
                        <div class="producto-especifico" data-id-producto="<?= $item['id_producto'] ?>"
                            data-incluir-servicio="<?= $item['incluir_servicio'] ? '1' : '0' ?>">
                            <div class="contenedor-img">
                                <img class="borde-redondeado" src="<?= $imagen ?>">
                            </div>
                            <div class="contenedor-informacion">
                                <div class="fila superior">
                                    <h6 class="medium"><?= htmlspecialchars($producto['nombre']) ?></h6>
                                    <p class="pequenio color-gris">$<?= number_format($precio_total, 2, ',', '.') ?></p>
                                </div>
                                <p class="fila media pequenio">
                                    <?= $item['incluir_servicio'] ? 'Incluye servicio' : 'Sin servicio' ?>
                                </p>
                                <div class="fila inferior">
                                    <div class="contenedor-izquierda">
                                        <div class="elegir-cantidad">
                                            <p class="btn-cantidad" id="restar">-</p>
                                            <input class="btn-cantidad cantidad-input" type="tel"
                                                value="<?= $item['cantidad'] ?>" min="1"
                                                data-id-producto="<?= $item['id_producto'] ?>"
                                                data-incluir-servicio="<?= $item['incluir_servicio'] ? '1' : '0' ?>">
                                            <p class="btn-cantidad" id="sumar">+</p>
                                        </div>
                                        <form action="" method="POST">
                                            <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                                            <input type="hidden" name="incluir_servicio"
                                                value="<?= $item['incluir_servicio'] ? '1' : '0' ?>">
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
        </div>
        <div class="contenedor-prefactura">
            <div class="contenedor-header">
                <p class="media-negrita pequenio">Total de Orden</p>
                <p class="pequenio color-gris"> <?= $total_productos ?> producto<?= $total_productos != 1 ? 's' : '' ?></p>
            </div>

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

            <div class="contenedor-footer">
                <p class="media-negrita pequenio">Total</p>
                <p class="media-negrita">$<?= number_format($total, 2, ',', '.') ?></p>
            </div>

            <form action="compra-finalizada.php" method="POST">
                <button class="btn btn-primario" type="submit">
                    <p class="color-blanco pequenio">Confirmar compra</p>
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="contenedor-sin-productos">
            <iconify-icon icon="mage:robot-sad-fill" class="color-gris icon-h3"></iconify-icon>
            <p class="media-negrita color-gris pequenio">Estamos muy tristes. No tenes ningun producto en tu carrito.</p>
        </div>
    <?php endif; ?>
</main>
</body>
<script src="/js/carrito.js"></script>
</html>