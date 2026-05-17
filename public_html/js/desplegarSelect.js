document.addEventListener('DOMContentLoaded', () => {
    // Seleccionamos todos los selects
    const contenedoresSelect = document.querySelectorAll('.contenedor-select');

    contenedoresSelect.forEach(contenedor => {
        const header = contenedor.querySelector('.header-select');
        const contenido = contenedor.querySelector('.contenido-select');
        const headerTexto = header.querySelector('p');
        const opciones = contenido.querySelectorAll('p');

        // Botones responsive
        const botonDesplegar = document.querySelector('.desplegar-select'); // botón móvil (puede estar fuera del contenedor)
        const botonCerrar = contenedor.querySelector('.cerrar-select');

        // Función para abrir select (mobile)
        const abrirSelect = () => {
            contenedor.classList.add('mostrar-flex');
            contenido.classList.remove('oculto');
            contenido.classList.add('mostrar-flex');
        };

        // Función para cerrar select (desktop y mobile)
        const cerrarSelect = () => {
            contenedor.classList.remove('mostrar-flex');
            contenido.classList.remove('mostrar-flex');
            contenido.classList.add('oculto');
        };

        // ---- SELECT NORMAL (desktop) ----
        header.addEventListener('click', e => {
            e.stopPropagation();
            contenido.classList.toggle('mostrar-flex');
            contenido.classList.toggle('oculto');
        });

        // ---- SELECCIONAR OPCIÓN ----
        opciones.forEach(opcion => {
            opcion.addEventListener('click', e => {
                e.stopPropagation();
                headerTexto.textContent = opcion.textContent;
                cerrarSelect();
            });
        });

        // ---- BOTÓN DESPLEGAR (mobile) ----
        if (botonDesplegar) {
            botonDesplegar.addEventListener('click', e => {
                e.stopPropagation();
                abrirSelect();
            });
        }

        // ---- BOTÓN CERRAR (mobile) ----
        if (botonCerrar) {
            botonCerrar.addEventListener('click', e => {
                e.stopPropagation();
                cerrarSelect();
            });
        }

        // ---- CERRAR AL HACER CLICK FUERA ----
        document.addEventListener('click', e => {
            if (!contenedor.contains(e.target) && e.target !== botonDesplegar) {
                cerrarSelect();
            }
        });

        // ---- CERRAR AL HACER SCROLL ----
        window.addEventListener('scroll', cerrarSelect);
    });
});
