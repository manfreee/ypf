<?php
/*Este código define la maquetación base del panel de usuario público. Carga el encabezado de la página, los estilos visuales de configuración y autenticación, y monta un menú lateral de navegación con pestañas (Configuración de la cuenta, Mis Módulos, Mis pedidos y una opción directa para Cerrar sesión). Al igual que el panel administrativo visto anteriormente, deja el contenedor .seccion-configuracion-especifica vacío para que los scripts de JavaScript inyecten dinámicamente el contenido de cada sección según la pestaña seleccionada, finalizando con la carga del pie de página global.*/

// Incluye el encabezado global de la aplicación web
include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php';
?>
<!-- Enlaces a las hojas de estilo CSS para el diseño del panel de usuario y formularios -->
<link rel="stylesheet" href="/css/configuracion.css">
<link rel="stylesheet" href="/css/autenticacion.css">

<main>
    <!-- Contenedor principal del panel de configuración de perfil de usuario -->
    <div class="configuracion usuario">
        <!-- Menú lateral / Pestañas de navegación de opciones del usuario -->
        <div class="borde-redondeado shadow seccion-configuracion-eleccion usuario">
            
            <!-- Opción: Configuración de la cuenta (Seleccionada por defecto) -->
            <div class="eleccion-configuracion seleccionado" data-config-id="cuenta">
                <a class="pequenio light">Configuracion de la Cuenta</a>
            </div>
            
            <!-- Opción: Mis Módulos -->
            <div class="eleccion-configuracion" data-config-id="modulos">
                <a class="pequenio light">Mis Modulos</a>
            </div>
            
            <!-- Opción: Mis pedidos -->
            <div class="eleccion-configuracion" data-config-id="pedidos">
                <a class="pequenio light">Mis pedidos</a>
            </div>
            
            <!-- Enlace directo para destruir la sesión y salir del sistema -->
            <a href="/public/public/funciones/cerrar-sesion.php" class="eleccion-configuracion">
                <p class="pequenio light">Cerrar sesión</p>
            </a>
        </div>

        <!-- Contenedor dinámico donde JavaScript cargará el contenido específico de cada pestaña -->
        <div class="borde-redondeado shadow seccion-configuracion-especifica usuario">

        </div>

    </div>
</main>
</div>

<!-- Scripts de JavaScript encargados de gestionar los eventos de interacción, modales y llamadas AJAX -->
<script src="/js/configuracion.js"></script>
<script src="/js/mostrarModificar.js"></script>
<script src="/js/mostrar-flotante.js"></script>
<script src="/js/modificar.js"></script>
<!-- <script src="/Luminora/assets/js/configuracion.js"></script>
<script src="/Luminora/assets/js/select.js"></script>
<script src="/Luminora/assets/js/ridirigir-panel.js"></script> -->

<!-- Carga del componente de pie de página global -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/footer.php'; ?>