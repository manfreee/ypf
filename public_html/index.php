<?php /*include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/header.php'; ?>*/
include ('public/public/componentes/header.php'); ?>
<link rel="stylesheet" href="css/index.css">
<header class="header">
    <div class="circulo"></div>
    <div class="texto-decorativo">
        <h3 class="color-blanco rockstart">TRANSICIÓN ENERGÉTICA, FUTURO SUSTENTABLE</h3>
        <div class="linea"></div>
        <p class="grande ethnocentric color-blanco titulo-principal">NECOTRACKING</p>
    </div>
    <div class="cont-img">
        <picture>
            <!-- <source srcset="/css/recursos/img/panel-2.png" media="(max-width: 480px)"> -->
            <img src="css/recursos/img/panel-1.v3.png" alt="Descripción">
        </picture>
    </div>
</header>

<section class="section-uno">
    <div class="subtitulo">
        <div class="fila">
            <span class="texto grande color-primario ethnocentric">Futuro</span>
            <span class="linea"></span>
        </div>
        <div class="fila">
            <span class="linea"></span>
            <span class="texto grande color-secundario ethnocentric">Brillante</span>
        </div>
    </div>
    <div class="contenedor-informacion">
        <div class="cont-img">
            <img src="css/recursos/img/panel-2.png">
        </div>
        <div class="informacion-especifica">
            <p class="color-primario nulshock">NecoTracking es un sistema inteligente que permite monitorear en tiempo
                real el rendimiento de tus módulos solares, detectando fallas, caídas de energía o variaciones en el
                comportamiento del sistema.</p>
            <p class="pequenio light-swiss">Gracias a nuestra tecnología desarrollada en Necochea, podés visualizar el
                estado de tus paneles desde tu celular, recibir alertas automáticas y optimizar el uso de la energía
                solar. Esto ayuda a prolongar la vida útil de la instalación, mejorar la eficiencia y reducir costos
                innecesarios.
                Nuestro objetivo es acercar soluciones accesibles que acompañen la transición hacia un consumo
                energético más responsable y sustentable.</p>
            <a href="public/public/tienda.php" class="btn primario">
                <iconify-icon icon="famicons:bag" class="color-blanco icon-h6"></iconify-icon>
                <span class="negrita-swiss color-blanco">Comprar</span>
            </a>
        </div>
    </div>
</section>

<section class="section-dos">
    <div class="subtitulo">
        <span class="texto grande color-primario ethnocentric">Tecnología local</span>
        <span class="texto grande color-secundario ethnocentric">Resultados reales</span>
    </div>
    <div class="contenedor-informacion">

        <div class="cont-img">
            <img src="css/recursos/img/panel-3.png">
        </div>


        <div class="informacion">
            <div class="contenedor-izquierda">
                <p class="color-blanco nulshock pequenio">Nuestros módulos están equipados con sensores que registran
                    tensión, corriente, temperatura y estado general del panel.</p>
                <p class="pequenio color-blanco light-swiss">Toda la información se envía automáticamente a nuestra
                    plataforma, donde el usuario puede visualizar el rendimiento, el historial y los cambios detectados.
                    Cuando ocurre una anomalía como una caída de energía, sobrecalentamiento o desconexión el sistema
                    genera una alerta inmediata por correo electrónico.
                    Esto permite actuar rápido y evitar daños mayores en la instalación solar.</p>
                <a href="public/public/funcionamiento.php" class="btn secundario">
                    <span class="negrita-swiss color-blanco">Ver funcionamiento</span>
                </a>
            </div>
            <div class="contenedor-derecha">
                <div class="contenedor-superior">
                    <iconify-icon icon="simple-line-icons:energy" class="icon color-blanco icon-h5"></iconify-icon>

                    <iconify-icon icon="ph:sun" class="icon color-blanco icon-h5"></iconify-icon>

                    <iconify-icon icon="hugeicons:solar-power" class="icon color-blanco icon-h5"></iconify-icon>

                </div>
                <p class="contenedor-inferior color-blanco pequenio light-swiss">
                    En Necochea se desaprovecha gran parte del potencial solar. Nuestra tecnología ayuda a aprovecharlo
                    mejor, optimizando el rendimiento y evitando pérdidas en cada instalación.
                </p>



            </div>
        </div>
    </div>
</section>

<section class="section-tres">
    <div class="contenedor-izquierda">
        <div class="burbuja uno">
            <h4 class="negrita-swiss">89.000</h4>
            <p class="pequenio">Habitantes en Necochea</p>
        </div>
        <div class="burbuja dos">
            <h4 class="negrita-swiss">49.557</h4>
            <p class="pequenio">Modulos en Necochea por familia</p>
        </div>
        <div class="burbuja tres">
            <h4 class="negrita-swiss">27.875kWh</h4>
            <p class="pequenio">Generados en total</p>
        </div>
    </div>
    <div class="contenedor-derecha">
        <div class="informacion">
            <p class="color-primario nulshock">En NecoTracking creemos que la transición energética comienza con
                información clara y accesible. Nuestro sistema ayuda a usuarios, hogares y pequeños emprendimientos a
                gestionar de forma eficiente su energía solar.</p>
            <p class="pequenio light-swiss">Desarrollado en Necochea, el proyecto busca acompañar el crecimiento de las
                tecnologías renovables en la región.
                Con herramientas simples, seguras y fáciles de usar, brindamos una solución que conecta a las personas
                con su propia energía, promoviendo un futuro más sustentable y moderno para la ciudad.</p>
        </div>
        <div class="cont-img">
            <img class="borde-redondeado" src="css/recursos/img/vista-aerea-de-necochea.webp">
        </div>
    </div>
</section>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/public/public/componentes/footer.php'; ?>