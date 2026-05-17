document.addEventListener("DOMContentLoaded", () => {
    const btnDropDown = document.getElementById("btn-drop-down");
    const dropDown = document.querySelector(".user-drop-down .drop-down");

    if (!btnDropDown || !dropDown) return; // seguridad

    // Abrir / cerrar menu al hacer clic en el ícono de usuario
    btnDropDown.addEventListener("click", (e) => {
        e.stopPropagation();

        // Alterna las clases
        dropDown.classList.toggle("mostrar-flex");
        dropDown.classList.toggle("oculto");
    });

    // Cerrar al hacer clic fuera del menú
    document.addEventListener("click", (e) => {
        const clicFuera = !dropDown.contains(e.target) && e.target !== btnDropDown;
        if (clicFuera && dropDown.classList.contains("mostrar-flex")) {
            dropDown.classList.remove("mostrar-flex");
            dropDown.classList.add("oculto");
        }
    });

    // Cerrar al hacer scroll
    window.addEventListener("scroll", () => {
        if (dropDown.classList.contains("mostrar-flex")) {
            dropDown.classList.remove("mostrar-flex");
            dropDown.classList.add("oculto");
        }
    });
});
