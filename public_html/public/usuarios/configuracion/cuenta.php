<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /iniciar-sesion.php');
    exit();
}

$usuario_id = (int)$_SESSION['id_usuario'];

// Consulta preparada para mayor seguridad
$stmt = $conexion->prepare("
    SELECT nombre, apellido, email, localidad, provincia, pais 
    FROM usuarios 
    WHERE id_usuario = ? 
    LIMIT 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $usuario = [
        'nombre' => '',
        'apellido' => '',
        'email' => '',
        'localidad' => '',
        'provincia' => '',
        'pais' => ''
    ];
}
?>

<!-- TÍTULO Y BOTÓN EDITAR -->
<div class="contenedor-informacion oculto" id="btn-editar">
    <iconify-icon icon="ion:chevron-back-outline" class="icon-h6 volver-atras"></iconify-icon>
    <h5 class="negrita-swiss color-primario">Cuenta</h5>
</div>

<button class="btn-primario btn-editar-configuracion" id="cacona" type="button">
    <iconify-icon icon="material-symbols:edit-rounded" class="color-blanco icon-h6"></iconify-icon>
</button>

<!-- SECCIÓN SOLO LECTURA -->
<div class="form-cuerpo seccion-configuracion-lectura">
    <?php foreach ($usuario as $campo => $valor): ?>
        <div class="input-grupo">
            <p class="pequenio"><b class="color-primario negrita"><?= ucfirst($campo) ?></b></p>
            <input required class="form-input" type="text"
                   name="<?= $campo ?>"
                   value="<?= htmlspecialchars($valor ?? '') ?>" readonly />
        </div>
    <?php endforeach; ?>
</div>

<!-- MODAL DE EDICIÓN -->
<div id="modalEditar" class="modal-overlay oculto" 
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5); display: none;
            justify-content: center; align-items: center; z-index: 1000;">

    <div class="modal-contenido borde-redondeado shadow"
         style="background: white; padding: 20px; border-radius: 8px;
                width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;">

        <h6 class="media-negrita color-primario">Modificar cuenta</h6>

        <form id="form-editar-cuenta" class="form-cuerpo-modificar"
              method="POST" action="/public/usuarios/configuracion/guardar-cuenta.php">

            <?php foreach ($usuario as $campo => $valor): ?>
                <div class="input-grupo">
                    <p class="pequenio"><b class="color-primario negrita"><?= ucfirst($campo) ?></b></p>
                    <input required class="form-input" type="text"
                           name="<?= $campo ?>"
                           value="<?= htmlspecialchars($valor ?? '') ?>" />
                </div>
            <?php endforeach; ?>

            <div class="contenedor-botones">
                <button type="submit" id="btn-guardar-cuenta" class="btn btn-primario">
                    Guardar Cambios
                </button>
                <button type="button" class="btn btn-cancelar-modificacion">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>