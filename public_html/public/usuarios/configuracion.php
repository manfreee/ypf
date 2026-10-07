<?php
/*Este código define la estructura HTML base de la interfaz de configuración del usuario. Se encarga de incluir los estilos globales y componentes del encabezado, construir un menú lateral dinámico con pestañas (Cuenta, Panel y Módulos) identificado mediante atributos data-config-id, y dejar un contenedor vacío (.seccion-configuracion-especifica) donde el archivo JavaScript cargará dinámicamente el contenido de cada sección según la opción seleccionada.*/

// Incluye el encabezado específico de la interfaz de usuario autenticado
include($_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header-usuario.php');
?>

<!-- Enlaces a las hojas de estilo CSS para el diseño de la pantalla de configuración y formularios -->
<link rel="stylesheet" href="/css/configuracion.css">
<link rel="stylesheet" href="/css/autenticacion.css">

<main>
    <!-- Contenedor principal del panel de configuración -->
    <div class="configuracion">
        
        <!-- Menú de navegación/pestañas laterales para elegir la sección -->
        <div class="borde-redondeado shadow seccion-configuracion-eleccion">
            
            <!-- Opción: Configuración de la cuenta (Marcada como seleccionada por defecto) -->
            <div class="eleccion-configuracion seleccionado" data-config-id="cuenta">
                <a class="pequenio light">Configuración de la cuenta</a>
            </div>
            
            <!-- Opción: Configuración del panel -->
            <div class="eleccion-configuracion" data-config-id="panel">
                <a class="pequenio light">Configuración del panel</a>
            </div>
            
            <!-- Opción: Módulos -->
            <div class="eleccion-configuracion" data-config-id="modulos">
                <a class="pequenio light">Módulos</a>
            </div>
        </div>

        <!-- Contenedor dinámico donde se inyectará el contenido HTML mediante AJAX o JavaScript -->
        <div class="borde-redondeado shadow seccion-configuracion-especifica">

        </div>
    </div>
</main>
</div>

<!-- Scripts de JavaScript encargados de la interactividad, la carga dinámica y la modificación de datos -->
<script src="/js/configuracion.js"></script>
<script src="/js/mostrarModificar.js"></script>
<script src="/js/modificar.js"></script>

</body>

</html>