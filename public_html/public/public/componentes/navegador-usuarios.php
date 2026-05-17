<?php
session_start();

$nombre_usuario   = htmlspecialchars($_SESSION['nombre'] ?? 'Usuario');
$apellido_usuario = htmlspecialchars($_SESSION['apellido'] ?? '');
$email_usuario    = htmlspecialchars($_SESSION['email'] ?? 'sin-email');

?>
<link rel="stylesheet" href="/css/navegador-usuario.css">
<div class="contenedor-comun">
    <header class="navegador-usuario">
        <nav class="navegador-superior">
            <iconify-icon icon="material-symbols:menu-rounded" class="icon icon-h6 abrir-menu color-blanco oculto"
                id="btn-desplegar"></iconify-icon>
            <div class="user-drop-down">
                <iconify-icon icon="solar:user-bold" class="icon icon-h6 btn-drop-down color-blanco"
                    id="btn-drop-down"></iconify-icon>
                <div class="shadow drop-down oculto">
                    <div class="contenedor-informacion-user">
                        <div class="nombre-user">
                            <p class="pequenio texto-truncado"><b><?php echo $nombre_usuario ?></b></p>
                            <p class="pequenio texto-truncado"><b><?php echo $apellido_usuario ?></b></p>
                        </div>
                        <p class="muy-pequenio light texto-truncado"><?php echo $email_usuario ?></p>
                    </div>
                    <a href="/public/public/funciones/cerrar-sesion.php" class="pequenio light">Cerrar sesión</a>
                </div>
            </div>
        </nav>
        <asaide class="navegador-lateral" id="desplegable">
            <a href="/public/usuarios/panel-control.php" title="Panel de control">
                <iconify-icon icon="icon-park-solid:system" class="icon icon-h6 color-primario"></iconify-icon>
                <span class="oculto pequenio light color-blanco">Panel de control</span>
            </a>

            <!-- <a href="" title="Estadisticas">
                <iconify-icon icon="fluent:data-area-32-filled" class="icon icon-h6 color-primario"></iconify-icon>
                <span class="oculto pequenio light color-blanco">Estadisticas</span>
            </a>

            <a href="" title="Energía">
                <iconify-icon icon="ix:electrical-energy-filled" class="icon icon-h6 color-primario"></iconify-icon>
                <span class="oculto pequenio light color-blanco">Energia</span>
            </a> -->


            <a href="/public/usuarios/configuracion.php" title="Configuración">
                <iconify-icon icon="material-symbols:settings-rounded" class="icon icon-h6 color-primario"></iconify-icon>
                <span class="oculto pequenio light color-blanco">Configuración</span>
            </a>
            <a href="" title="Cerrar sesión" class="btn-responsive cerrar-sesion oculto">
                <iconify-icon icon="streamline:logout-1-solid" class="icon icon-h6 color-primario"></iconify-icon>
                <span class="oculto pequenio light color-blanco">Cerrar sesión</span>
            </a>
        </asaide>
    </header>

    <script src="/js/desplegar.js"></script>
    <script src="/js/dropDown.js"></script>
</body>

</html>