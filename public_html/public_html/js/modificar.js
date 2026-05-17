// Debug: verificar que el archivo se cargó
console.log('modificar.js cargado correctamente');

// Función para abrir el modal
function abrirModalModificar() {
    const modal = document.getElementById('modalEditar');
    console.log('abrirModalModificar -> modal:', modal);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('oculto');
    }
}

// Función para cerrar el modal
function cerrarModalModificar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('oculto');
    }
}

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado');
    
    // Botón para abrir el modal
    const btnEditar = document.querySelector('.btn-editar-configuracion'); //getElementById('cacona')
    console.log('Botón editar:', btnEditar);
    
    if (btnEditar) {
        btnEditar.addEventListener('click', function(e) {
            console.log('Botón de editar clickeado');
            e.preventDefault();
            abrirModal();
        });
    }
});
// Delegación de eventos global (sirve aunque el HTML se inyecte por AJAX)
document.addEventListener('click', (e) => {
    const target = e.target;

    // Abrir modal al presionar el botón editar
    const botonEditar = target.closest('.btn-editar-configuracion');
    if (botonEditar) {
        console.log('Click en .btn-editar-configuracion');
        e.preventDefault();
        abrirModalModificar();
        return;
    }

    // Cerrar con el botón cancelar dentro del modal
    const botonCancelar = target.closest('.btn-cancelar-modificacion');
    if (botonCancelar) {
        console.log('Click en .btn-cancelar-modificacion');
        e.preventDefault();
        cerrarModalModificar();
        return;
    }

    const modal = document.getElementById('modalEditar');
    if (!modal) return;

    // Si el modal está oculto, no hacer nada más
    if (modal.classList.contains('oculto')) return;

    // Cerrar clickeando en el overlay (fuera del contenido)
    if (target === modal) {
        console.log('Click en overlay del modal');
        cerrarModalModificar();
        return;
    }
});

// Manejar envío del formulario de edición de cuenta por AJAX
document.addEventListener('submit', (e) => {
    const form = e.target.closest('#form-editar-cuenta');
    if (!form) return;

    e.preventDefault();

    const btnGuardar = form.querySelector('#btn-guardar-cuenta');
    if (!btnGuardar) return;

    // Crear / obtener contenedor de mensajes dentro del modal
    let mensajeRespuesta = form.querySelector('#mensaje-respuesta');
    if (!mensajeRespuesta) {
        mensajeRespuesta = document.createElement('div');
        mensajeRespuesta.id = 'mensaje-respuesta';
        mensajeRespuesta.className = 'oculto';
        form.insertBefore(mensajeRespuesta, form.firstChild);
    }

    const mostrarMensaje = (mensaje, tipo) => {
        mensajeRespuesta.innerHTML = mensaje;
        mensajeRespuesta.className = tipo === 'exito' ? 'alerta exito' : 'alerta error';
        mensajeRespuesta.classList.remove('oculto');

        setTimeout(() => {
            mensajeRespuesta.classList.add('oculto');
        }, 5000);
    };

    btnGuardar.disabled = true;
    const textoOriginal = btnGuardar.textContent;
    btnGuardar.textContent = 'Guardando...';

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
        .then(r => r.json())
        .then(data => {
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoOriginal;

            if (data && data.ok) {
                mostrarMensaje('Los cambios se guardaron correctamente', 'exito');

                // Actualizar los inputs de solo lectura con los nuevos valores
                const formData = new FormData(form);
                formData.forEach((valor, campo) => {
                    const inputLectura = document.querySelector(`.seccion-configuracion-lectura input[name="${campo}"]`);
                    if (inputLectura) {
                        inputLectura.value = valor;
                    }
                });

                // Cerrar modal después de un breve tiempo
                setTimeout(() => {
                    cerrarModalModificar();
                }, 1500);
            } else {
                const errorMsg = data && data.errores
                    ? data.errores.join('<br>')
                    : 'Ocurrió un error al guardar los cambios';
                mostrarMensaje(errorMsg, 'error');
            }
        })
        .catch(err => {
            console.error('Error al guardar la cuenta:', err);
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoOriginal;
            mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
        });
});

