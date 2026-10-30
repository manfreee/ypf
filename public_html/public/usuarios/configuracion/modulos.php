<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/configuracion-inicial-modulo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/session.php';


if (!isset($_SESSION['id_usuario'])) {
    die("Error: Usuario no autenticado");
}

$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT * FROM modulos WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$modulos = []; // Array donde guardaremos los módulos con sus datos

while ($modulo = $result->fetch_assoc()) { 
    $numero_serie = $modulo['numero_serie'];

    // Traer los últimos 3 registros de la tabla 'datos' para este módulo
    $sql_datos = "SELECT tension, corriente, potencia, energia, angulo, intensidad, fecha_hora 
                 FROM datos 
                 WHERE id_modulo = ? 
                 ORDER BY fecha_hora DESC 
                 LIMIT 3";
    $stmt_datos = $conexion->prepare($sql_datos);
    $stmt_datos->bind_param("i", $modulo['id_modulo']);
    $stmt_datos->execute();
    $result_datos = $stmt_datos->get_result();

    $datos_modulo = [];
    while ($dato = $result_datos->fetch_assoc()) {
        $datos_modulo[] = $dato; // Guardamos cada dato
    }
    $stmt_datos->close();

    // Guardamos todo en el arreglo principal
    $modulos[] = [
        'id_modulo' => $modulo['id_modulo'],
        'numero_serie' => $numero_serie,
        'nombre' => $modulo['nombre'],
        'datos' => $datos_modulo
    ];
}
$stmt->close();

// ========================
// AGREGAR UN MODULO NUEVO
// ========================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar-modulo'])) {
    if (isset($sesion_activa) && !$sesion_activa) {
        // Redirigir si no hay sesión activa
        header("Location: /iniciar-sesion.php");
        exit;
    }

    $codigo = trim($_POST['codigo']); // Limpia espacios
    $nombre = "Modulo Solar " . $codigo;
    $estado_defecto = 'activo';

    if (!empty($codigo)) {

        // Verifica si ya existe el módulo con ese número de serie
        $sql_check = "SELECT id_modulo FROM modulos WHERE numero_serie = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("s", $codigo);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // mostrar mensaje o manejar el error si ya existe un modulo registrado con ese numero de serie
            $mensaje = "Ya existe un módulo con el código $codigo.";
            $stmt_check->close();

        } else {
            $stmt_check->close();

            // Insertar el nuevo módulo incluyendo la columna 'estado'
            $sql_insert = "INSERT INTO modulos (numero_serie, nombre, id_usuario, estado)
                           VALUES (?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql_insert);
            $stmt->bind_param("ssis", $codigo, $nombre, $id_usuario, $estado_defecto);

            if ($stmt->execute()) {
                $id_modulo = $conexion->insert_id;
                $stmt->close();

                if (function_exists('configuracionInicialModulo')) {
                    configuracionInicialModulo($codigo, $conexion);
                }

                // Redirigir al panel del modulo recién creado
                header("Location: /public/usuarios/panel-control.php?id_modulo=" . urlencode($id_modulo));
                exit;

            } else {
                // Error
                $mensaje = "Error al insertar el módulo :(: " . $stmt->error;
                $stmt->close();
            }
        }

    } else {
        $mensaje = "El codigo no puede estar vacío.";
    }
}
?>

<button class="btn-primario btn-editar-configuracion btn-mostrar-flotante">
    <iconify-icon icon="material-symbols:add-rounded" class="color-blanco icon-h6"></iconify-icon>
    <p class="pequenio color-blanco">Nuevo modulo</p>
</button>

<div class="contenedor-modulos">
 <?php if (!empty($modulos)): ?>
<?php foreach ($modulos as $modulo) { ?>
        <div class="modulo" data-id="<?= (int)$modulo['id_modulo'] ?>" onclick="window.location.href='/public/usuarios/panel-control.php?id_modulo=<?= (int)$modulo['id_modulo'] ?>'">
            <div class="cont-superior">
                <h6 class="medium texto-truncado"><?= htmlspecialchars($modulo['nombre']) ?></h6>
            </div>
            <div class="cont-datos-sensores">
                <div class="item">
                    <iconify-icon icon="mdi:lightning-bolt" class="color-gris icon-h6"></iconify-icon>
                    <p class="pequenio color-gris">
                        <?= isset($modulo['datos'][0]['tension']) ? round($modulo['datos'][0]['tension'], 2) . 'V' : '--:--' ?>
                    </p>
                </div>
                <div class="item">
                    <iconify-icon icon="mdi:flash" class="color-gris icon-h6"></iconify-icon>
                    <p class="pequenio color-gris">
                        <?= isset($modulo['datos'][0]['corriente']) ? round($modulo['datos'][0]['corriente'], 2) . 'A' : '--:--' ?>
                    </p>
                </div>
                <div class="item">
                    <iconify-icon icon="mdi:power" class="color-gris icon-h6"></iconify-icon>
                    <p class="pequenio color-gris">
                        <?= isset($modulo['datos'][0]['potencia']) ? round($modulo['datos'][0]['potencia'], 2) . 'W' : '--:--' ?>
                    </p>
                </div>
            </div>
        </div>
    <?php } ?>
        <?php else: ?>
        <div class="contenedor-sin-productos">
            <iconify-icon icon="mage:robot-sad-fill" class="color-gris icon-h3"></iconify-icon>
            <p class="media-negrita color-gris pequenio">No tenes ningun modulo.</p>
        </div>
    <?php endif; ?>
</div>


<div class="flotante borde-redondeado shadow oculto">
    <form class="form-agregar-modulo" method="POST" action="/public/usuarios/configuracion/modulos.php">
        <div class="input-grupo">
            <div class="contenedor-input">
                <iconify-icon icon="sidekickicons:password-pencil-20-solid"
                    class="input-icon color-primario icon-h6"></iconify-icon>
                <input required="" placeholder="Codigo" class="form-input" type="number" name="codigo" />
            </div>
        </div>

        <div class="contenedor-texto pequenio media-negrita texto-truncado doble texto-centrado color-error" style="<?php echo !empty($mensaje) ? '' : 'display: none;'; ?>">
            <?php if (!empty($mensaje)): ?>
                <?= htmlspecialchars($mensaje) ?>
            <?php endif; ?>
        </div>

        <button class="btn primario btn-enviar" type="submit" name="agregar-modulo">
            <span class="pequenio"><b class="color-blanco">Agregar Modulo</b></span>
        </button>

        <div class="form-footer">
            <a class="pequenio iniciar-sesion-link" href="../modulos/comprar-rui.php">
                ¿Todavia no tenes modulo? <span class="pequenio"><b>Comprar</b></span>
            </a>
        </div>
    </form>
</div>

<!-- Script para manejar la funcionalidad de módulos -->
<script src="/js/mostrar-flotante.js"></script>