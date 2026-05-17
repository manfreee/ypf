    function mostrarFlotante(elemento) {
    const flotante = document.querySelector('.flotante');
    if (flotante) {
        flotante.classList.toggle('oculto');
        
        // Limpiar mensajes de error al abrir el formulario
        const mensajeError = flotante.querySelector('.contenedor-texto');
        if (mensajeError) {
            mensajeError.textContent = '';
            mensajeError.style.display = 'none';
        }
        
        // Enfocar el campo de código si existe
        const inputCodigo = flotante.querySelector('input[name="codigo"]');
        if (inputCodigo) {
            inputCodigo.focus();
        }
    }
}

function cerrarFlotante() {
    const flotante = document.querySelector('.flotante');
    if (flotante && !flotante.classList.contains('oculto')) {
        flotante.classList.add('oculto');
    }
}

// Delegación de eventos: escucha clicks en botones
document.addEventListener('click', (e) => {
    const botonMostrar = e.target.closest('.btn-mostrar-flotante');
    if (botonMostrar) {
        e.preventDefault();
        mostrarFlotante(botonMostrar);
    }

    const botonCancelar = e.target.closest('.btn-cancelar-modificacion');
    if (botonCancelar) {
        e.preventDefault();
        cerrarFlotante();
    }
    
    // Manejar envío del formulario de agregar módulo
    const formAgregarModulo = e.target.closest('.form-agregar-modulo');
    if (formAgregarModulo && e.target.matches('button[type="submit"]')) {
        e.preventDefault();
        const inputCodigo = formAgregarModulo.querySelector('input[name="codigo"]');
        const mensajeError = formAgregarModulo.querySelector('.contenedor-texto');
        const submitBtn = e.target;
        
        if (!inputCodigo) return;
        
        const codigo = inputCodigo.value.trim();
        
        // Validación básica
        if (!codigo) {
            mostrarMensaje(mensajeError, 'El código no puede estar vacío', 'error');
            return;
        }

        // Deshabilitar el botón de enviar
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="pequenio"><b class="color-blanco">Agregando...</b></span>';

        // Enviar formulario
        const formData = new FormData(formAgregarModulo);
        formData.append('agregar-modulo', 'true');
        
        fetch(formAgregarModulo.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.text();
        })
        .then(text => {
            if (!text) return;
            
            // Verificar si hay mensaje de error en la respuesta
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const errorElement = doc.querySelector('.contenedor-texto');
            
            if (errorElement && errorElement.textContent.trim()) {
                mostrarMensaje(mensajeError, errorElement.textContent.trim(), 'error');
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje(mensajeError, 'Error al procesar la solicitud', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
});

// Función para mostrar mensajes en el formulario
function mostrarMensaje(elemento, mensaje, tipo = 'error') {
    if (!elemento) return;
    
    elemento.textContent = mensaje;
    elemento.style.display = 'block';
    elemento.style.color = tipo === 'error' ? '#dc3545' : '#28a745';
    
    // Ocultar el mensaje después de 5 segundos
    setTimeout(() => {
        elemento.style.display = 'none';
    }, 5000);
}

// Cerrar el flotante si se hace click fuera de él
document.addEventListener('click', (e) => {
    const flotante = document.querySelector('.flotante');
    const botonMostrar = e.target.closest('.btn-mostrar-flotante');
    const dentroFlotante = e.target.closest('.flotante');

    if (!flotante) return;

    if (!flotante.classList.contains('oculto') && !dentroFlotante && !botonMostrar) {
        flotante.classList.add('oculto');
    }
});