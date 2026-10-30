<?php include __DIR__ . '/../funciones/session.php'; ?>
<link rel="stylesheet" href="/css/navegador.css">
<header class="navegador cont">
    <!-- <a href="/public/funciones/cerrar-sesion.php" class="color-blanco">Cerrar sesion - testeo</a> -->
    <div class="cont-menu-responsive">
        <iconify-icon icon="material-symbols:menu-rounded" class="icon oculto color-blanco icon-h6 abrir-menu"
            id="btn-desplegar"></iconify-icon>
        <a href="/index.php" class="imagen-logo-link">
            <p><b class="nulshock color-blanco">NecoTracking</b></p>
        </a>
        <?php if ($sesion_activa): ?>
            <!-- Estos accesos aparecen en la versión móvil cuando hay una sesión activa. -->
            <div class="contenedor-btns-responsive oculto">
                <a href="/public/usuarios/funciones/perfil-usuario.php">
                    <iconify-icon icon="solar:user-bold" class="icon icon-h6 color-blanco"></iconify-icon>
                </a>

                <a href="/public/usuarios/carrito.php">
                    <iconify-icon icon="mynaui:shopping-bag-solid" class="icon icon-h6 color-blanco"></iconify-icon>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- Menú principal con enlaces a las secciones públicas. -->
    <nav class="menu-nav" id="desplegable">
        <div class="sub-menu">
            <iconify-icon icon="material-symbols:close-rounded" class="icon icon-h6 oculto cerrar-menu"
                id="cerrar-desplegable"></iconify-icon>
            <a href="/index.php" class="color-blanco pequenio light-swiss">Inicio</a>
            <a href="/public/funcionamiento.php" class="color-blanco pequenio light-swiss">Funcionamiento</a>
            <a href="/public/tienda.php" class="color-blanco pequenio light-swiss">Comprar
                Modulo</a>
            <a href="public/sobre-nosotros.php" class="color-blanco pequenio light-swiss">Sobre nosotros</a>
        </div>
        <div class="cont-btns">
            <?php if (!$sesion_activa): ?>
                <!-- Los visitantes no autenticados pueden iniciar sesión o registrarse. -->
                <a href="public/iniciar-sesion.php"
                    class="negrita-swiss btn pequenio texto-truncado">Iniciar sesión</a>
                <a href="public/registrarse.php"
                    class="negrita-swiss color-primario btn btn-primario pequenio oculto">Registrarme</a>
                <!-- <a href="" class="btn-movil registrarse"><b class="color-blanco">Registrarse</b></a> -->
            <?php endif; ?>
        </div>
    </nav>
    <!-- Accesos de perfil y carrito para usuarios autenticados en escritorio. -->
    <div class="cont-btns">
        <?php if ($sesion_activa): ?>
            <a href="/usuarios/perfil-usuario.php">
                <iconify-icon icon="solar:user-bold" class="icon icon-h6 color-blanco"></iconify-icon>
            </a>

            <a href="/public/usuarios/carrito.php">
                <iconify-icon icon="mynaui:shopping-bag-solid" class="icon icon-h6 color-blanco"></iconify-icon>
            </a>
        <?php endif; ?>
    </div>
</header>
<!-- Controla la apertura y el cierre del menú responsive. -->
<script src="/js/desplegar.js"></script>