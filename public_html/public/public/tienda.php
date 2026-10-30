<?php
/*Obtiene el producto desde la base de datos, muestra su información,
  permite seleccionar la cantidad y el servicio opcional, y envía la
  selección al carrito mediante una solicitud POST.*/
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php');

// Recibe el ID por GET (ej: tienda.php?id=2), si no viene nada usa el 1 por defecto
$id_producto = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['id_producto']) ? (int)$_GET['id_producto'] : 1);

// Consulta del producto
$stmt = $conexion->prepare("
    SELECT id_producto, nombre, descripcion, precio, precio_servicio, stock, imagen 
    FROM productos 
    WHERE id_producto = ?
");
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();

if (!$producto) {
    echo "<div class='cont'><p>El producto solicitado no está disponible.</p></div>";
    include_once($_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php');
    exit();
}

$imagen = "/css/recursos/img/productos/" . $producto['imagen'];
$stmt->close();
?>
<link rel="stylesheet" href="/css/tienda.css">
<!-- <header class="tienda-header">
        <h1>Nuestros Productos</h1>
        <?php
            $items_en_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
        ?>
        <a href="carrito.php" class="btn-carrito">Ver Carrito (<?php echo $items_en_carrito; ?>)</a>
    </header> -->

<!-- <a href="public/admin/form-producto.php">
            <button type="button">agregar producto</button>
    </a> -->

<section class="cont modulo-comprar">
    <div class="imagenes-modulo">
        <picture class="imagen">
            <img class="borde-redondeado" src="<?= $imagen ?>" alt="Imagen adaptable al dispositivo" loading="lazy">
        </picture>
        <!-- <div class="contenedor-imagenes">
            <div class="borde-redondeado sub-imagen">
                <img class="borde-redondeado" src="css/recursos/img/productos/modulo-2.jpg">
            </div>
            <div class="borde-redondeado sub-imagen">
                <img class="borde-redondeado" src="css/recursos/img/productos/modulo-3.jpg">
            </div>
            <div class="borde-redondeado sub-imagen">
                <img class="borde-redondeado" src="css/recursos/img/productos/modulo-4.jpg">
            </div>
            <div class="borde-redondeado sub-imagen">
                <img class="borde-redondeado" src="css/recursos/img/productos/modulo-5.jpg">
            </div>
        </div> -->
    </div>
    <div class="informacion-modulo">
        <div class="informacion-principal">
            <div class="nombre-precio">
                <h4 class="color-primario ethnocentric"><?= htmlspecialchars($producto['nombre']) ?></h4>
                <div class="contenedor-precio">
                    <h3 class="rockstart super-negrita">$<?= number_format($producto['precio'], 2, ',', '.') ?></h3>
                    <!-- <p class="pequenio medium">3 cuotas de $ xxx.xxx,xx</p> -->
                </div>
            </div>
            <p class="pequenio texto-modulo"> <?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
        </div>
        <form class="form-carrito" action="/public/usuarios/carrito.php" method="POST">

            <!-- Los valores permiten al carrito identificar y calcular el producto seleccionado. -->
            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
            <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>">
            <input type="hidden" name="precio" value="<?php echo $producto['precio']; ?>">

            <div class="informacion-servicio">
                <p class="color-gris pequenio">Servicios</p>
                <h4 class="precio rockstart super-negrita">
                    $<?= number_format($producto['precio_servicio'], 2, ',', '.') ?>
                </h4>
                <label class="cont-servicio">
                    <input type="checkbox" name="incluir_servicio" value="1">
                    <p class="light">
                        Agregar <a href="#servicios-informacion" class="negrita-swiss">servicios</a> de configuración e
                        instalación.
                    </p>
                </label>
            </div>

            <!-- La cantidad se limita al stock disponible. -->
            <div class="elegir-cantidad">
                <p class="btn-cantidad sumar" id="restar">-</p>
                <input class="btn-cantidad" type="tel" id="cantidad-producto" name="cantidad" value="1" min="1"
                    max="<?= $producto['stock'] ?>">
                <p class="btn-cantidad restar" id="sumar">+</p>
            </div>


            <button type="submit" class="btn primario btn-comprar" name="btnAccion" value="Agregar">
                <iconify-icon icon="mage:bag-a-fill" class="color-blanco icon-h5"></iconify-icon>
                <p><b class="color-blanco pequenio">Agregar al carrito</b></p>
            </button>

        </form>
    </div>
</section>

<!-- <section class="cont caracteristicas-modulo">
    <div class="contenedor-caracteristicas">
        <div class="caracteristica">
            <p class="rockstart super-negrita color-primario">Medidas</p>
            <ul style="padding-left: 15px;">
                <li class="pequenio">Ancho de la base: 80cm</li>
                <li class="pequenio">Largo de la base: 55cm</li>
                <li class="pequenio">Alto de la base (sin techo): 55cm</li>
                <li class="pequenio">Alto total: 80cm</li>
                <li class="pequenio">Alto del techo: 25cm</li>
                <li class="pequenio">Alto de la base del recipiente de agua: 25cm</li>
                <li class="pequenio">Largo de la maceta:48</li>
                <li class="pequenio">Ancho de la maceta: 48cm</li>
            </ul>
        </div>
    </div>
    <picture class="imagen">
        <img class="borde-redondeado" src="css/recursos/img/productos/modulo-1.png"
            alt="Imagen adaptable al dispositivo" loading="lazy">
    </picture>
</section> -->

<section class="servicio" id="servicios-informacion">
    <div class="servicio-informacion">
        <h5><b class="color-blanco rockstart super-negrita">Servicios Profesionales</b></h5>
        <p class="light texto-servicio pequenio color-blanco">
            Al adquirir nuestro módulo solar con seguimiento automático, ofrecemos servicios opcionales de instalación y
            configuración profesional, realizados por un equipo especializado. Nos encargamos de montar la estructura,
            calibrar los sensores LDR, configurar el sistema Arduino y asegurarnos de que el dispositivo funcione con la
            máxima precisión desde el primer día.
            <br><br>
            Este servicio garantiza una puesta en marcha rápida, evita errores de armado y permite ajustar el sistema
            según la orientación, el entorno y las necesidades energéticas de tu proyecto.
        </p>
    </div>

    <div class="servicio-punteo">
        <p class="pequenio color-blanco">
            Al elegir nuestro servicio de instalación y configuración, obtienes:
        </p>
        <ul style="padding-left: 15px;">
            <li class="light pequenio">Instalación profesional del módulo solar y su estructura motorizada.</li>
            <li class="light pequenio">Calibración de los sensores para un seguimiento preciso del sol.
            </li>
            <li class="light pequenio">Configuración completa del sistema Arduino y pruebas de funcionamiento.</li>
            <li class="light pequenio">Optimización del ángulo, sensibilidad y parámetros según tu ubicación.</li>
            <li class="light pequenio">Guía inicial para monitorear el rendimiento y realizar ajustes básicos.</li>
            <li class="light pequenio">Resolución de cualquier duda técnica que puedas tener.</li>
        </ul>
    </div>
</section>

<script src="/js/agregar-cantidad.js"></script>

<script>
    // Permite cambiar la imagen principal al seleccionar una miniatura.
    document.addEventListener("DOMContentLoaded", () => {
        const imagenPrincipal = document.querySelector(".imagenes-modulo .imagen img");
        const subImagenes = document.querySelectorAll(".contenedor-imagenes .sub-imagen img");

        subImagenes.forEach(subImg => {
            subImg.addEventListener("click", () => {
                // Cambia solo la imagen principal sin tocar las miniaturas
                imagenPrincipal.src = subImg.src;
            });
        });
    });
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/footer.php'; ?>