document.addEventListener("DOMContentLoaded", () => {
    const btnDesplegar = document.getElementById("btn-desplegar");
    const contenedor = document.getElementById("desplegable");
    const btnCerrar = document.getElementById("cerrar-desplegable");

    // Cerrar al hacer clic fuera del menú
    document.addEventListener("click", (e) => {
        const clicFuera = !contenedor.contains(e.target) && e.target !== btnDesplegar;
        if (clicFuera) {
            contenedor.classList.remove("activo");
            btnDesplegar.setAttribute("icon", "material-symbols:menu-rounded");
        }
    });

    // cerrar menú al hacer clic en el botón de cerrar (navegador)
    if (btnCerrar) {
        btnCerrar.addEventListener("click", (e) => {
            e.stopPropagation();
            contenedor.classList.remove("activo");
            btnDesplegar.setAttribute("icon", "material-symbols:menu-rounded");
        });
    }

    // Abrir / cerrar menú al hacer clic en el ícono principal (navagador-usuario)
    btnDesplegar.addEventListener("click", (e) => {
        e.stopPropagation();
        contenedor.classList.toggle("activo");

        // Cambia el icono según el estado
        if (contenedor.classList.contains("activo")) {
            btnDesplegar.setAttribute("icon", "material-symbols:close-rounded");
        } else {
            btnDesplegar.setAttribute("icon", "material-symbols:menu-rounded");
        }
    });

});
