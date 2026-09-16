<?php
/*Este script es el gestor de módulos fotovoltaicos/solares (modulos.php). Cumple una doble función: consulta y prepara la información de los módulos del usuario trayendo las últimas lecturas de sensores (tensión, corriente, potencia) para dibujarlos en tarjetas interactivas, y por otro lado, procesa el alta de un nuevo módulo validando la duplicidad del número de serie, aplicando la función configuracionInicialModulo() y renderizando la ventana flotante para ingresar dicho código.*/
// Incluye las dependencias necesarias: conexión, función de inicialización y control de sesión
require_once $_SERVER['DOCUMENT_ROOT'] . '/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/usuarios/funciones/configuracion-inicial-modulo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/public/public/funciones/session.php';

// Verifica que el usuario haya iniciado sesión antes de cargar la información
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Usuario no autenticado");
}

$id_usuario = $_SESSION['id_usuario'];

/* --- CONSULTA Y PREPARACIÓN DE MÓDULOS --- */

// Prepara la consulta para traer los módulos asignados al usuario logueado
$sql = "SELECT * FROM modulos WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$modulos = []; // Array estructurado donde guardaremos los módulos con sus métricas

// Recorre cada módulo del usuario
while ($modulo = $result->fetch_assoc()) {
    $numero_serie = $modulo['numero_serie'];

    // Trae las últimas 3 lecturas de sensores registradas en la tabla 'datos' para este módulo
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
        $datos_modulo[] = $dato; // Almacena cada registro de telemetría
    }

    // Estructura y guarda el módulo junto a sus datos dinámicos
    $modulos[] = [
        'id_modulo' => $modulo['id_modulo'],
        'numero_serie' => $numero_serie,
        'nombre' => $modulo['nombre'],
        'datos' => $datos_modulo
    ];
}


/* --- PROCESAMIENTO: AGREGAR UN MÓDULO NUEVO --- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar-modulo'])) {
    // Redirige al login si la sesión no estuviera activa
    if (!$sesion_activa) {
        header("Location: iniciar-sesion.php");
        exit;
    }

    $codigo = trim($_POST['codigo']); // Limpia espacios en blanco
    $nombre = "Modulo Solar " . $codigo;

    if (!empty($codigo)) {

        // 1. Verifica si ya existe un módulo registrado con ese número de serie
        $sql_check = "SELECT id_modulo FROM modulos WHERE numero_serie = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("s", $codigo);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Notifica error si el código ya está en uso por otro registro
            $mensaje = "Ya existe un módulo con el código $codigo.";
            $stmt_check->close();

        } else {
            $stmt_check->close();

            // 2. Inserta el nuevo módulo vinculado al id_usuario actual
            $sql_insert = "INSERT INTO modulos (numero_serie, nombre, id_usuario)
                           VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($sql_insert);
            $stmt->bind_param("isi", $codigo, $nombre, $id_usuario);

            if ($stmt->execute()) {
                $id_modulo = $conexion->insert_id;
                $stmt->close();
                
                // Ejecuta los parámetros o valores predeterminados para el módulo recién creado
                configuracionInicialModulo($codigo, $conexion);
                
                if ($conexion) {
                    $conexion->close();
                }
                
                // 3. Redirige al panel de control específico del nuevo módulo
                header("Location: /public/usuarios/panel-control.php?id_modulo=" . urlencode($id_modulo));
                exit;

            } else {
                // Notifica si ocurre un fallo al ejecutar el INSERT
                $mensaje = "Error al insertar el módulo :(: " . $stmt->error;
                $stmt->close();
            }
        }
        $conexion->close();

    } else {
        $mensaje = "El codigo no puede estar vacío.";
    }
}
?>

<!-- Botón para desplegar la ventana/modal flotante -->
<button class="btn-primario btn-editar-configuracion btn-mostrar-flotante">
    <iconify-icon icon="material-symbols:add-rounded" class="color-blanco icon-h6"></iconify-icon>
    <p class="pequenio color-blanco">Nuevo modulo</p>
</button>

<!-- Grilla o lista de módulos en pantalla -->
<div class="contenedor-modulos">
    <?php if (!empty($modulos)): ?>
        <?php foreach ($modulos as $modulo) { ?>
            <!-- Tarjeta que redirige al panel individual al hacer click -->
            <div class="modulo" data-id="<?= $modulo['id_modulo'] ?>" onclick="window.location.href='/public/usuarios/panel-control.php?id_modulo=<?= $modulo['id_modulo'] ?>'">
                <div class="cont-superior">
                    <h6 class="medium texto-truncado"><?= htmlspecialchars($modulo['nombre']) ?></h6>
                </div>
                
                <!-- Presentación de las últimas métricas de telemetría (Tensión, Corriente, Potencia) -->
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
        <!-- Vista en caso de que el usuario no posea ningún módulo -->
        <div class="contenedor-sin-productos">
            <iconify-icon icon="mage:robot-sad-fill" class="color-gris icon-h3"></iconify-icon>
            <p class="media-negrita color-gris pequenio">No tenes ningun modulo.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal o tarjeta flotante para la registración de un nuevo módulo -->
<div class="flotante borde-redondeado shadow oculto">
    <form class="form-agregar-modulo" method="POST" action="/public/usuarios/configuracion/modulos.php">
        <div class="input-grupo">
            <div class="contenedor-input">
                <iconify-icon icon="sidekickicons:password-pencil-20-solid"
                    class="input-icon color-primario icon-h6"></iconify-icon>
                <input required="" placeholder="Codigo" class="form-input" type="number" name="codigo" />
            </div>
        </div>

        <!-- Contenedor para la visualización de mensajes de error -->
        <div class="contenedor-texto pequenio media-negrita texto-truncado doble texto-centrado color-error" style="display: none;">
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

<!-- Script para manejar la visibilidad de la ventana flotante -->
<script src="/js/mostrar-flotante.js"></script>