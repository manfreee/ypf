document.addEventListener("DOMContentLoaded", () => {
    const contenedorConfiguracion = document.querySelector(".configuracion");
    const seccionEspecifica = document.querySelector(".seccion-configuracion-especifica");
    const seccionEleccion = document.querySelector(".seccion-configuracion-eleccion");

    const esMobile = () => window.innerWidth <= 768;

    seccionEspecifica.addEventListener("click", (e) => {
        const btnVolver = e.target.closest(".volver-atras");
        if (!btnVolver) return;

        seccionEspecifica.classList.remove("activo");
        seccionEleccion.classList.remove("oculto");
        seccionEspecifica.innerHTML = "";
    });

    contenedorConfiguracion.addEventListener("click", (e) => {
        const item = e.target.closest(".eleccion-configuracion");
        if (!item) return;

        document.querySelectorAll(".eleccion-configuracion").forEach(el =>
            el.classList.remove("seleccionado")
        );

        if (!esMobile()) {
            item.classList.add("seleccionado");
        }

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
            })
            .catch(err => {
                seccionEspecifica.innerHTML = `<p class="error">Error al cargar la sección.</p>`;
                console.error(err);
            });
    });

    if (!esMobile()) {
        fetch("/public/usuarios/configuracion-eleccion.php?opcion=cuenta")
            .then(r => r.text())
            .then(html => seccionEspecifica.innerHTML = html)
            .catch(console.error);
    }
});
