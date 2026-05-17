
const btnSumar = document.getElementById('sumar');
const btnRestar = document.getElementById('restar');
const inputCantidad = document.getElementById('cantidad-producto');

const MIN = 1;
const MAX = 100;

// Asegurar que siempre sea número válido
function limpiarValor() {
    let valor = parseInt(inputCantidad.value) || MIN;
    if (valor < MIN) valor = MIN;
    if (valor > MAX) valor = MAX;
    inputCantidad.value = valor;
}

btnSumar.addEventListener('click', () => {
    limpiarValor();
    let valor = parseInt(inputCantidad.value);
    if (valor < MAX) {
        inputCantidad.value = valor + 1;
    }
});

btnRestar.addEventListener('click', () => {
    limpiarValor();
    let valor = parseInt(inputCantidad.value);
    if (valor > MIN) {
        inputCantidad.value = valor - 1;
    }
});

// Evita que el usuario escriba letras o números fuera de rango
inputCantidad.addEventListener('input', limpiarValor);