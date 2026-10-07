<?php
/*Este script es el componente de configuración y gestión individual de un módulo. Se encarga de determinar qué módulo se va a gestionar (a través de la URL GET, la sesión o consultando el primero disponible en la base de datos) y verifica estrictamente que pertenezca al usuario en sesión. Muestra la información básica (número de serie y nombre), despliega un modal emergente para la edición del nombre mediante peticiones POST y ofrece la opción de eliminar el módulo previa confirmación con JavaScript.*/
// Incluye dependencias necesarias para la base de datos, sesiones y funciones helpers
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/modulo-funciones.php';

// Carga la hoja de estilos CSS para los modales emergentes
echo '<link rel="stylesheet" href="/css/modal.css">';

// Verifica que exista una sesión activa; si no, redirige al inicio de sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /public/usuarios/login.php');
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

/* --- RESOLUCIÓN DEL MÓDULO ACTIVO --- */

$id_modulo = null;

// 1. Intenta obtener el ID del módulo enviado por parámetro URL (GET)
if (isset($_GET['id_modulo']) && !empty($_GET['id_modulo'])) {
    $id_modulo = intval($_GET['id_modulo']);
} 
// 2. Si no viene por GET, verifica si existe guardado previamente en la sesión
else if (isset($_SESSION['id_modulo_actual']) && !empty($_SESSION['id_modulo_actual'])) {
    $id_modulo = intval($_SESSION['id_modulo_actual']);
}
// 3. Si no hay referencia, obtiene el primer módulo disponible perteneciente al usuario
else {
    $query = "SELECT id_modulo FROM modulos WHERE id_usuario = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_modulo = $row['id_modulo'];
        $_SESSION['id_modulo_actual'] = $id_modulo; // Lo guarda en la sesión activa
    } else {
        // Alerta al usuario si no posee ningún módulo configurado
        echo '<div class="alerta error">No tienes módulos configurados. <a href="/public/usuarios/configuracion/modulos.php">Agregar un módulo</a></div>';
        exit();
    }
    $stmt->close();
}

// Comprueba la propiedad del módulo por seguridad antes de permitir cualquier modificación
if (!verificarPropiedadModulo($conexion, $id_modulo, $usuario_id)) {
    echo '<div class="alerta error">No tienes permiso para acceder a este módulo o no existe.</div>';
    exit();
}

// Obtiene los datos descriptivos del módulo actual (Número de serie y Nombre)
$numero_serie = obtenerNumeroSerieModulo($conexion, $id_modulo, $usuario_id);
$nombre_modulo = '';

$stmt = $conexion->prepare("SELECT nombre FROM modulos WHERE id_modulo = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_modulo, $usuario_id);
$stmt->execute();
$stmt->bind_result($nombre_modulo);
$stmt->fetch();
$stmt->close();
?>

<!-- Botón para abrir el modal de edición de configuración -->
<button class="btn-primario btn-editar-configuracion">
    <iconify-icon icon="material-symbols:edit-rounded" class="color-blanco icon-h6"></iconify-icon>
</button>

<!-- Cuerpo informativo del módulo actual -->
<div class="form-cuerpo">
    <div class="info-modulo">
        <p class="pequenio">
            <b class="color-primario">Número de serie:</b> 
            <?php echo htmlspecialchars($numero_serie); ?>
        </p>
    </div>
    
    <div class="input-grupo">
        <p class="pequenio"><b class="color-primario negrita">Nombre del módulo</b></p>
        <input required id="nombre-modulo-actual" value="<?php echo htmlspecialchars($nombre_modulo); ?>" class="form-input" type="text" readonly />
    </div>
</div>

<!-- Botón de acción destructiva: Eliminar Módulo -->
<button class="btn btn-eliminar" id="btn-eliminar-modulo">
    <iconify-icon icon="material-symbols:delete-rounded" class="color-error icon-h6"></iconify-icon>
    <p class="pequenio medium color-error">Eliminar módulo</p>
</button>

<!-- Modal emergente para la modificación del nombre -->
<div id="modalEditar" class="modal oculto">
    <div class="modal-contenido">
        <div class="modal-encabezado">
            <h6 class="media-negrita color-primario">Modificar Nombre del Módulo</h6>
            <button type="button" class="btn-cerrar" onclick="cerrarModalModificar()">&times;</button>
        </div>
        <div class="modal-cuerpo">
            <!-- Formulario de actualización que envía los datos al controlador externo -->
            <form id="formEditarModulo" class="form-cuerpo-modificar" method="POST" action="/public/usuarios/funciones/actualizar-modulo.php">
                <input type="hidden" name="tipo" value="modulo">
                <input type="hidden" name="id_modulo" value="<?php echo $id_modulo; ?>">

                <div class="contenedor-inputs">
                    <div class="input-grupo">
                        <p class="pequenio"><b class="color-primario negrita">Nuevo nombre del módulo</b></p>
                        <input required name="nombre_modulo" id="input-nuevo-nombre" value="<?php echo htmlspecialchars($nombre_modulo); ?>" class="form-input" type="text" maxlength="100" />
                        <p class="pequenio color-gris">Máximo 100 caracteres</p>
                    </div>
                </div>

                <div class="contenedor-botones">
                    <button type="submit" name="actualizar_nombre" class="btn btn-primario">
                        Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secundario" onclick="cerrarModalModificar()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Carga del archivo JavaScript externo de utilidades -->
<script src="/js/modificar.js"></script>

<script>
// Función encargada de abrir y visualizar el modal
function abrirModalModificar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('visible');
        document.body.style.overflow = 'hidden'; // Bloquea el scroll de la página de fondo
    }
}

// Función encargada de ocultar el modal
function cerrarModalModificar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('visible');
        document.body.style.overflow = ''; // Restablece el scroll de la página
    }
}

// Event Listeners una vez cargado el DOM
document.addEventListener('DOMContentLoaded', function() {
    const btnEliminar = document.getElementById('btn-eliminar-modulo');
    const btnEditar = document.querySelector('.btn-editar-configuracion');
    
    // Vincula el click del botón de edición con la apertura del modal
    if (btnEditar) {
        btnEditar.addEventListener('click', function(e) {
            e.preventDefault();
            abrirModalModificar();
        });
    }
    
    // Permite cerrar el modal haciendo click en el área oscura externa
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModalModificar();
            }
        });
    }
    
    // Dispara el prompt de confirmación antes de redirigir a la eliminación
    if (btnEliminar) {
        btnEliminar.addEventListener('click', function(e) {
            e.stopPropagation();
            if (confirm('¿Estás seguro de que deseas eliminar este módulo? Esta acción no se puede deshacer.')) {
                window.location.href = '/public/usuarios/funciones/eliminar-modulo.php?id_modulo=<?php echo $id_modulo; ?>';
            }
        });
    }
});
</script>