<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/modulo-funciones.php';

// Agregar el CSS del modal
echo '<link rel="stylesheet" href="/css/modal.css">';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /public/usuarios/login.php');
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

// Verificar si se proporcionó un ID de módulo
$id_modulo = null;

// Primero intentar obtener el ID del módulo de la URL
if (isset($_GET['id_modulo']) && !empty($_GET['id_modulo'])) {
    $id_modulo = intval($_GET['id_modulo']);
} 
// Si no está en la URL, verificar si hay un módulo en la sesión
else if (isset($_SESSION['id_modulo_actual']) && !empty($_SESSION['id_modulo_actual'])) {
    $id_modulo = intval($_SESSION['id_modulo_actual']);
}
// Si aún no hay módulo, redirigir a la lista de módulos
else {
    // Intentar obtener el primer módulo del usuario
    $query = "SELECT id_modulo FROM modulos WHERE id_usuario = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_modulo = $row['id_modulo'];
        $_SESSION['id_modulo_actual'] = $id_modulo; // Guardar en sesión
    } else {
        echo '<div class="alerta error">No tienes módulos configurados. <a href="/public/usuarios/configuracion/modulos.php">Agregar un módulo</a></div>';
        exit();
    }
    $stmt->close();
}

// Verificar si el módulo existe y pertenece al usuario
if (!verificarPropiedadModulo($conexion, $id_modulo, $usuario_id)) {
    echo '<div class="alerta error">No tienes permiso para acceder a este módulo o no existe.</div>';
    exit();
}

// Obtener el número de serie y nombre del módulo actual
$numero_serie = obtenerNumeroSerieModulo($conexion, $id_modulo, $usuario_id);
$nombre_modulo = '';

// Obtener el nombre actual del módulo
$stmt = $conexion->prepare("SELECT nombre FROM modulos WHERE id_modulo = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_modulo, $usuario_id);
$stmt->execute();
$stmt->bind_result($nombre_modulo);
$stmt->fetch();
$stmt->close();
?>

<!-- Botón de editar -->
<button class="btn-primario btn-editar-configuracion">
    <iconify-icon icon="material-symbols:edit-rounded" class="color-blanco icon-h6"></iconify-icon>
</button>

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

<button class="btn btn-eliminar" id="btn-eliminar-modulo">
    <iconify-icon icon="material-symbols:delete-rounded" class="color-error icon-h6"></iconify-icon>
    <p class="pequenio medium color-error">Eliminar módulo</p>
</button>

<!-- Modal para editar módulo -->
<div id="modalEditar" class="modal oculto">
    <div class="modal-contenido">
        <div class="modal-encabezado">
            <h6 class="media-negrita color-primario">Modificar Nombre del Módulo</h6>
            <button type="button" class="btn-cerrar" onclick="cerrarModalModificar()">&times;</button>
        </div>
        <div class="modal-cuerpo">
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

<!-- Incluir el archivo de funciones de módulo -->
<script src="/js/modificar.js"></script>

<script>
// Función para mostrar el modal
function abrirModalModificar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('visible');
        document.body.style.overflow = 'hidden'; // Prevenir scroll del fondo
    }
}

// Función para cerrar el modal
function cerrarModalModificar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('visible');
        document.body.style.overflow = ''; // Restaurar scroll
    }
}

// Manejar la eliminación del módulo
document.addEventListener('DOMContentLoaded', function() {
    const btnEliminar = document.getElementById('btn-eliminar-modulo');
    const btnEditar = document.querySelector('.btn-editar-configuracion');
    
    // Configurar el botón de editar
    if (btnEditar) {
        btnEditar.addEventListener('click', function(e) {
            e.preventDefault();
            abrirModalModificar();
        });
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModalModificar();
            }
        });
    }
    
    // Confirmación para eliminar módulo
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