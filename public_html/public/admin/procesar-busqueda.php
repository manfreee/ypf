<?php
//API¿?
    // Se incluye la conexión a la base de datos desde la ruta absoluta del servidor.
    // IMPORTANTE: esta ruta apunta a /PHP/conexion.php, así que debe existir exactamente ahí.
    include_once $_SERVER["DOCUMENT_ROOT"]. "/PHP/conexion.php";

    // Verifica si la URL trae un parámetro llamado "tabla".
    // Esto permite saber qué tipo de datos se quieren buscar o listar.
    if (isset($_GET['tabla'])) {
        // Guarda el valor de la tabla que se desea consultar.
        $tabla = $_GET['tabla'];

        // switch permite manejar distintos casos según el valor de $tabla.
        switch ($tabla) {
            // Caso 1: busqueda de usuarios
            case 'usuarios':
                // Se intenta obtener el id del usuario si existe y es numérico.
                // Si no llega un id válido, se deja en null.
                $usuario_seleccionado = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;

                // Verifica si viene un parámetro q en la URL.
                // q suele significar "query" o texto de búsqueda.
                if (isset($_GET['q'])) {
                    //Verificar si exsite un o más elemento en la bdd segun lo que tenga la barra de busqueda.
                    //usando consulta preparada para evitar inyeccion SQL
                    $q = $_GET['q'];

                    // Si la búsqueda está vacía, se muestran todos los usuarios ordenados por id.
                    if ($q === "") {
                        $resultado_usuario = $conexion->query("SELECT *, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM usuarios ORDER BY id_usuario");
                    } else {
                        // Si hay texto en la búsqueda, se usa una consulta preparada.
                        // Esto ayuda a evitar inyección SQL.
                        $stmt = $conexion->prepare("SELECT *, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM usuarios
                        WHERE CONCAT(nombre, ' ', apellido) LIKE ? ORDER BY id_usuario");

                        // Se arma el texto con comodines para buscar coincidencias parciales.
                        // Ejemplo: q = "ana" -> "%ana%"
                        $like = "%" . $q . "%";

                        // Se enlaza el parámetro a la consulta preparada.
                        $stmt->bind_param("s", $like);

                        // Se ejecuta la consulta.
                        $stmt->execute();

                        // Se obtiene el resultado para recorrerlo después.
                        $resultado_usuario = $stmt->get_result();
                    }
                }

                // Recorre todos los usuarios devueltos por la consulta.
                while ($usuario = $resultado_usuario->fetch_assoc()) {
                    // Obtiene el id del rol del usuario.
                    $rol_id = $usuario['id_rol'];

                    // Valor por defecto si no encuentra el rol.
                    $rol = "Sin rol";

                    // Consulta preparada para obtener el nombre del rol según el id_rol.
                    $stmt_rol = $conexion->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");

                    // Si la preparación de la consulta fue exitosa, se ejecuta.
                    if ($stmt_rol) {
                        // Se enlaza el parámetro id_rol.
                        $stmt_rol->bind_param("i", $rol_id);

                        // Ejecuta la consulta.
                        $stmt_rol->execute();

                        // Se guarda el resultado en una variable.
                        $stmt_rol->bind_result($nombre_rol);

                        // Si encuentra un rol, lo guarda.
                        if ($stmt_rol->fetch()) {
                            $rol = $nombre_rol;
                        }

                        // Cierra el statement para liberar recursos.
                        $stmt_rol->close();
                    }?>

                    <!--
                        Se imprime cada usuario como un artículo HTML.
                        data-id guarda el id del usuario para luego poder manejarlo con JavaScript.
                        style inline: borde y padding para simplificar la vista.
                    -->
                    <article class='usuario-card item usuario' data-id='<?= $usuario['id_usuario'] ?>'style='border:1px solid #000; padding:20px;'>
                        <!-- Muestra el nombre completo del usuario. -->
                        <h3><?php echo ucfirst($usuario['nombre_completo']) ?></h3>

                        <!-- Muestra el correo del usuario -->
                        <p><strong>Correo: </strong><?= $usuario['email'] ?></p>

                        <!-- Muestra el nombre del rol -->
                        <p><strong>Rol: </strong><?= $rol ?></p>

                        <!-- Acciones para cada usuario -->
                        <div class='acciones'>
                            <a href='#' class='btn-editar'>Editar</a>
                            <a href='#' class='btn-eliminar'>Eliminar</a>
                        </div>
                    </article><?php
                }
            break;

            // Si la tabla no coincide con ninguna opción válida, no hace nada.
            default:
            break;
        }
    }
?>