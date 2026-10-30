<?php
// API de búsqueda/listado de usuarios.
 
// CAMBIO: hay que iniciar la sesión para poder controlar el acceso (ver más abajo).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Se incluye la conexión a la base de datos desde la ruta absoluta del servidor.
// IMPORTANTE: esta ruta apunta a /PHP/conexion.php, así que debe existir exactamente ahí.
// CAMBIO (a verificar): los otros archivos del proyecto incluyen '/conexion.php' sin la carpeta /PHP.
// Confirmá en el hosting cuál es la ruta real y dejá solo una.
include_once $_SERVER["DOCUMENT_ROOT"] . "/PHP/conexion.php";
 
// CAMBIO: control de acceso. Antes cualquiera que abriera la URL veía nombres y correos de todos los usuarios.
// AJUSTAR: $ID_ROL_ADMIN debe ser el id_rol del administrador en la tabla `roles`,
// y hay que confirmar que el login guarde 'id_rol' dentro de $_SESSION['usuario'].
$ID_ROL_ADMIN = 1;
if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['id_rol'] ?? null) != $ID_ROL_ADMIN) {
    http_response_code(403);
    exit();
}
 
// Verifica si la URL trae un parámetro llamado "tabla".
// Esto permite saber qué tipo de datos se quieren buscar o listar.
if (isset($_GET['tabla'])) {
    // Guarda el valor de la tabla que se desea consultar.
    $tabla = $_GET['tabla'];
 
    // switch permite manejar distintos casos según el valor de $tabla.
    switch ($tabla) {
        // Caso 1: búsqueda de usuarios
        case 'usuarios':
            // CAMBIO: se eliminó $usuario_seleccionado porque nunca se usaba.
 
            // CAMBIO: q ahora es opcional. Antes, si la URL llegaba sin q, $resultado_usuario
            // no existía y el while daba un error fatal. Con '' se listan todos los usuarios.
            $q = $_GET['q'] ?? '';
 
            // Se arma el texto con comodines para buscar coincidencias parciales.
            // Ejemplo: q = "ana" -> "%ana%". Con q vacío queda "%%", que coincide con todos,
            // por eso ya no hace falta el if separado para el caso vacío.
            $like = "%" . $q . "%";
 
            // CAMBIOS en la consulta:
            // - Se piden solo las columnas necesarias (antes SELECT * traía también el hash de la contraseña).
            // - Se usa LEFT JOIN con roles para traer el nombre del rol en la misma consulta
            //   (antes se hacía una consulta extra por cada usuario).
            // - COALESCE deja "Sin rol" si el usuario no tiene un rol que coincida.
            // - Sigue siendo una consulta preparada, así que no hay inyección SQL.
            $stmt = $conexion->prepare("
                SELECT u.id_usuario,
                       u.email,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                       COALESCE(r.nombre_rol, 'Sin rol') AS rol
                FROM usuarios u
                LEFT JOIN roles r ON r.id_rol = u.id_rol
                WHERE CONCAT(u.nombre, ' ', u.apellido) LIKE ?
                ORDER BY u.id_usuario
            ");
 
            // Se enlaza el parámetro a la consulta preparada.
            $stmt->bind_param("s", $like);
 
            // Se ejecuta la consulta.
            $stmt->execute();
 
            // Se obtiene el resultado para recorrerlo después.
            $resultado_usuario = $stmt->get_result();
 
            // Recorre todos los usuarios devueltos por la consulta.
            // CAMBIO: se eliminó la consulta interna a `roles` y el bind_result,
            // porque el rol ya viene en $usuario['rol'] gracias al JOIN.
            while ($usuario = $resultado_usuario->fetch_assoc()) { ?>
 
                <!--
                    Se imprime cada usuario como un artículo HTML.
                    data-id guarda el id del usuario para luego poder manejarlo con JavaScript.
                    style inline: borde y padding para simplificar la vista.
                -->
                <article class='usuario-card item usuario' data-id='<?= (int)$usuario['id_usuario'] ?>' style='border:1px solid #000; padding:20px;'>
                    <!-- Muestra el nombre completo del usuario. -->
                    <!-- CAMBIO: htmlspecialchars evita que un nombre con HTML inyecte código en la página. -->
                    <h3><?= htmlspecialchars(ucfirst($usuario['nombre_completo'])) ?></h3>
 
                    <!-- Muestra el correo del usuario (CAMBIO: escapado con htmlspecialchars) -->
                    <p><strong>Correo: </strong><?= htmlspecialchars($usuario['email']) ?></p>
 
                    <!-- Muestra el nombre del rol (CAMBIO: viene del JOIN y se escapa) -->
                    <p><strong>Rol: </strong><?= htmlspecialchars($usuario['rol']) ?></p>
 
                    <!-- Acciones para cada usuario -->
                    <div class='acciones'>
                        <a href='#' class='btn-editar'>Editar</a>
                        <a href='#' class='btn-eliminar'>Eliminar</a>
                    </div>
                </article><?php
            }
 
            // CAMBIO: se cierra el statement para liberar recursos.
            $stmt->close();
        break;
 
        // Si la tabla no coincide con ninguna opción válida, no hace nada.
        default:
        break;
    }
}
?>