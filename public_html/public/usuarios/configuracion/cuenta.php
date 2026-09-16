<?php
/*Este código actúa como el módulo de vista parcial para la sección "Cuenta" dentro del panel de usuario. Realiza una consulta SQL a la tabla usuarios para obtener la información personal (nombre, apellido, email, localidad, provincia, pais) correspondiente al usuario con sesión activa. Presenta los datos de dos formas: en modo solo lectura (readonly) para visualización inmediata y dentro de un modal flotante de edición con un formulario que procesa los cambios dinámicamente mediante la acción /public/usuarios/configuracion/guardar-cuenta.php.*/
// Incluye el archivo de conexión a la base de datos de manera única
include_once($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Inicia o recupera la sesión activa
session_start();

// Obtiene el identificador del usuario guardado en la variable de sesión
$usuario_id = $_SESSION['id_usuario'];

// Consulta SQL para obtener los datos personales e información geográfica del usuario
$query = "SELECT nombre, apellido, email, localidad, provincia, pais 
          FROM usuarios 
          WHERE id_usuario = '$usuario_id' LIMIT 1";

// Ejecuta la consulta en la base de datos
$resultado = mysqli_query($conexion, $query);

// Extrae el registro obtenido como un arreglo asociativo ($campo => $valor)
$usuario = mysqli_fetch_assoc($resultado);
?>

<!-- TÍTULO Y BOTÓN EDITAR -->
<!-- Botón superior/encabezado de la sección, oculto por defecto -->
<div class="contenedor-informacion oculto" id="btn-editar">
    <iconify-icon icon="ion:chevron-back-outline" class="icon-h6 volver-atras"></iconify-icon>
    <h5 class="negrita-swiss color-primario">Cuenta</h5>
</div>

<!-- Botón flotante/acción para desplegar el modal de edición -->
<button class="btn-primario btn-editar-configuracion" id="cacona" type="button">
    <iconify-icon icon="material-symbols:edit-rounded" class="color-blanco icon-h6"></iconify-icon>
</button>

<!-- SECCIÓN SOLO LECTURA -->
<!-- Renderiza dinámicamente todos los campos del usuario con entradas de texto bloqueadas -->
<div class="form-cuerpo seccion-configuracion-lectura">
    <?php foreach ($usuario as $campo => $valor): ?>
        <div class="input-grupo">
            <!-- Muestra la clave del array capitalizada (ej: 'nombre' -> 'Nombre') -->
            <p class="pequenio"><b class="color-primario negrita"><?= ucfirst($campo) ?></b></p>
            <!-- Entrada de lectura con escape de caracteres especiales para evitar vulnerabilidades XSS -->
            <input required class="form-input" type="text"
                   name="<?= $campo ?>"
                   value="<?= htmlspecialchars($valor) ?>" readonly />
        </div>
    <?php endforeach; ?>
</div>

<!-- MODAL DE EDICIÓN -->
<!-- Capa superpuesta del modal (inicialmente oculta con display: none y clase oculto) -->
<div id="modalEditar" class="modal-overlay oculto" 
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5); display: none;
            justify-content: center; align-items: center; z-index: 1000;">

    <!-- Contenedor blanco flotante con scroll interno en pantallas pequeñas -->
    <div class="modal-contenido borde-redondeado shadow"
         style="background: white; padding: 20px; border-radius: 8px;
                width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;">

        <h6 class="media-negrita color-primario">Modificar cuenta</h6>

        <!-- Formulario POST para enviar las modificaciones al script 'guardar-cuenta.php' -->
        <form id="form-editar-cuenta" class="form-cuerpo-modificar"
              method="POST" action="/public/usuarios/configuracion/guardar-cuenta.php">

            <!-- Genera los campos del formulario dinámicamente listos para ser editados -->
            <?php foreach ($usuario as $campo => $valor): ?>
                <div class="input-grupo">
                    <p class="pequenio"><b class="color-primario negrita"><?= ucfirst($campo) ?></b></p>
                    <input required class="form-input" type="text"
                           name="<?= $campo ?>"
                           value="<?= htmlspecialchars($valor) ?>" />
                </div>
            <?php endforeach; ?>

            <!-- Botones de acción del modal: guardar cambios o cancelar -->
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