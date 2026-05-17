document.addEventListener('DOMContentLoaded', function() {
    const productosEspecificos = document.querySelectorAll('.producto-especifico');
    
    productosEspecificos.forEach(productoDiv => {
        const cantidadInput = productoDiv.querySelector('.cantidad-input');
        const btnRestar = productoDiv.querySelector('#restar');
        const btnSumar = productoDiv.querySelector('#sumar');
        
        if (!cantidadInput || !btnRestar || !btnSumar) return;
        
        const idProducto = cantidadInput.dataset.idProducto;
        const incluirServicio = cantidadInput.dataset.incluirServicio;
        
        // ============================================
        function actualizarCantidad(nuevaCantidad) {
            fetch('/Luminora/php/funciones/actualizar-cantidad.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_producto: idProducto,
                    incluir_servicio: incluirServicio,
                    cantidad: nuevaCantidad
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Error al actualizar:', data.mensaje);
                    alert('Error al actualizar la cantidad');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }
        
        // ============================================
        btnRestar.addEventListener('click', function() {
            let cantidad = parseInt(cantidadInput.value);
            if (cantidad > 1) {
                cantidad--;
                cantidadInput.value = cantidad;
                actualizarCantidad(cantidad);
            }
        });
        
        // ============================================
        btnSumar.addEventListener('click', function() {
            let cantidad = parseInt(cantidadInput.value);
            cantidad++;
            cantidadInput.value = cantidad;
            actualizarCantidad(cantidad);
        });
        
        // ============================================
        cantidadInput.addEventListener('change', function() {
            let cantidad = parseInt(this.value);
            if (cantidad < 1 || isNaN(cantidad)) {
                cantidad = 1;
                this.value = cantidad;
            }
            actualizarCantidad(cantidad);
        });
    });
});