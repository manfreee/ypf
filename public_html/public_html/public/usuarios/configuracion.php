<?php include($_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header-usuario.php');
?>
<link rel="stylesheet" href="/css/configuracion.css">

<link rel="stylesheet" href="/css/autenticacion.css">

<main>
    <div class="configuracion">
        <div class="borde-redondeado shadow seccion-configuracion-eleccion">
            <div class="eleccion-configuracion seleccionado" data-config-id="cuenta">
                <a class="pequenio light">Configuración de la cuenta</a>
            </div>
            <div class="eleccion-configuracion" data-config-id="panel">
                <a class="pequenio light">Configuración del panel</a>
            </div>
            <div class="eleccion-configuracion" data-config-id="modulos">
                <a class="pequenio light">Módulos</a>
            </div>
        </div>

        <div class="borde-redondeado shadow seccion-configuracion-especifica">

        </div>
    </div>
</main>
</div>
<script src="/js/configuracion.js"></script>
<script src="/js/mostrarModificar.js"></script>
<script src="/js/modificar.js"></script>

</body>

</html>