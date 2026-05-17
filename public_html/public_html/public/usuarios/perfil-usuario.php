<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php';
    // include $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/configuracion/cuenta.php';
?>
<link rel="stylesheet" href="/css/configuracion.css">
<link rel="stylesheet" href="/css/autenticacion.css">



<main>
    <div class="configuracion usuario">
        <div class="borde-redondeado shadow seccion-configuracion-eleccion usuario">
            <div class="eleccion-configuracion seleccionado" data-config-id="cuenta">
                <a class="pequenio light">Configuracion de la Cuenta</a>
            </div>
            <div class="eleccion-configuracion" data-config-id="modulos">
                <a class="pequenio light">Mis Modulos</a>
            </div>
            <div class="eleccion-configuracion" data-config-id="pedidos">
                <a class="pequenio light">Mis pedidos</a>
            </div>
            <a href="/public/public/funciones/cerrar-sesion.php" class="eleccion-configuracion">
                <p class="pequenio light">Cerrar sesión</p>
            </a>
        </div>

        <div class="borde-redondeado shadow seccion-configuracion-especifica usuario">

        </div>


    </div>
</main>
</div>
<script src="/js/configuracion.js"></script>
<script src="/js/mostrarModificar.js"></script>
<script src="/js/mostrar-flotante.js"></script>
<script src="/js/modificar.js"></script>
<!-- <script src="/Luminora/assets/js/configuracion.js"></script>
<script src="/Luminora/assets/js/select.js"></script>
<script src="/Luminora/assets/js/ridirigir-panel.js"></script> -->

<?php include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/footer.php'; ?>