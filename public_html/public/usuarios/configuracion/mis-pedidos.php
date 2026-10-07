<?php
/*Este script es la vista de historial de pedidos del usuario (mis-pedidos.php). Se encarga de verificar que el usuario esté autenticado, realizar una consulta a la base de datos para recuperar todas las ventas asociadas a su cuenta y renderizarlas en tarjetas dinámicas. Para cada venta registrada, ejecuta una segunda consulta que recupera el desglose detallado de los productos comprados, cantidades, precios e inclusión de servicios opcionales, calculando el subtotal y formateando los importes en moneda local.*/
// Incluye la conexión a la base de datos y la gestión de la sesión
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/session.php';

// Comprueba si existe la variable de sesión id_usuario; si no está definida, interrumpe la ejecución
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Usuario no autenticado");
}

// Guarda el ID del usuario actual de la sesión
$id_usuario = $_SESSION['id_usuario'];

// Prepara la consulta SQL para obtener las ventas/pedidos pertenecientes al usuario ordenadas por fecha reciente
$sql_pedidos = "
    SELECT 
        v.id_venta AS id_pedido,
        v.fecha_hora AS fecha,
        v.total,
        'completado' AS estado
    FROM venta v
    WHERE v.id_usuario = ?
    ORDER BY v.fecha_hora DESC
";

// Ejecuta la consulta del encabezado de pedidos mediante sentencias preparadas
$stmt = $conexion->prepare($sql_pedidos);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

// Obtiene todos los pedidos como un array asociativo
$pedidos = $resultado->fetch_all(MYSQLI_ASSOC);

// Cierra la sentencia para liberar recursos de memoria
$stmt->close();
?>

<!-- Carga de la hoja de estilos específica para la vista de mis pedidos -->
<link rel="stylesheet" href="/css/mis-pedidos.css">

<div class="contenedor-modulos" style="max-height: 450px; overflow-y: auto;">
    <!-- Comprueba si el usuario cuenta con al menos un pedido registrado -->
    <?php if (!empty($pedidos)): ?>
        <!-- Recorre la lista general de pedidos del usuario -->
        <?php foreach ($pedidos as $pedido): ?>
            <div class="modulo pedido-especifico">
                <div class="cont-superior header-pedido">
                    <h6 class="medium texto-truncado color-blanco">
                        Pedido #<?= $pedido['id_pedido'] ?>
                    </h6>
                </div>

                <!-- Cabecera informativa del pedido: Fecha, Importe Total y Estado -->
                <div class="cont-datos-sensores contenedor-informacion">
                    <div class="item informacion-item">
                        <p class="pequenio color-blanco medium">Fecha</p>
                        <p class="pequenio color-blanco">
                            <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?>
                        </p>
                    </div>
                    <div class="item informacion-item">
                        <p class="pequenio color-blanco medium">Total</p>
                        <p class="pequenio color-blanco">
                            $<?= number_format($pedido['total'], 2, ',', '.') ?>
                        </p>
                    </div>
                    <div class="item informacion-item">
                        <p class="pequenio color-blanco medium">Estado</p>
                        <p class="pequenio color-blanco">
                            <?= ucfirst($pedido['estado']) ?>
                        </p>
                    </div>
                </div>

                <!-- Sección de desglose de ítems pertenecientes a este pedido -->
                <div class="detalle-pedido">
                    <h6 class="light color-gris">Productos</h6>
                    <div class="tipo">
                        <p class="pequenio">Producto</p>
                        <p class="pequenio">Cantidad</p>
                        <p class="pequenio">Precio</p>
                        <p class="pequenio">Servicio</p>
                    </div>
                    <?php
                    // Prepara la consulta para traer los detalles/productos del pedido actual
                    $sql_detalle = "
                        SELECT 
                            dv.cantidad,
                            dv.incluir_servicio,
                            dv.precio_unitario,
                            p.nombre,
                            dv.precio_servicio
                        FROM detalle_venta dv
                        INNER JOIN productos p ON dv.id_producto = p.id_producto
                        WHERE dv.id_venta = ?
                    ";

                    // Ejecuta la consulta de detalle especificando el id_pedido
                    $stmt_detalle = $conexion->prepare($sql_detalle);
                    $stmt_detalle->bind_param("i", $pedido['id_pedido']);
                    $stmt_detalle->execute();
                    $resultado_detalle = $stmt_detalle->get_result();
                    $detalles = $resultado_detalle->fetch_all(MYSQLI_ASSOC);
                    $stmt_detalle->close();
                    ?>

                    <ul class="producto-detalle">
                        <?php 
                        $subtotal = 0;
                        // Recorre cada producto del pedido actual y calcula el valor subtotal
                        foreach ($detalles as $detalle): 
                            $precio_total = $detalle['precio_unitario'] * $detalle['cantidad'];
                            if ($detalle['incluir_servicio']) {
                                $precio_total += $detalle['precio_servicio'] * $detalle['cantidad'];
                            }
                            $subtotal += $precio_total;
                        ?>
                            <li>
                                <p class="pequenio media-negrita"><?= htmlspecialchars($detalle['nombre']) ?></p>
                                <p class="pequenio"><?= $detalle['cantidad'] ?></p>
                                <p class="pequenio">$<?= number_format($detalle['precio_unitario'], 2, ',', '.') ?></p>
                                <p class="pequenio">
                                    <?php if ($detalle['incluir_servicio']): ?>
                                        +$<?= number_format($detalle['precio_servicio'], 2, ',', '.') ?> (servicio)
                                    <?php else: ?>
                                        Sin servicio
                                    <?php endif; ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                        
                        <!-- Muestra la sumatoria total del subtotal calculado -->
                        <li class="total">
                            <p class="pequenio media-negrita">Total:</p>
                            <p class="pequenio"></p>
                            <p class="pequenio media-negrita">
                                $<?= number_format($subtotal, 2, ',', '.') ?>
                            </p>
                            <p class="pequenio"></p>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Vista alternativa en caso de que el usuario no posea historial de compras -->
        <div class="contenedor-sin-productos">
            <iconify-icon icon="mage:robot-sad-fill" class="color-gris icon-h3"></iconify-icon>
            <p class="media-negrita color-gris pequenio">No tenés ningún pedido.</p>
        </div>
    <?php endif; ?>
</div>