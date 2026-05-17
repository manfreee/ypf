document.addEventListener("DOMContentLoaded", () => {
    const contenedorConfiguracion = document.querySelector(".configuracion");
    const seccionEspecifica = document.querySelector(".seccion-configuracion-especifica");
    const seccionEleccion = document.querySelector(".seccion-configuracion-eleccion");

    const esMobile = () => window.innerWidth <= 768;

    // detectar clics dentro de seccionEspecifica para el botón volver
    seccionEspecifica.addEventListener("click", (e) => {
        const btnVolver = e.target.closest(".volver-atras");
        if (!btnVolver) return;

        // Accion al presionar volver
        seccionEspecifica.classList.remove("activo");
        seccionEleccion.classList.remove("oculto");
        seccionEspecifica.innerHTML = ""; // limpia el contenido cargado
    });

    // Manejo de los clics en las opciones del menu
    contenedorConfiguracion.addEventListener("click", (e) => {
        const item = e.target.closest(".eleccion-configuracion");
        if (!item) return;

        // Quitar selección anterior
        document.querySelectorAll(".eleccion-configuracion").forEach(el =>
            el.classList.remove("seleccionado")
        );
        item.classList.add("seleccionado");

        const opcion = item.dataset.configId;
        seccionEspecifica.innerHTML = `<p class="pequenio color-gris">Cargando...</p>`;

        fetch(`/public/usuarios/configuracion-eleccion.php?opcion=${encodeURIComponent(opcion)}`)
            .then(r => r.text())
            .then(html => {
                seccionEspecifica.innerHTML = html;

                if (esMobile()) {
                    seccionEleccion.classList.add("oculto");
                    seccionEspecifica.classList.add("activo");

                    const contenedorInfo = seccionEspecifica.querySelector(".contenedor-informacion");
                    if (contenedorInfo) contenedorInfo.classList.remove("oculto");
                }

                // Comportamiento especial para la seccion de modulos: redirigir al panel al hacer click
                if (opcion === "modulos") {
                    const contenedorModulos = seccionEspecifica.querySelector(".contenedor-modulos");
                    if (contenedorModulos) {
                        contenedorModulos.addEventListener("click", (ev) => {
                            const tarjeta = ev.target.closest(".modulo");
                            if (!tarjeta) return;
                            const idModulo = tarjeta.dataset.id;
                            if (!idModulo) return;
                            window.location.href = `/public/usuarios/panel-control.php?id_modulo=${encodeURIComponent(idModulo)}`;
                        });
                    }
                }
            })
            .catch(err => {
                seccionEspecifica.innerHTML = `<p class="error">Error al cargar la sección.</p>`;
                console.error(err);
            });
    });

    // Cargar la sección por defecto (solo desktop)
    if (!esMobile()) {
        fetch("/public/usuarios/configuracion-eleccion.php?opcion=cuenta")
            .then(r => r.text())
            .then(html => seccionEspecifica.innerHTML = html)
            .catch(console.error);
    }
});
